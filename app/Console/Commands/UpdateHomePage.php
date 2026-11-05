<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Support\HomePage;
use Illuminate\Console\Command;

class UpdateHomePage extends Command
{
    protected $signature = 'cms:home-page';

    protected $description = 'Replace the saved homepage with the current design from home.blade.php (the old version is kept as a revision)';

    public function handle(): int
    {
        $home = Page::where('path', '')->first();
        if (! $home) {
            $this->error('Halaman utama belum diimport. Jalankan: php artisan cms:import');

            return self::FAILURE;
        }

        $this->info(HomePage::upgrade($home)
            ? 'Siap. Halaman utama kini menggunakan reka bentuk baharu. Versi lama boleh dipulihkan di Admin > Halaman > Laman Utama.'
            : 'Halaman utama sudah menggunakan reka bentuk terkini.');

        return self::SUCCESS;
    }
}
