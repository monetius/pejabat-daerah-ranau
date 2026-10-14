@extends('admin.layout')
@section('title', 'Log Masuk')
@section('content')
    <div class="card login-card">
        @include('admin.brand')
        <h1>Selamat kembali.</h1><p class="page-intro">Log masuk untuk mengurus kandungan laman web.</p>
        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <label for="email">E-mel</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            @error('email')
            <div class="err">{{ $message }}</div>@enderror

            <label for="password">Kata laluan</label>
            <input type="password" id="password" name="password" required>

            <label style="font-weight:400"><input type="checkbox" name="remember" value="1"> Ingat saya</label>
            <p><button class="btn">Log masuk</button></p>
        </form>
    </div>
@endsection
