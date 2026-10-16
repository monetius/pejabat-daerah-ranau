<?php

namespace App\Support;

class Shortcodes
{
    public const NEWS = '[[hebahan-berita]]';

    public const CALENDAR = '[[kalendar]]';

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
