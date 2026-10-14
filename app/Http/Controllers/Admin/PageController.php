<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageRevision;
use App\Support\ContentEditor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PageController extends Controller
{
    /** First URL segments that belong to the app itself. */
    private const RESERVED = ['admin', 'hebahan', 'assets', 'uploads', 'storage', 'build', 'login', 'up'];

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        $pages = Page::query()
            ->when($q !== '', fn ($query) => $query->where(
                fn ($w) => $w->where('title', 'like', "%{$q}%")->orWhere('path', 'like', "%{$q}%")
            ))
            ->orderBy('path')
            ->get();

        [$sectionRank, $pageRank] = $this->navOrder();
        $norm = fn (string $name) => preg_replace('/[^a-z0-9]/', '', strtolower($name));

        $groups = $pages->groupBy(fn (Page $p) => $p->section ?: 'Lain-lain');

        // Sections follow the navbar order; unknown sections come after, "Lain-lain" last.
        $names = $groups->keys()->map(fn ($n) => (string) $n)->all();
        usort($names, function (string $a, string $b) use ($sectionRank, $norm) {
            $ra = $a === 'Lain-lain' ? PHP_INT_MAX : ($sectionRank[$norm($a)] ?? PHP_INT_MAX - 1);
            $rb = $b === 'Lain-lain' ? PHP_INT_MAX : ($sectionRank[$norm($b)] ?? PHP_INT_MAX - 1);

            return [$ra, $a] <=> [$rb, $b];
        });

        // Pages inside a section follow the dropdown order; unlinked pages go after, by path.
        $ordered = [];
        foreach ($names as $name) {
            $ordered[$name] = $groups[$name]->sort(
                fn (Page $a, Page $b) => [$pageRank[$a->id] ?? PHP_INT_MAX, $a->path] <=> [$pageRank[$b->id] ?? PHP_INT_MAX, $b->path]
            )->values();
        }

        return view('admin.pages.index', ['groups' => collect($ordered), 'q' => $q]);
    }

    /**
     * Order of sections and pages as they appear in the navbar.
     * Uses the Menu screen when it exists, otherwise the original site order.
     *
     * @return array{0: array<string,int>, 1: array<int,int>}
     */
    private function navOrder(): array
    {
        $sections = ['utama' => -1];
        $pageRank = [];

        if (Schema::hasTable('menu_items')) {
            foreach (DB::table('menu_items')->whereNull('parent_id')->orderBy('sort_order')->orderBy('id')->pluck('label') as $i => $label) {
                $sections[preg_replace('/[^a-z0-9]/', '', strtolower($label))] ??= $i;
            }

            $ids = DB::table('menu_items as c')
                ->join('menu_items as p', 'c.parent_id', '=', 'p.id')
                ->whereNotNull('c.page_id')
                ->orderBy('p.sort_order')->orderBy('p.id')->orderBy('c.sort_order')->orderBy('c.id')
                ->pluck('c.page_id');
            foreach ($ids as $i => $id) {
                $pageRank[$id] ??= $i;
            }
        }

        foreach (['infokorporat', 'profildaerah', 'perkhidmatanonline', 'galeripautan', 'hubungikami'] as $i => $key) {
            $sections[$key] ??= 100 + $i;
        }

        return [$sections, $pageRank];
    }

    public function create(): View
    {
        return view('admin.pages.form', [
            'item' => new Page(['status' => 'draft']),
            'editor' => (new ContentEditor(''))->schema(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $page = Page::create($this->validated($request));

        return redirect()->route('admin.pages.edit', $page)->with('status', 'Halaman disimpan.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.form', [
            'item' => $page,
            'editor' => $this->editor($page)->schema(),
            'revisions' => $page->revisions()->with('author')->orderByDesc('id')->take(30)->get(),
        ]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        DB::transaction(function () use ($request, $page): void {
            $current = Page::lockForUpdate()->findOrFail($page->id);
            $data = $this->validated($request, $current);
            $this->snapshot($current, $data, $request->user()?->id);
            $current->update($data);
        });

        return redirect()->route('admin.pages.edit', $page)->with('status', 'Halaman dikemas kini.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        if ($page->is_system) {
            return back()->withErrors(['delete' => 'Halaman asal tapak tidak boleh dipadam. Tukar status kepada Draf jika mahu menyembunyikannya.']);
        }

        $page->delete();

        return redirect()->route('admin.pages.index')->with('status', 'Halaman dipadam.');
    }

    /** Renders unsaved form content inside the real site layout, in a new tab. */
    public function preview(Request $request): View
    {
        $request->validate(['page_id' => ['nullable', 'integer', 'exists:pages,id']]);
        $current = $request->filled('page_id') ? Page::findOrFail($request->integer('page_id')) : null;
        $page = $current?->replicate() ?? new Page;
        $page->fill($this->validated($request, $current));

        return view('site.cms-page', ['page' => $page, 'preview' => true]);
    }

    public function restore(Request $request, Page $page, PageRevision $revision): RedirectResponse
    {
        abort_unless($revision->page_id === $page->id, 404);

        $data = ['title' => $revision->title, 'body' => $revision->body, 'scripts' => $revision->scripts];
        $this->snapshot($page, $data, $request->user()?->id);
        $page->update($data);

        return redirect()->route('admin.pages.edit', $page)->with('status', 'Versi lama dipulihkan.');
    }

    /** Saves the CURRENT content as a revision when it is about to change. */
    private function snapshot(Page $page, array $new, ?int $userId): void
    {
        $changed = $page->title !== $new['title']
            || $page->body !== $new['body']
            || (string) $page->scripts !== (string) ($new['scripts'] ?? '');

        if (! $changed) {
            return;
        }

        $page->revisions()->create([
            'title' => $page->title,
            'body' => $page->body,
            'scripts' => $page->scripts,
            'saved_by' => $userId,
        ]);

        $stale = $page->revisions()->orderByDesc('id')->skip(30)->take(1000)->pluck('id');
        if ($stale->isNotEmpty()) {
            PageRevision::whereIn('id', $stale)->delete();
        }
    }

    private function validated(Request $request, ?Page $current = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:220'],
            'slug' => ['nullable', 'string', 'max:220', 'alpha_dash', Rule::unique('pages', 'slug')->ignore($current)],
            'path' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9_\-\/]+$/'],
            'section' => ['nullable', 'string', 'max:80'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'content' => ['required', 'string', 'max:2000000'],
            'status' => ['required', Rule::in(['draft', 'published'])],
        ]);

        $data['body'] = ($current ? $this->editor($current) : new ContentEditor(''))->apply($data['content']);
        unset($data['content']);
        $data['body_class'] = $current?->body_class;
        $data['styles'] = $current?->styles;
        $data['scripts'] = $current?->scripts;

        $isHome = $current !== null && $current->path === '';

        if ($isHome) {
            $data['path'] = '';
            $data['slug'] = $current->slug;
        } else {
            $data['slug'] = ($data['slug'] ?? $current?->slug) ?: $this->uniqueSlug($data['title'], $current);
            $path = strtolower(trim((string) ($data['path'] ?? ''), '/ '));
            if ($path === '') {
                $path = 'halaman/'.$data['slug'];
            }

            if (in_array(explode('/', $path)[0], self::RESERVED, true)) {
                throw ValidationException::withMessages(['path' => 'Laluan ini dikhaskan untuk sistem. Pilih laluan lain.']);
            }
            $taken = Page::where('path', $path)->when($current, fn ($q) => $q->whereKeyNot($current->getKey()))->exists();
            if ($taken) {
                throw ValidationException::withMessages(['path' => 'Laluan ini sudah digunakan oleh halaman lain.']);
            }
            $data['path'] = $path;
        }

        return $data;
    }

    private function editor(Page $page): ContentEditor
    {
        return new ContentEditor((string) $page->body, true, hash('sha256', serialize($page->getRawOriginal())));
    }

    private function uniqueSlug(string $title, ?Page $current): string
    {
        $base = Str::slug($title) ?: 'halaman';
        $slug = $base;
        $i = 2;
        while (Page::where('slug', $slug)->when($current, fn ($q) => $q->whereKeyNot($current->getKey()))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
