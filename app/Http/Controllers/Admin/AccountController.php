<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\TwoFactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    public function edit(): Response
    {
        return response()->view('admin.account')->header('Cache-Control', 'no-store, private');
    }

    public function update(Request $request, TwoFactor $twoFactor): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:12', 'max:128', 'confirmed', 'different:current_password'],
        ]);
        DB::transaction(function () use ($request, $data, $twoFactor): void {
            $user = $request->user()->newQuery()->lockForUpdate()->findOrFail($request->user()->id);
            if (!$twoFactor->isVerified($request, $user) || !Hash::check($data['current_password'], $user->password)) {
                throw ValidationException::withMessages(['current_password' => __('Kata laluan semasa tidak betul. Sila cuba lagi.')]);
            }
            $user->forceFill(['password' => $data['password'], 'remember_token' => null])->save();
            $request->user()->setRawAttributes($user->getAttributes(), true);
            $twoFactor->markVerified($request, $user);
        });

        return redirect()->route('admin.account.edit')->with('status', __('Kata laluan telah dikemas kini. Sesi lain perlu log masuk semula.'));
    }
}
