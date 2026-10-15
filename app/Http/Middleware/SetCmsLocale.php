<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetCmsLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin', 'admin/*')) {
            $locale = $request->cookie('cms_locale', $request->session()->get('cms_locale', 'ms'));
            App::setLocale(in_array($locale, ['ms', 'en'], true) ? $locale : 'ms');
        } else {
            App::setLocale(config('app.locale'));
        }

        return $next($request);
    }
}
