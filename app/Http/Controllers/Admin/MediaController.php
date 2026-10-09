<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index()
    {
        $dir = public_path('uploads');

        $files = File::isDirectory($dir)
            ? collect(File::allFiles($dir))->sortByDesc(fn($f) => $f->getMTime())->values()
            : collect();

        return view('admin.media.index', ['files' => $files]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,gif,pdf'],
        ]);

        $file = $request->file('file');
        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'fail';
        $name = $base . '-' . Str::lower(Str::random(6)) . '.' . $file->extension();
        $folder = 'uploads/' . date('Y/m');

        $file->move(public_path($folder), $name);

        return back()->with('status', 'Fail dimuat naik: /' . $folder . '/' . $name);
    }

    public function destroy(Request $request)
    {
        $rel = $request->validate(['path' => ['required', 'string']])['path'];

        $root = realpath(public_path('uploads'));
        $full = realpath(public_path('uploads/' . $rel));

        abort_unless(
            $root && $full && str_starts_with($full, $root . DIRECTORY_SEPARATOR) && is_file($full),
            404
        );

        File::delete($full);

        return back()->with('status', 'Fail dipadam.');
    }
}
