@extends('admin.layout')
@section('title', __('Log Masuk'))
@section('content')
    <div class="card login-card">
        @include('admin.brand')
        <h1>{{ __('Selamat kembali.') }}</h1><p class="page-intro">{{ __('Log masuk untuk mengurus kandungan laman web.') }}</p>
        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <label for="email">{{ __('E-mel') }}</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            @error('email')
            <div class="err">{{ $message }}</div>@enderror

            <label for="password">{{ __('Kata laluan') }}</label>
            <input type="password" id="password" name="password" required>

            <label style="font-weight:400"><input type="checkbox" name="remember" value="1"> {{ __('Ingat saya') }}</label>
            <p><button class="btn">{{ __('Log masuk') }}</button></p>
        </form>
    </div>
@endsection
