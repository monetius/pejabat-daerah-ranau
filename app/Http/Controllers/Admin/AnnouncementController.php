<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AnnouncementController extends Controller
{
    public function index()
    {
        return view('admin.announcements.index', [
            'items' => Announcement::latest()->paginate(15),
        ]);
    }

    public function create()
    {
        return view('admin.announcements.form', ['item' => new Announcement(['status' => 'draft', 'category' => 'hebahan'])]);
    }

    public function store(Request $request)
    {
        Announcement::create($this->validated($request));

        return redirect()->route('admin.announcements.index')->with('status', 'Hebahan disimpan.');
    }

    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.form', ['item' => $announcement]);
    }

    public function update(Request $request, Announcement $announcement)
    {
        $announcement->update($this->validated($request, $announcement));

        return redirect()->route('admin.announcements.index')->with('status', 'Hebahan dikemas kini.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')->with('status', 'Hebahan dipadam.');
    }

    private function validated(Request $request, ?Announcement $current = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:220'],
            'slug' => ['nullable', 'string', 'max:220', 'alpha_dash', Rule::unique('announcements', 'slug')->ignore($current)],
            'category' => ['required', Rule::in(array_keys(Announcement::CATEGORIES))],
            'summary' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'body' => ['required', 'string'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
        ]);

        $data['slug'] = ($data['slug'] ?? null) ?: $this->uniqueSlug($data['title'], $current);

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
        $name = $base . '-' . Str::lower(Str::random(6)) . '.' . $file->extension();
        $folder = 'uploads/' . date('Y/m');
        $file->move(public_path($folder), $name);

        return '/' . $folder . '/' . $name;
    }

    private function uniqueSlug(string $title, ?Announcement $current): string
    {
        $base = Str::slug($title) ?: 'hebahan';
        $slug = $base;
        $i = 2;
        while (Announcement::where('slug', $slug)->when($current, fn($q) => $q->whereKeyNot($current->getKey()))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
