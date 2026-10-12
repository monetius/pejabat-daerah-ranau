<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Logs the admin out when there has been no activity for config('cms.idle_minutes') minutes. */
class AdminIdleTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        $minutes = max(1, (int) config('cms.idle_minutes', 5));
        $now = time();
        $last = $request->session()->get('admin_last_activity');

        if ($last !== null && $now - (int) $last > $minutes * 60) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesi tamat.'], 401);
            }

            return redirect()->route('admin.login')
                ->with('status', "Anda telah dilog keluar kerana tiada aktiviti selama {$minutes} minit.");
        }

        $request->session()->put('admin_last_activity', $now);

        return $next($request);
    }
}
