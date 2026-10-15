<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $dir = public_path('uploads');

        $files = File::isDirectory($dir)
            ? collect(File::allFiles($dir))->sortByDesc(fn ($f) => $f->getMTime())->values()
            : collect();

        if ($request->expectsJson()) {
            $images = public_path('assets/img');
            $library = $files->concat(File::isDirectory($images) ? File::allFiles($images) : []);

            return response()->json(['files' => $library
                ->filter(fn ($file): bool => in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf'], true))
                ->map(fn ($file): array => [
                    'name' => $file->getFilename(),
                    'url' => '/'.str_replace('\\', '/', substr($file->getPathname(), strlen(public_path()) + 1)),
                    'image' => strtolower($file->getExtension()) !== 'pdf',
                ])->values()]);
        }

        return view('admin.media.index', ['files' => $files]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,gif,pdf'],
        ]);

        $file = $request->file('file');
        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'fail';
        $name = $base.'-'.Str::lower(Str::random(6)).'.'.$file->extension();
        $folder = 'uploads/'.date('Y/m');

        $file->move(public_path($folder), $name);

        if ($request->expectsJson()) {
            return response()->json(['file' => ['name' => $name, 'url' => '/'.$folder.'/'.$name, 'image' => strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== 'pdf']], 201);
        }

        return back()->with('status', __('Fail dimuat naik: :path', ['path' => '/'.$folder.'/'.$name]));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $rel = $request->validate(['path' => ['required', 'string']])['path'];

        $root = realpath(public_path('uploads'));
        $full = realpath(public_path('uploads/'.$rel));

        abort_unless(
            $root && $full && str_starts_with($full, $root.DIRECTORY_SEPARATOR) && is_file($full),
            404
        );

        File::delete($full);

        return back()->with('status', __('Fail dipadam.'));
    }
}
