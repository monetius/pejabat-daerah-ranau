<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Support\Shortcodes;
use Illuminate\Console\Command;

class UseNewsSection extends Command
{
    protected $signature = 'cms:news-section';

    protected $description = 'Replace the hard-coded Hebahan & Berita block on the homepage with the live announcements section';

    public function handle(): int
    {
        $home = Page::where('path', '')->first();
        if (!$home) {
            $this->error('Halaman utama belum diimport. Jalankan: php artisan cms:import');

            return self::FAILURE;
        }

        if (str_contains($home->body, Shortcodes::NEWS)) {
            $this->info('Halaman utama sudah menggunakan bahagian Hebahan & Berita yang baru.');

            return self::SUCCESS;
        }

        $new = Shortcodes::swapNewsSection($home->body);
        if ($new === null) {
            $this->error('Bahagian "Hebahan & Berita" tidak ditemui dalam halaman utama. Letakkan [[hebahan-berita]] sendiri di Admin > Halaman.');

            return self::FAILURE;
        }

        // Keep the old version so it can be restored from the page's revision list.
        $home->revisions()->create(['title' => $home->title, 'body' => $home->body, 'scripts' => $home->scripts]);
        $home->update(['body' => $new]);

        $this->info('Siap. Halaman utama kini memaparkan hebahan & berita terkini. Versi lama boleh dipulihkan di Admin > Halaman > Laman Utama.');

        return self::SUCCESS;
    }
}
