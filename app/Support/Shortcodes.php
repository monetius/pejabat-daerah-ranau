<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class Shortcodes
{
    public const NEWS = '[[hebahan-berita]]';

    public const CALENDAR = '[[kalendar]]';

    public const LAST_UPDATED = '[[kemas-kini]]';

    /** Replaces [[hebahan-berita]] in page HTML with the live announcements section. */
    public static function render(?string $html): string
    {
        $html = (string) $html;

        if (str_contains($html, self::NEWS)) {
            $html = str_replace(self::NEWS, view('partials.news-section')->render(), $html);
        }

        if (str_contains($html, self::CALENDAR)) {
            $html = str_replace(self::CALENDAR, view('partials.home-calendar')->render(), $html);
        }

        return $html;
    }

    /**
     * Fills in the footer's "Kemas Kini Terakhir" date: replaces [[kemas-kini]], or fills the label
     * when it was saved with nothing after it (older footers stored in the CMS).
     */
    public static function renderFooter(string $html): string
    {
        $hasShortcode = str_contains($html, self::LAST_UPDATED);
        $hasEmptyLabel = preg_match('/Kemas\s+Kini\s+Terakhir:\s*<\/p>/i', $html) === 1;
        if (! $hasShortcode && ! $hasEmptyLabel) {
            return $html;
        }

        $date = e(self::lastUpdated()?->timezone('Asia/Kuala_Lumpur')->format('d/m/Y') ?? '');
        $html = str_replace(self::LAST_UPDATED, $date, $html);

        return $hasEmptyLabel
            ? preg_replace('/(Kemas\s+Kini\s+Terakhir:)\s*(<\/p>)/i', '$1 '.$date.'$2', $html, 1)
            : $html;
    }

    /** Most recent change to anything visitors can see: published pages, announcements, navbar/footer. */
    public static function lastUpdated(): ?Carbon
    {
        try {
            $latest = array_filter([
                DB::table('pages')->where('status', 'published')->max('updated_at'),
                DB::table('announcements')->where('status', 'published')->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))->max('updated_at'),
                DB::table('site_parts')->whereIn('key', ['navbar', 'footer'])->max('updated_at'),
            ]);
        } catch (Throwable) {
            return null;
        }

        return $latest === [] ? null : Carbon::parse(max($latest));
    }

    /**
     * Finds the <section> that holds the "Hebahan & Berita" heading and swaps the whole thing
     * for the [[hebahan-berita]] shortcode. Returns null when the section can't be found.
     */
    public static function swapNewsSection(string $html): ?string
    {
        if (! preg_match('/<h2[^>]*>\s*Hebahan\s*(?:&amp;|&)\s*Berita\s*<\/h2>/i', $html, $h, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $headingAt = $h[0][1];
        $start = strrpos(substr($html, 0, $headingAt), '<section');
        if ($start === false) {
            return null;
        }

        $depth = 0;
        $pos = $start;
        $end = null;
        while (preg_match('/<(\/?)section\b[^>]*>/i', $html, $tag, PREG_OFFSET_CAPTURE, $pos)) {
            $depth += $tag[1][0] === '/' ? -1 : 1;
            $pos = $tag[0][1] + strlen($tag[0][0]);
            if ($depth === 0) {
                $end = $pos;
                break;
            }
        }

        if ($end === null) {
            return null;
        }

        return substr($html, 0, $start).self::NEWS.substr($html, $end);
    }
}
