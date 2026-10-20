<?php

namespace App\Support;

use Dom\Element;
use Dom\HTMLDocument;
use Dom\Node;
use Dom\Text;
use Illuminate\Support\Facades\Lang;

class HomepageTranslation
{
    public static function isEnglish(): bool
    {
        return request()->path() === '/' && SiteNavigation::locale() === 'en';
    }

    public static function render(string $html): string
    {
        if (! self::isEnglish()) {
            return $html;
        }

        $document = HTMLDocument::createFromString('<!doctype html><html><head><meta charset="UTF-8"></head><body><div id="homepage-translation-root">'.$html.'</div></body></html>', LIBXML_NOERROR, 'UTF-8');
        $root = $document->getElementById('homepage-translation-root');
        foreach ($root->querySelectorAll('.hb-feature-body h3, .hb-card-text h4, .hb-author, .hb-feature-media, .hb-card-media') as $authored) {
            $authored->setAttribute('data-authored-content', '');
        }
        self::translate($root, Lang::get('homepage', [], 'en'));

        return $root->innerHTML;
    }

    /** @param array<string, string> $labels */
    private static function translate(Node $node, array $labels): void
    {
        if ($node instanceof Element) {
            if (in_array($node->localName, ['script', 'style', 'svg'], true) || $node->hasAttribute('data-authored-content')) {
                return;
            }
            foreach (['aria-label', 'title', 'alt', 'data-title', 'data-desc', 'data-credit'] as $attribute) {
                if ($node->hasAttribute($attribute)) {
                    $node->setAttribute($attribute, self::label($node->getAttribute($attribute), $labels));
                }
            }
        } elseif ($node instanceof Text) {
            $node->textContent = self::label($node->textContent, $labels);
        }
        foreach ($node->childNodes as $child) {
            self::translate($child, $labels);
        }
    }

    /** @param array<string, string> $labels */
    private static function label(string $value, array $labels): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim($value));
        if (! isset($labels[$normalized])) {
            return $value;
        }

        return preg_replace('/\S(?:[\s\S]*\S)?/u', $labels[$normalized], $value, 1);
    }
}
