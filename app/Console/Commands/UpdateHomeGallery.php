<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UpdateHomeGallery extends Command
{
    protected $signature = 'cms:home-gallery';

    protected $description = 'Replace the "Galeri & Pautan" shortcut cards on the saved homepage with the current design from home.blade.php';

    private const GRID = '/<div\b[^>]*\bclass="[^"]*\bquick-access-grid\b[^"]*"[^>]*>/i';

    public function handle(): int
    {
        $home = Page::where('path', '')->first();
        if (! $home) {
            $this->error('Halaman utama belum diimport. Jalankan: php artisan cms:import');

            return self::FAILURE;
        }

        $fresh = self::grid(File::get(resource_path('views/site/home.blade.php')));
        $current = self::grid((string) $home->body);
        if ($fresh === null || $current === null) {
            $this->error('Bahagian kad "Galeri & Pautan" tidak ditemui.');

            return self::FAILURE;
        }
        if ($current['html'] === $fresh['html'] || str_contains($current['html'], 'quick-access-title')) {
            $this->info('Kad Galeri & Pautan di halaman utama sudah menggunakan reka bentuk terkini.');

            return self::SUCCESS;
        }

        // Keep the old version so it can be restored from the page's revision list.
        $home->revisions()->create(['title' => $home->title, 'body' => $home->body, 'scripts' => $home->scripts]);
        $body = (string) $home->body;
        $home->update(['body' => substr($body, 0, $current['start']).$fresh['html'].substr($body, $current['end'])]);

        $this->info('Siap. Kad Galeri & Pautan dikemas kini. Versi lama boleh dipulihkan di Admin > Halaman > Laman Utama.');

        return self::SUCCESS;
    }

    /** Finds the shortcut-card grid and returns its full HTML plus where it starts and ends. */
    private static function grid(string $html): ?array
    {
        if (! preg_match(self::GRID, $html, $open, PREG_OFFSET_CAPTURE)) {
            return null;
        }
        $start = $open[0][1];
        $pos = $start + strlen($open[0][0]);
        $depth = 1;
        while ($depth > 0 && preg_match('/<(\/?)div\b[^>]*>/i', $html, $tag, PREG_OFFSET_CAPTURE, $pos)) {
            $depth += $tag[1][0] === '/' ? -1 : 1;
            $pos = $tag[0][1] + strlen($tag[0][0]);
        }

        return $depth === 0 ? ['start' => $start, 'end' => $pos, 'html' => substr($html, $start, $pos - $start)] : null;
    }
}
