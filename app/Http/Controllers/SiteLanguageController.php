<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiteLanguageController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'locale' => ['required', Rule::in(['ms', 'en'])],
            'translate_page' => ['nullable', 'boolean'],
        ]);
        $request->session()->put('site_locale', $data['locale']);
        $previous = url()->previous();
        $origin = $request->getSchemeAndHttpHost();
        if (!str_starts_with($previous, $origin . '/') || preg_match('#^/admin(?:/|$)#', (string) parse_url($previous, PHP_URL_PATH))) {
            $previous = route('home');
        }
        $response = redirect()->to($previous, 303)->withCookie(
            cookie('site_locale', $data['locale'], 525600, '/', null, $request->isSecure(), true, false, 'lax')
        );
        if (!$request->boolean('translate_page')) {
            $response->withCookie(cookie()->forget('googtrans', '/'));
            $response->withCookie(cookie()->forget('googtrans', '/', $request->getHost()));
        }

        return $response;
    }
}
