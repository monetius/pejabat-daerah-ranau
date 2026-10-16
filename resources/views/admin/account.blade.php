@extends('admin.layout')
@section('title', __('Akaun & keselamatan'))
@section('content')
    <h1>{{ __('Akaun & keselamatan') }}</h1>
    <p class="page-intro">{{ __('Urus kata laluan dan keselamatan akaun CMS anda.') }}</p>
    @if ($errors->any())<div class="errbox" role="alert"><ul>@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>@endif
    <div class="card security-card">
        <div class="security-heading"><span class="security-symbol">@include('admin.icon', ['name' => 'security'])</span><div><h2>{{ __('Pengesahan e-mel') }}</h2><p class="hint">{{ __('Setiap log masuk memerlukan kata laluan dan kod yang dihantar ke e-mel akaun anda.') }}</p></div></div>
        <p>{{ __('E-mel akaun') }}: <strong>{{ auth()->user()->email }}</strong></p>
        <h2>{{ __('Tukar kata laluan') }}</h2>
        <form method="POST" action="{{ route('admin.account.update') }}">
            @csrf @method('PUT')
            <label for="current-password">{{ __('Kata laluan semasa') }}</label><input id="current-password" type="password" name="current_password" autocomplete="current-password" required>
            <label for="new-password">{{ __('Kata laluan baharu') }}</label><input id="new-password" type="password" name="password" autocomplete="new-password" minlength="12" maxlength="128" aria-describedby="password-hint" required>
            <p class="hint" id="password-hint">{{ __('Gunakan sekurang-kurangnya 12 aksara. Frasa laluan yang panjang lebih mudah diingati.') }}</p>
            <label for="confirm-password">{{ __('Sahkan kata laluan baharu') }}</label><input id="confirm-password" type="password" name="password_confirmation" autocomplete="new-password" minlength="12" maxlength="128" required>
            <p><button class="btn">{{ __('Simpan kata laluan') }}</button></p>
        </form>
    </div>
@endsection
