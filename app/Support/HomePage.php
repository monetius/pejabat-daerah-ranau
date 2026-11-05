<?php

namespace App\Support;

use App\Console\Commands\ImportSite;
use App\Models\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

/**
 * Keeps the saved homepage (pages table, path '') in step with the design in
 * resources/views/site/home.blade.php. When the design version changes, the saved body is
 * replaced once and the previous one is kept as a revision (Admin > Halaman > Laman Utama).
 */
class HomePage
{
    /** Bump this (and data-home-version in home.blade.php) to roll out a new homepage design. */
    public const VERSION = '2';

    public static function isCurrent(?string $body): bool
    {
        return str_contains((string) $body, 'data-home-version="'.self::VERSION.'"');
    }

    /** The homepage body as written in home.blade.php, or null when it can't be read. */
    public static function designBody(): ?string
    {
        $file = resource_path('views/site/home.blade.php');
        if (! File::exists($file)) {
            return null;
        }
        $body = (new ImportSite)->parse(File::get($file))['body'] ?? null;

        return $body !== null && self::isCurrent($body) ? $body : null;
    }

    /** Swaps in the current design once. Returns true when the saved homepage changed. */
    public static function upgrade(Page $home): bool
    {
        if (self::isCurrent($home->body)) {
            return false; // normal visits: no extra queries
        }
        $body = self::designBody();
        if ($body === null) {
            return false;
        }

        try {
            return DB::transaction(function () use ($home, $body): bool {
                $page = Page::lockForUpdate()->find($home->id);
                if (! $page || self::isCurrent($page->body)) {
                    return false;
                }
                $page->revisions()->create(['title' => $page->title, 'body' => $page->body, 'scripts' => $page->scripts]);
                $page->update(['body' => $body, 'scripts' => null]);
                $home->setRawAttributes($page->getAttributes(), true);

                return true;
            });
        } catch (Throwable) {
            return false;
        }
    }
}
