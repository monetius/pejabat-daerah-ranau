<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait MakesUniqueSlugs
{
    /**
     * Turns a title into a slug that no other row of $modelClass uses yet (adds -2, -3, ... when needed).
     *
     * @param class-string<Model> $modelClass
     */
    private function uniqueSlug(string $modelClass, string $title, ?Model $current, string $fallback): string
    {
        $base = Str::slug($title) ?: $fallback;
        $slug = $base;
        $i = 2;
        while ($modelClass::where('slug', $slug)->when($current, fn($q) => $q->whereKeyNot($current->getKey()))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
