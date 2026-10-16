@extends('admin.layout')
@section('title', __('Sahkan e-mel anda'))
@section('content')
    <div class="card login-card two-factor-challenge">
        @include('admin.brand')
        <span class="security-symbol">@include('admin.icon', ['name' => 'security'])</span>
        <h1>{{ __('Semak peti masuk anda') }}</h1>
        <p class="page-intro">{{ __('Kami menghantar kod 6 digit ke :email.', ['email' => $maskedEmail]) }}</p>
        <p class="hint">{{ __('Masukkan kod untuk melengkapkan log masuk. Kod sah selama 10 minit. Semak folder spam jika perlu.') }}</p>
        @if ($errors->any())<div class="errbox" role="alert">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('admin.two-factor.verify') }}">
            @csrf
            <label for="verification-code">{{ __('Kod pengesahan') }}</label>
            <input id="verification-code" class="verification-code" type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required autofocus aria-describedby="code-expiry">
            <p class="hint" id="code-expiry" data-expires-at="{{ $expiresAt }}">{{ __('Sesi pengesahan tamat selepas 10 minit.') }}</p>
            <p><button class="btn">{{ __('Sahkan dan log masuk') }}</button></p>
        </form>
        <div class="verification-actions">
            <form method="POST" action="{{ route('admin.two-factor.resend') }}">@csrf<button class="btn gray">{{ __('Hantar semula kod') }}</button></form>
            <form method="POST" action="{{ route('admin.two-factor.cancel') }}">@csrf<button class="btn link">{{ __('Kembali ke log masuk') }}</button></form>
        </div>
    </div>
@endsection
