<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/** Saves an uploaded file under public/uploads/YYYY/MM with a safe, unique name. */
class PublicUpload
{
    /** @return string The public URL, e.g. /uploads/2026/11/poster-ab12cd.jpg */
    public static function store(UploadedFile $file, string $fallbackName = 'fail'): string
    {
        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: $fallbackName;
        $name = $base . '-' . Str::lower(Str::random(6)) . '.' . $file->extension();
        $folder = 'uploads/' . date('Y/m');
        $file->move(public_path($folder), $name);

        return '/' . $folder . '/' . $name;
    }
}
