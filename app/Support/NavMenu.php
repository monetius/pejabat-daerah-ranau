<?php

namespace App\Support;

use App\Models\MenuItem;
use App\Models\Page;
use App\Models\SitePart;
use Illuminate\Support\Facades\DB;

class NavMenu
{
    /**
     * Replaces the inside of <ul id="navLinks"> with $itemsHtml.
     * Returns null when the list can't be found, so the caller can keep the original.
     */
    public static function replaceList(string $html, string $itemsHtml): ?string
    {
        if (!preg_match('/<ul\b[^>]*\bid=["\']navLinks["\'][^>]*>/i', $html, $open, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $innerStart = $open[0][1] + strlen($open[0][0]);
        $depth = 1;
        $pos = $innerStart;
        $closeAt = null;

        while ($depth > 0 && preg_match('/<(\/?)ul\b[^>]*>/i', $html, $tag, PREG_OFFSET_CAPTURE, $pos)) {
            $depth += $tag[1][0] === '/' ? -1 : 1;
            $pos = $tag[0][1] + strlen($tag[0][0]);
            if ($depth === 0) {
                $closeAt = $tag[0][1];
            }
        }

        if ($closeAt === null) {
            return null;
        }

        return substr($html, 0, $innerStart) . "\n" . $itemsHtml . "\n" . substr($html, $closeAt);
    }

    /**
     * Reads the current navbar HTML and returns its menu as
     * [['label' => ..., 'href' => ?string, 'children' => [['label' => ..., 'href' => ...]]]].
     */
    public static function parse(string $html): array
    {
        $previous = libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8"><body>' . $html . '</body>');
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new \DOMXPath($dom);
        $menu = [];

        foreach ($xpath->query('//ul[@id="navLinks"]/li') as $li) {
            $isDropdown = str_contains(' ' . $li->getAttribute('class') . ' ', ' dropdown ');

            if ($isDropdown) {
                $button = $xpath->query('.//button', $li)->item(0);
                $label = self::clean($button?->textContent ?? '');
                $children = [];
                foreach ($xpath->query('.//ul/li/a', $li) as $a) {
                    $children[] = ['label' => self::clean($a->textContent), 'href' => trim($a->getAttribute('href'))];
                }
                if ($label !== '') {
                    $menu[] = ['label' => $label, 'href' => null, 'children' => $children];
                }

                continue;
            }

            $a = $xpath->query('./a', $li)->item(0);
            if ($a) {
                $menu[] = ['label' => self::clean($a->textContent), 'href' => trim($a->getAttribute('href')), 'children' => []];
            }
        }

        return $menu;
    }

    /** Fills the menu table from the navbar that is live now. Returns how many items were created. */
    public static function importFromNavbar(): int
    {
        $html = SitePart::htmlFor('navbar') ?? view('partials.navbar')->render();
        $menu = self::parse($html);
        $count = 0;

        DB::transaction(function () use ($menu, &$count) {
            foreach ($menu as $i => $top) {
                $parent = MenuItem::create(['label' => $top['label'], 'sort_order' => $i + 1] + self::linkFor($top['href']));
                $count++;
                foreach ($top['children'] as $j => $child) {
                    MenuItem::create([
                        'parent_id' => $parent->id,
                        'label' => $child['label'],
                        'sort_order' => $j + 1,
                    ] + self::linkFor($child['href']));
                    $count++;
                }
            }
        });

        return $count;
    }

    /** Links to a CMS page when the address matches one, otherwise keeps it as a custom URL. */
    private static function linkFor(?string $href): array
    {
        if ($href === null || $href === '') {
            return [];
        }

        if (str_starts_with($href, '/') && !str_starts_with($href, '//')) {
            $path = trim((string) parse_url($href, PHP_URL_PATH), '/');
            $page = Page::where('path', $path)->first();
            if ($page) {
                return ['page_id' => $page->id];
            }
        }

        return ['url' => $href];
    }

    private static function clean(string $text): string
    {
        $text = str_replace("\u{25BE}", '', $text);

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}
