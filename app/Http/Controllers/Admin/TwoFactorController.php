<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\TwoFactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    public function __construct(public TwoFactor $twoFactor)
    {
    }

    public function challenge(Request $request): Response|RedirectResponse
    {
        $user = $this->twoFactor->pendingUser($request);
        if (!$user) {
            return $this->expired($request);
        }
        [$local, $domain] = explode('@', $user->email, 2);
        $expiresAt = $this->twoFactor->codeExpiresAt($request);

        return response()->view('admin.two-factor-challenge', [
            'maskedEmail' => mb_substr($local, 0, 1) . '***@' . $domain,
            'hasSentCode' => $expiresAt !== null,
            'expiresAt' => $expiresAt,
            'serverNow' => now()->timestamp,
            'resendAvailableAt' => now()->timestamp + $this->twoFactor->resendWaitSeconds($request, $user),
        ])->withHeaders(['Cache-Control' => 'no-store, private', 'Pragma' => 'no-cache', 'Referrer-Policy' => 'no-referrer']);
    }

    public function verify(Request $request): RedirectResponse
    {
        if (!$this->twoFactor->pendingUser($request)) {
            return $this->expired($request);
        }
        if ($this->twoFactor->codeExpiresAt($request) === null) {
            throw ValidationException::withMessages(['code' => __('Klik Hantar kod terlebih dahulu untuk menerima kod pengesahan.')]);
        }
        $data = $request->validate(['code' => ['required', 'string', 'max:64']]);
        $user = $this->twoFactor->verify($request, $data['code']);
        if (!$user) {
            throw ValidationException::withMessages(['code' => __('Kod tidak sah atau telah tamat tempoh.')]);
        }
        $remember = (bool) $request->session()->get('two_factor_pending.remember', false);
        Auth::login($user, $remember);
        $this->twoFactor->markVerified($request, $user);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function send(Request $request): RedirectResponse
    {
        $user = $this->twoFactor->pendingUser($request);
        if (!$user) {
            return $this->expired($request);
        }
        $this->twoFactor->sendCode($request, $user);

        return redirect()->route('admin.two-factor.challenge')->with('status', __('Kod baharu telah dihantar. Gunakan kod terkini sahaja.'));
    }

    public function cancel(Request $request): RedirectResponse
    {
        $this->twoFactor->cancel($request);

        return redirect()->route('admin.login');
    }

    private function expired(Request $request): RedirectResponse
    {
        $request->session()->forget('two_factor_pending');

        return redirect()->route('admin.login')->with('status', __('Sesi pengesahan telah tamat. Sila log masuk semula.'));
    }
}
