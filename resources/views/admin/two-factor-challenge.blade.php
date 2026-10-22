@extends('admin.layout')
@section('title', __('Sahkan e-mel anda'))
@section('content')
    <div class="card login-card two-factor-challenge" @if ($hasSentCode) data-verification-timer
        data-expires-at="{{ $expiresAt }}" data-server-now="{{ $serverNow }}" data-resend-at="{{ $resendAvailableAt }}"
        data-expiry-label="{{ __('Kod tamat dalam :time.') }}" data-expired-label="{{ __('Kod telah tamat tempoh. Sila kembali ke log masuk.') }}"
        data-resend-label="{{ __('Hantar semula kod') }}" data-resend-wait-label="{{ __('Hantar semula kod dalam :time') }}" @endif>
        @include('admin.brand')
        <span class="security-symbol">@include('admin.icon', ['name' => 'security'])</span>
        <h1>{{ __('Sahkan log masuk anda') }}</h1>
        <p class="page-intro">{{ $hasSentCode ? __('Kami menghantar kod 6 digit ke :email.', ['email' => $maskedEmail]) : __('Hantar kod pengesahan ke :email untuk meneruskan.', ['email' => $maskedEmail]) }}</p>
        <p class="hint" id="verification-instructions">{{ $hasSentCode ? __('Masukkan kod untuk melengkapkan log masuk. Kod sah selama 3 minit. Semak folder spam jika perlu.') : __('Kod belum dihantar. Pilih Hantar semula kod untuk mencuba lagi.') }}</p>
        @if ($errors->any())<div class="errbox" role="alert">{{ $errors->first() }}</div>@endif
        <form id="verification-send-form" method="POST" action="{{ route('admin.two-factor.send') }}">@csrf</form>
        <form method="POST" action="{{ route('admin.two-factor.verify') }}">
            @csrf
            <label id="verification-code-label" for="verification-digit-1">{{ __('Kod pengesahan') }}</label>
            <input id="verification-code" type="hidden" name="code">
            <div class="verification-digits" role="group" aria-labelledby="verification-code-label" aria-describedby="{{ $hasSentCode ? 'code-expiry' : 'verification-instructions' }}">
                @for ($digit = 1; $digit <= 6; $digit++)
                    <input id="verification-digit-{{ $digit }}" class="verification-digit" type="text" inputmode="numeric" pattern="[0-9]" maxlength="{{ $digit === 1 ? 6 : 1 }}" autocomplete="{{ $digit === 1 ? 'one-time-code' : 'off' }}" aria-label="{{ __('Angka :number daripada 6', ['number' => $digit]) }}" required @disabled(!$hasSentCode) @if ($hasSentCode && $digit === 1) autofocus @endif>
                @endfor
            </div>
            @if ($hasSentCode)
            <p class="hint" id="code-expiry" role="timer">{{ __('Kod tamat dalam :time.', ['time' => gmdate('i:s', max(0, $expiresAt - $serverNow))]) }}</p>
            @endif
            <p><button class="btn" data-verify-code @disabled(!$hasSentCode)>{{ __('Sahkan') }}</button></p>
            <div class="verification-send-row">
                <span>{{ __('Perlukan kod baharu?') }}</span>
                <button type="submit" form="verification-send-form" class="verification-resend" @if ($hasSentCode) data-resend-code @disabled($resendAvailableAt > $serverNow) @endif>
                    @if ($hasSentCode && $resendAvailableAt > $serverNow)
                        {{ __('Hantar semula kod dalam :time', ['time' => sprintf('%02d:%02d', intdiv($resendAvailableAt - $serverNow, 60), ($resendAvailableAt - $serverNow) % 60)]) }}
                    @else
                        {{ __('Hantar semula kod') }}
                    @endif
                </button>
            </div>
        </form>
        <div class="verification-actions">
            <form method="POST" action="{{ route('admin.two-factor.cancel') }}">@csrf<button class="verification-resend">{{ __('Batal') }}</button></form>
        </div>
    </div>
@endsection
@if ($hasSentCode)
@push('foot')
    <script src="{{ asset('assets/js/admin-verification.js') }}?v={{ filemtime(public_path('assets/js/admin-verification.js')) }}" defer></script>
@endpush
@endif
