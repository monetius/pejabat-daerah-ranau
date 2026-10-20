<?php

namespace App\Support;

use Dom\Element;
use Dom\HTMLDocument;
use Dom\Node;
use Dom\Text;
use Illuminate\Support\Facades\Lang;

class SiteNavigation
{
    public static function locale(): string
    {
        $request = request();
        $locale = $request->cookie('site_locale', $request->hasSession() ? $request->session()->get('site_locale', 'ms') : 'ms');

        return in_array($locale, ['ms', 'en'], true) ? $locale : 'ms';
    }

    public static function render(string $html): string
    {
        $document = HTMLDocument::createFromString('<!doctype html><html><head><meta charset="UTF-8"></head><body><div id="site-navigation-root">'.$html.'</div></body></html>', LIBXML_NOERROR, 'UTF-8');
        $root = $document->getElementById('site-navigation-root');
        $navbar = $root->querySelector('nav.navbar');
        $menu = $root->querySelector('#navLinks');
        if ($navbar === null || $menu === null) {
            return $html;
        }

        $locale = self::locale();
        if ($locale === 'en') {
            self::translate($root);
            $root->querySelector('.skip-link')?->setAttribute('lang', 'en');
        }
        $navbar->setAttribute('lang', $locale);
        $navbar->setAttribute('translate', 'no');
        $navbar->setAttribute('class', trim($navbar->getAttribute('class').' notranslate'));
        $item = $document->createElement('li');
        $item->setAttribute('class', 'nav-language-item');
        $item->innerHTML = view('partials.nav-language', ['locale' => $locale])->render();
        $menu->appendChild($item);

        return $root->innerHTML;
    }

    private static function translate(Node $node): void
    {
        if ($node instanceof Element) {
            if (in_array($node->localName, ['script', 'style', 'svg'], true)) {
                return;
            }
            foreach (['aria-label', 'title', 'alt'] as $attribute) {
                if ($node->hasAttribute($attribute)) {
                    $translated = Lang::get($node->getAttribute($attribute), [], 'en');
                    if (is_string($translated)) {
                        $node->setAttribute($attribute, $translated);
                    }
                }
            }
        } elseif ($node instanceof Text) {
            $label = trim($node->textContent);
            if ($label !== '') {
                $translated = Lang::get('navbar.'.$label, [], 'en');
                if ($translated === 'navbar.'.$label) {
                    $translated = Lang::get($label, [], 'en');
                }
                if (is_string($translated) && $translated !== $label) {
                    $node->textContent = str_replace($label, $translated, $node->textContent);
                }
            }
        }
        foreach ($node->childNodes as $child) {
            self::translate($child);
        }
    }
}
