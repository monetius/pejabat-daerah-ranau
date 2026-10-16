<?php

namespace App\Http\Middleware;

use App\Support\TwoFactor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactor
{
    public function __construct(public TwoFactor $twoFactor) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->twoFactor->isVerified($request, $request->user())) {
            if ($request->isMethod('GET')) {
                $request->session()->put('url.intended', $request->fullUrl());
            }
            Auth::logout();
            $request->session()->forget(['two_factor_pending', 'two_factor_verified']);
            $request->session()->regenerate();

            return $request->expectsJson()
                ? response()->json(['message' => __('Pengesahan e-mel diperlukan. Sila log masuk semula.')], 401)
                : redirect()->route('admin.login')->with('status', __('Pengesahan e-mel diperlukan. Sila log masuk semula.'));
        }

        return $next($request);
    }
}
