<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\SitePart;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImportSite extends Command
{
    protected $signature = 'cms:import {--force : Overwrite pages/parts that already exist in the database}';

    protected $description = 'Import the existing Blade pages, navbar and footer into the CMS database';

    private const SKIP = ['cms-page', 'dynamic-page'];

    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $base = resource_path('views/site');
        $created = 0;
        $skipped = 0;

        foreach (File::allFiles($base) as $file) {
            $rel = str_replace('\\', '/', $file->getRelativePathname());
            if (!str_ends_with($rel, '.blade.php')) {
                continue;
            }
            $name = substr($rel, 0, -strlen('.blade.php'));
            if (in_array($name, self::SKIP, true) || str_starts_with($name, 'announcements/')) {
                continue;
            }

            $data = $this->parse($file->getContents());
            if ($data === null) {
                $this->warn("Skipped (could not read content): {$name}");
                $skipped++;

                continue;
            }

            $path = $name === 'home' ? '' : $name;
            $exists = Page::where('path', $path)->exists();
            if ($exists && !$force) {
                $this->line("Exists, left alone: /{$path}");
                $skipped++;

                continue;
            }

            $section = $name === 'home' ? 'Utama' : ucwords(str_replace('-', ' ', dirname($name)));
            $title = $data['title'] ?: 'Pejabat Daerah Ranau';

            Page::updateOrCreate(['path' => $path], [
                'title' => $title,
                'slug' => $name === 'home' ? 'laman-utama' : str_replace('/', '-', $name),
                'section' => $section,
                'meta_description' => $data['description'],
                'body_class' => $data['body_class'],
                'styles' => $data['styles'],
                'body' => $data['body'],
                'scripts' => $data['scripts'],
                'status' => 'published',
                'is_system' => true,
            ]);
            $this->info('Imported: /' . $path);
            $created++;
        }

        foreach (['navbar' => 'Navbar (menu atas)', 'footer' => 'Footer (bahagian bawah)'] as $key => $label) {
            $file = resource_path("views/partials/{$key}.blade.php");
            if (!File::exists($file)) {
                continue;
            }
            if (SitePart::whereKey($key)->exists() && !$force) {
                $this->line("Exists, left alone: {$key}");

                continue;
            }
            SitePart::updateOrCreate(['key' => $key], [
                'label' => $label,
                'html' => trim(str_replace("\r\n", "\n", File::get($file))),
            ]);
            $this->info("Imported: {$key}");
        }

        $this->newLine();
        $this->info("Done. Imported {$created} page(s), skipped {$skipped}.");

        return self::SUCCESS;
    }

    /** @return array{title:?string,description:?string,body_class:?string,styles:?string,body:string,scripts:?string}|null */
    public function parse(string $raw): ?array
    {
        $c = str_replace("\r\n", "\n", $raw);
        $marker = "@section('content')";
        $s = strpos($c, $marker);
        if ($s === false) {
            return null;
        }
        $head = substr($c, 0, $s);
        $after = substr($c, $s + strlen($marker));

        if (str_starts_with(ltrim($after), '@verbatim')) {
            $b0 = strpos($after, '@verbatim') + strlen('@verbatim');
            $b1 = strpos($after, '@endverbatim', $b0);
            if ($b1 === false) {
                return null;
            }
            $body = substr($after, $b0, $b1 - $b0);
        } else {
            $b1 = strrpos($after, '@endsection');
            if ($b1 === false) {
                return null;
            }
            $body = substr($after, 0, $b1);
        }

        $scripts = null;
        $p = strpos($c, "@push('scripts')");
        if ($p !== false) {
            $v = strpos($c, '@verbatim', $p);
            if ($v !== false) {
                $v += strlen('@verbatim');
                $e = strpos($c, '@endverbatim', $v);
                if ($e !== false) {
                    $scripts = trim(substr($c, $v, $e - $v));
                }
            }
        }

        preg_match_all('#assets/css/([\w.\-]+\.css)#', $head, $m);

        return [
            'title' => $this->meta($head, 'title'),
            'description' => $this->meta($head, 'description'),
            'body_class' => $this->meta($head, 'body_class'),
            'styles' => $m[1] ? implode("\n", array_unique($m[1])) : null,
            'body' => trim($body),
            'scripts' => $scripts ?: null,
        ];
    }

    private function meta(string $head, string $key): ?string
    {
        $re = '/@section\(\s*[\'"]' . $key . '[\'"]\s*,\s*([\'"])(.*?)\1\s*\)/s';
        if (preg_match($re, $head, $m)) {
            return str_replace("\\'", "'", $m[2]);
        }

        return null;
    }
}
