@extends('admin.layout')
@section('title', __('Sahkan e-mel anda'))
@section('content')
    <div class="card login-card two-factor-challenge" @if ($hasSentCode) data-verification-timer
        data-expires-at="{{ $expiresAt }}" data-server-now="{{ $serverNow }}" data-resend-at="{{ $resendAvailableAt }}"
        data-expiry-label="{{ __('Kod tamat dalam :time.') }}" data-expired-label="{{ __('Kod telah tamat tempoh. Sila kembali ke log masuk.') }}"
        data-resend-label="{{ __('Hantar semula kod') }}" data-resend-wait-label="{{ __('Hantar semula dalam :time') }}" @endif>
        @include('admin.brand')
        <span class="security-symbol">@include('admin.icon', ['name' => 'security'])</span>
        <h1>{{ $hasSentCode ? __('Semak peti masuk anda') : __('Sahkan log masuk anda') }}</h1>
        <p class="page-intro">{{ $hasSentCode ? __('Kami menghantar kod 6 digit ke :email.', ['email' => $maskedEmail]) : __('Hantar kod pengesahan ke :email untuk meneruskan.', ['email' => $maskedEmail]) }}</p>
        <p class="hint">{{ $hasSentCode ? __('Masukkan kod untuk melengkapkan log masuk. Kod sah selama 10 minit. Semak folder spam jika perlu.') : __('Klik Hantar kod apabila anda bersedia. Kod dan kira detik hanya bermula selepas kod dihantar.') }}</p>
        @if ($errors->any())<div class="errbox" role="alert">{{ $errors->first() }}</div>@endif
        @if ($hasSentCode)
        <form method="POST" action="{{ route('admin.two-factor.verify') }}">
            @csrf
            <label for="verification-code">{{ __('Kod pengesahan') }}</label>
            <input id="verification-code" class="verification-code" type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required autofocus aria-describedby="code-expiry">
            <p class="hint" id="code-expiry" role="timer">{{ __('Kod tamat dalam :time.', ['time' => gmdate('i:s', max(0, $expiresAt - $serverNow))]) }}</p>
            <p><button class="btn" data-verify-code>{{ __('Sahkan dan log masuk') }}</button></p>
        </form>
        @endif
        <div class="verification-actions">
            <form method="POST" action="{{ route('admin.two-factor.send') }}">
                @csrf
                <button class="btn {{ $hasSentCode ? 'gray' : '' }}" @if ($hasSentCode) data-resend-code @disabled($resendAvailableAt > $serverNow) @endif>
                    @if (!$hasSentCode)
                        {{ __('Hantar kod') }}
                    @elseif ($resendAvailableAt > $serverNow)
                        {{ __('Hantar semula dalam :time', ['time' => sprintf('%02d:%02d', intdiv($resendAvailableAt - $serverNow, 60), ($resendAvailableAt - $serverNow) % 60)]) }}
                    @else
                        {{ __('Hantar semula kod') }}
                    @endif
                </button>
            </form>
            <form method="POST" action="{{ route('admin.two-factor.cancel') }}">@csrf<button class="btn link">{{ __('Kembali ke log masuk') }}</button></form>
        </div>
    </div>
@endsection
@if ($hasSentCode)
@push('foot')
    <script src="{{ asset('assets/js/admin-verification.js') }}?v={{ filemtime(public_path('assets/js/admin-verification.js')) }}" defer></script>
@endpush
@endif
