<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Support\ContentEditor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        return view('admin.announcements.index', [
            'items' => Announcement::latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.announcements.form', ['item' => new Announcement(['status' => 'draft', 'category' => 'hebahan']), 'editor' => (new ContentEditor(''))->schema(), 'authorEnabled' => Schema::hasColumn('announcements', 'author')]);
    }

    public function store(Request $request): RedirectResponse
    {
        Announcement::create($this->validated($request));

        return redirect()->route('admin.announcements.index')->with('status', __('Hebahan disimpan.'));
    }

    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.form', ['item' => $announcement, 'editor' => $this->editor($announcement)->schema(), 'authorEnabled' => Schema::hasColumn('announcements', 'author')]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        DB::transaction(function () use ($request, $announcement): void {
            $current = Announcement::lockForUpdate()->findOrFail($announcement->id);
            $current->update($this->validated($request, $current));
        });

        return redirect()->route('admin.announcements.index')->with('status', __('Hebahan dikemas kini.'));
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')->with('status', __('Hebahan dipadam.'));
    }

    private function validated(Request $request, ?Announcement $current = null): array
    {
        $publicationMode = $request->input('publication_mode');
        $hasPublicationMode = $request->filled('publication_mode');
        $data = $request->validate([
            'title' => ['required', 'string', 'max:220'],
            'slug' => ['nullable', 'string', 'max:220', 'alpha_dash', Rule::unique('announcements', 'slug')->ignore($current)],
            'category' => ['required', Rule::in(array_keys(Announcement::CATEGORIES))],
            'author' => ['nullable', 'string', 'max:150'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'content' => ['required', 'string', 'max:2000000'],
            'publication_mode' => ['nullable', Rule::in(['draft', 'now', 'scheduled'])],
            'status' => [Rule::requiredIf(! $hasPublicationMode), 'nullable', Rule::in(['draft', 'published'])],
            'published_at' => [
                Rule::excludeIf($hasPublicationMode && $publicationMode !== 'scheduled'),
                Rule::requiredIf($publicationMode === 'scheduled'),
                'nullable',
                'date',
                Rule::when($publicationMode === 'scheduled', ['after:now']),
            ],
        ], [
            'published_at.required' => __('Pilih tarikh dan masa untuk penerbitan berjadual.'),
            'published_at.after' => __('Pilih tarikh dan masa akan datang untuk penerbitan berjadual.'),
        ]);

        if ($hasPublicationMode) {
            $data['status'] = $publicationMode === 'draft' ? 'draft' : 'published';
            if ($publicationMode === 'now') {
                $wasPublished = $current?->status === 'published' && ! $current->published_at?->isFuture();
                $data['published_at'] = $wasPublished ? ($current->published_at ?? now()) : now();
            } elseif ($publicationMode === 'draft') {
                $data['published_at'] = $current?->published_at;
            }
        }
        unset($data['publication_mode']);

        $data['body'] = ($current ? $this->editor($current) : new ContentEditor(''))->apply($data['content']);
        unset($data['content']);
        if (Schema::hasColumn('announcements', 'author')) {
            $data['author'] = array_key_exists('author', $data) ? (trim((string) $data['author']) ?: null) : $current?->author;
        } else {
            if (filled($data['author'] ?? null)) {
                throw ValidationException::withMessages(['author' => __('Fungsi penulis belum diaktifkan. Sila hubungi pentadbir.')]);
            }
            unset($data['author']);
        }
        $data['slug'] = ($data['slug'] ?? $current?->slug) ?: $this->uniqueSlug($data['title'], $current);

        // Cover image: a new upload replaces the old one; the tick box removes it.
        unset($data['image']);
        if ($request->hasFile('image')) {
            $data['image'] = $this->storeImage($request->file('image'));
        } elseif ($request->boolean('remove_image')) {
            $data['image'] = null;
        }

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    private function storeImage(UploadedFile $file): string
    {
        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'gambar';
        $name = $base.'-'.Str::lower(Str::random(6)).'.'.$file->extension();
        $folder = 'uploads/'.date('Y/m');
        $file->move(public_path($folder), $name);

        return '/'.$folder.'/'.$name;
    }

    private function editor(Announcement $announcement): ContentEditor
    {
        return new ContentEditor((string) $announcement->body, true, hash('sha256', serialize($announcement->getRawOriginal())));
    }

    private function uniqueSlug(string $title, ?Announcement $current): string
    {
        $base = Str::slug($title) ?: 'hebahan';
        $slug = $base;
        $i = 2;
        while (Announcement::where('slug', $slug)->when($current, fn ($q) => $q->whereKeyNot($current->getKey()))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
