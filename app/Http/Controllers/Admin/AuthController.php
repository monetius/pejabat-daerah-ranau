<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\TwoFactor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('admin.login');
    }

    public function login(Request $request, TwoFactor $twoFactor): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = 'cms-login:'.hash('sha256', Str::lower($credentials['email']).'|'.$request->ip());
        $ipKey = 'cms-login-ip:'.hash('sha256', (string) $request->ip());
        if (RateLimiter::tooManyAttempts($key, 5) || RateLimiter::tooManyAttempts($ipKey, 30)) {
            throw ValidationException::withMessages(['email' => __('Terlalu banyak percubaan log masuk. Sila cuba lagi sebentar lagi.')]);
        }

        if (Auth::validate($credentials + ['is_admin' => true])) {
            /** @var User $user */
            $user = Auth::getLastAttempted();
            Auth::getProvider()->rehashPasswordIfRequired($user, $credentials);
            RateLimiter::clear($key);
            $twoFactor->beginLogin($request, $user, $request->boolean('remember'));

            return redirect()->route('admin.two-factor.challenge');
        }

        RateLimiter::hit($key, 60);
        RateLimiter::hit($ipKey, 60);

        return back()->withErrors(['email' => __('E-mel atau kata laluan tidak betul.')])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
