<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $locale = $request->validate(['locale' => ['required', Rule::in(['ms', 'en'])]])['locale'];
        $request->session()->put('cms_locale', $locale);
        $previous = url()->previous();
        $adminUrl = $request->getSchemeAndHttpHost().'/admin';
        if ($previous !== $adminUrl && ! str_starts_with($previous, $adminUrl.'/')) {
            $previous = $request->user()?->is_admin ? route('admin.dashboard') : route('admin.login');
        }

        return redirect()->to($previous, 303)->withCookie(
            cookie('cms_locale', $locale, 525600, '/', null, $request->isSecure(), true, false, 'lax')
        );
    }
}
