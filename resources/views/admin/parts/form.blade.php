@extends('admin.layout')
@section('title', 'Edit ' . $part->label)
@section('content')
    <h1>{{ $part->label }}</h1>
    <p class="page-intro">{{ __('Perubahan teks, gambar dan pautan di sini terpakai pada semua halaman.') }}</p>
    @if ($part->key === 'navbar')
        <p class="flash">{{ __('Untuk menambah atau menyusun pautan navigasi, gunakan') }} <a href="{{ route('admin.menu.index') }}">{{ __('pengurusan Menu') }}</a>.</p>
    @endif
    <div class="card">
        <form method="POST" action="{{ route('admin.parts.update', $part) }}">
            @csrf @method('PUT')
            @include('admin.content-editor')
            <p class="row"><button class="btn">{{ __('Simpan perubahan') }}</button><a class="btn gray" href="{{ route('admin.parts.index') }}">{{ __('Batal') }}</a></p>
        </form>
    </div>
@endsection
