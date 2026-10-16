<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Throwable;

class TwoFactor
{
    public function fingerprint(User $user): string
    {
        return hash_hmac('sha256', json_encode([
            $user->id, $user->email, $user->getRawOriginal('password'), $user->is_admin,
        ], JSON_THROW_ON_ERROR), (string) config('app.key'));
    }

    public function beginLogin(Request $request, User $user, bool $remember): void
    {
        $request->session()->forget(['two_factor_pending', 'two_factor_verified']);
        $request->session()->regenerate();
        $this->sendCode($request, $user, $remember, now()->timestamp);
    }

    public function resend(Request $request, User $user): void
    {
        $pending = $request->session()->get('two_factor_pending');
        $this->sendCode($request, $user, (bool) $pending['remember'], (int) $pending['started']);
    }

    public function pendingUser(Request $request): ?User
    {
        $pending = $request->session()->get('two_factor_pending');
        if (! is_array($pending) || ! isset($pending['id'], $pending['started'], $pending['fingerprint'], $pending['challenge'])
            || now()->timestamp - $pending['started'] >= 600 || $pending['started'] > now()->timestamp) {
            return null;
        }
        $user = User::find($pending['id']);
        $challenge = Cache::get('cms-email-code:'.$pending['id']);

        return $user?->is_admin && is_array($challenge)
            && hash_equals($this->fingerprint($user), $pending['fingerprint'])
            && hash_equals($challenge['id'], $pending['challenge'])
            && $challenge['expires'] > now()->timestamp ? $user : null;
    }

    public function markVerified(Request $request, User $user): void
    {
        $request->session()->forget('two_factor_pending');
        $request->session()->regenerate();
        $request->session()->put('two_factor_verified', $this->fingerprint($user));
        $request->session()->put('admin_last_activity', time());
    }

    public function isVerified(Request $request, User $user): bool
    {
        $proof = $request->session()->get('two_factor_verified');

        return is_string($proof) && hash_equals($this->fingerprint($user), $proof);
    }

    public function verify(Request $request, string $code): ?User
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return null;
        }

        return $this->locked($user, function () use ($request, $code): ?User {
            $user = $this->pendingUser($request);
            if (! $user) {
                return null;
            }
            $key = 'cms-email-code:'.$user->id;
            $challenge = Cache::get($key);
            $attemptKey = 'cms-email-attempts:'.$user->id;
            if ($challenge['attempts'] >= 5 || RateLimiter::tooManyAttempts($attemptKey, 5)) {
                throw ValidationException::withMessages(['code' => __('Terlalu banyak percubaan. Sila cuba lagi dalam 10 minit.')]);
            }
            RateLimiter::hit($attemptKey, 600);
            $challenge['attempts']++;
            Cache::put($key, $challenge, max(1, $challenge['expires'] - now()->timestamp));
            if (! hash_equals($challenge['hash'], $this->codeHash($challenge['id'], $code))) {
                return null;
            }
            Cache::forget($key);
            RateLimiter::clear($attemptKey);

            return $user;
        });
    }

    public function cancel(Request $request): void
    {
        $user = $this->pendingUser($request);
        if ($user) {
            $this->locked($user, function () use ($request, $user): void {
                if ($this->pendingUser($request)) {
                    Cache::forget('cms-email-code:'.$user->id);
                }
            });
        }
        $request->session()->forget(['two_factor_pending', 'two_factor_verified']);
        $request->session()->regenerate();
    }

    private function sendCode(Request $request, User $user, bool $remember, int $started): void
    {
        if (! $this->hasDeliveryTransport((string) config('mail.default'))) {
            throw ValidationException::withMessages(['email' => __('Penghantaran e-mel belum dikonfigurasi. Hubungi pentadbir untuk menyediakan SMTP sebelum log masuk.')]);
        }
        $fingerprint = $this->fingerprint($user);
        $this->locked($user, function () use ($request, $user, $remember, $started, $fingerprint): void {
            $user->refresh();
            if (! $user->is_admin || ! hash_equals($fingerprint, $this->fingerprint($user))) {
                throw ValidationException::withMessages(['email' => __('Sesi pengesahan telah tamat. Sila log masuk semula.')]);
            }
            $minuteKey = 'cms-email-send-minute:'.$user->id;
            $hourKey = 'cms-email-send-hour:'.$user->id;
            $ipKey = 'cms-email-send-ip:'.hash('sha256', (string) $request->ip());
            if (RateLimiter::tooManyAttempts($minuteKey, 1) || RateLimiter::tooManyAttempts($hourKey, 5) || RateLimiter::tooManyAttempts($ipKey, 20)) {
                $seconds = max(RateLimiter::availableIn($minuteKey), RateLimiter::tooManyAttempts($hourKey, 5) ? RateLimiter::availableIn($hourKey) : 0,
                    RateLimiter::tooManyAttempts($ipKey, 20) ? RateLimiter::availableIn($ipKey) : 0);
                throw ValidationException::withMessages(['email' => __('Sila tunggu :seconds saat sebelum meminta kod baharu.', ['seconds' => $seconds])]);
            }
            RateLimiter::hit($minuteKey, 60);
            RateLimiter::hit($hourKey, 3600);
            RateLimiter::hit($ipKey, 3600);
            $id = bin2hex(random_bytes(32));
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = min($started + 600, now()->timestamp + 600);
            try {
                Mail::to($user->email)->send(new CmsLoginCode($code));
            } catch (Throwable $exception) {
                Log::warning('CMS login email delivery failed.', ['exception_type' => $exception::class]);
                throw ValidationException::withMessages(['email' => __('Kod tidak dapat dihantar. Semak tetapan e-mel atau cuba lagi kemudian.')]);
            }
            Cache::put('cms-email-code:'.$user->id, [
                'id' => $id, 'hash' => $this->codeHash($id, $code), 'expires' => $expires, 'attempts' => 0,
            ], max(1, $expires - now()->timestamp));
            $request->session()->put('two_factor_pending', [
                'id' => $user->id, 'remember' => $remember, 'started' => $started,
                'fingerprint' => $this->fingerprint($user), 'challenge' => $id,
            ]);
        });
    }

    private function codeHash(string $id, string $code): string
    {
        return hash_hmac('sha256', $id.'|'.$code, (string) config('app.key'));
    }

    /** @param list<string> $visited */
    private function hasDeliveryTransport(string $name, array $visited = []): bool
    {
        if (in_array($name, $visited, true)) {
            return false;
        }
        $mailer = config('mail.mailers.'.$name, []);
        if (in_array($mailer['transport'] ?? '', ['failover', 'roundrobin'], true)) {
            $children = $mailer['mailers'] ?? [];

            return count($children) > 0 && array_all($children, fn (string $child): bool => $this->hasDeliveryTransport($child, [...$visited, $name]));
        }

        return in_array($mailer['transport'] ?? '', ['smtp', 'sendmail', 'ses', 'ses-v2', 'mailgun', 'postmark', 'resend'], true);
    }

    private function locked(User $user, callable $callback): mixed
    {
        try {
            return Cache::lock('cms-email-lock:'.$user->id, 60)->block(5, $callback);
        } catch (LockTimeoutException) {
            throw ValidationException::withMessages(['code' => __('Permintaan sedang diproses. Sila cuba lagi.')]);
        }
    }
}
