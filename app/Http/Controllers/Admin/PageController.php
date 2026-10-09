<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PageController extends Controller
{
    /** First URL segments that belong to the app itself. */
    private const RESERVED = ['admin', 'hebahan', 'assets', 'uploads', 'storage', 'build', 'login', 'up'];

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $groups = Page::query()
            ->when($q !== '', fn($query) => $query->where(
                fn($w) => $w->where('title', 'like', "%{$q}%")->orWhere('path', 'like', "%{$q}%")
            ))
            ->orderBy('section')
            ->orderBy('path')
            ->get()
            ->groupBy(fn(Page $p) => $p->section ?: 'Lain-lain');

        return view('admin.pages.index', ['groups' => $groups, 'q' => $q]);
    }

    public function create()
    {
        $starter = <<<'HTML'
<div class="page-header">
  <div class="page-header-media"><img src="/assets/img/banner.jpg" alt="" /></div>
  <div class="page-header-scrim"></div>
  <div class="container">
    <ul class="breadcrumb">
      <li><a href="/">Laman Utama</a></li>
      <li>Tajuk Halaman</li>
    </ul>
    <h1>Tajuk Halaman</h1>
  </div>
</div>
<section class="ds-section">
  <div class="container">
    <div class="ds-block">
      <h2>Subtajuk</h2>
      <p>Tulis kandungan di sini.</p>
    </div>
  </div>
</section>
HTML;

        return view('admin.pages.form', [
            'item' => new Page([
                'status' => 'draft',
                'body_class' => 'has-banner',
                'styles' => 'dasar.css',
                'body' => $starter,
            ])
        ]);
    }

    public function store(Request $request)
    {
        $page = Page::create($this->validated($request));

        return redirect()->route('admin.pages.edit', $page)->with('status', 'Halaman disimpan.');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.form', [
            'item' => $page,
            'revisions' => $page->revisions()->with('author')->orderByDesc('id')->take(30)->get(),
        ]);
    }

    public function update(Request $request, Page $page)
    {
        $data = $this->validated($request, $page);
        $this->snapshot($page, $data, $request->user()?->id);
        $page->update($data);

        return redirect()->route('admin.pages.edit', $page)->with('status', 'Halaman dikemas kini.');
    }

    public function destroy(Page $page)
    {
        if ($page->is_system) {
            return back()->withErrors(['delete' => 'Halaman asal tapak tidak boleh dipadam. Tukar status kepada Draf jika mahu menyembunyikannya.']);
        }

        $page->delete();

        return redirect()->route('admin.pages.index')->with('status', 'Halaman dipadam.');
    }

    /** Renders unsaved form content inside the real site layout, in a new tab. */
    public function preview(Request $request)
    {
        $page = new Page($request->only(['title', 'meta_description', 'body_class', 'styles', 'body', 'scripts']));
        $page->title = $page->title ?: 'Pratonton';

        return view('site.cms-page', ['page' => $page, 'preview' => true]);
    }

    public function restore(Request $request, Page $page, PageRevision $revision)
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

        if (!$changed) {
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
            'body_class' => ['nullable', 'string', 'max:120'],
            'styles' => ['nullable', 'string', 'max:2000'],
            'body' => ['required', 'string'],
            'scripts' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'published'])],
        ]);

        $data['body'] = str_replace("\r\n", "\n", $data['body']);
        $data['scripts'] = isset($data['scripts']) ? str_replace("\r\n", "\n", $data['scripts']) : null;
        $data['styles'] = $this->cleanLines($data['styles'] ?? null);

        $isHome = $current !== null && $current->path === '';

        if ($isHome) {
            $data['path'] = '';
            $data['slug'] = $current->slug;
        } else {
            $data['slug'] = $data['slug'] ?: $this->uniqueSlug($data['title'], $current);
            $path = strtolower(trim((string) ($data['path'] ?? ''), '/ '));
            if ($path === '') {
                $path = 'halaman/' . $data['slug'];
            }

            if (in_array(explode('/', $path)[0], self::RESERVED, true)) {
                throw ValidationException::withMessages(['path' => 'Laluan ini dikhaskan untuk sistem. Pilih laluan lain.']);
            }
            $taken = Page::where('path', $path)->when($current, fn($q) => $q->whereKeyNot($current->getKey()))->exists();
            if ($taken) {
                throw ValidationException::withMessages(['path' => 'Laluan ini sudah digunakan oleh halaman lain.']);
            }
            $data['path'] = $path;
        }

        return $data;
    }

    private function cleanLines(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $lines = array_filter(array_map('trim', preg_split('/\R/', $value)));

        return $lines ? implode("\n", $lines) : null;
    }

    private function uniqueSlug(string $title, ?Page $current): string
    {
        $base = Str::slug($title) ?: 'halaman';
        $slug = $base;
        $i = 2;
        while (Page::where('slug', $slug)->when($current, fn($q) => $q->whereKeyNot($current->getKey()))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
