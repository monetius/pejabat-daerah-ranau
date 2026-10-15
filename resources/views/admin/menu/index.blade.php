@extends('admin.layout')
@section('title', __('Menu Navigasi'))
@section('content')
    <div class="row" style="justify-content:space-between;margin-bottom:12px">
        <h1 style="margin:0">{{ __('Menu Navigasi') }}</h1>
        <a class="btn" href="{{ route('admin.menu.create') }}">{{ __('+ Menu utama baru') }}</a>
    </div>

    @if ($items->isEmpty())
        <div class="card">
            <p>{{ __('Mulakan pengurusan menu dengan menyalin navigasi semasa.') }}</p>
            <p class="hint">{{ __('Klik butang di bawah untuk menyalin menu navbar semasa ke sini. Selepas itu anda boleh tambah, susun dan padam pautan melalui borang mudah.') }}</p>
            <form method="POST" action="{{ route('admin.menu.import') }}">@csrf
                <button class="btn">{{ __('Import menu daripada navbar semasa') }}</button>
            </form>
        </div>
    @else
        <div class="card">
            <p class="hint">{{ __('Menu utama yang mempunyai sub-menu akan menjadi dropdown. Untuk halaman baru: cipta di') }}
                <a href="{{ route('admin.pages.index') }}">{{ __('Halaman') }}</a> {{ __('dahulu (status Diterbitkan), kemudian tambah di sini. Logo dan nama organisasi diurus di') }} <a href="{{ route('admin.parts.index') }}">{{ __('Navbar & Footer') }}</a>.
            </p>
            <table>
                @foreach ($items as $item)
                    @include('admin.menu.row', ['item' => $item, 'sub' => false, 'first' => $loop->first, 'last' => $loop->last])
                    @foreach ($item->children as $kid)
                        @include('admin.menu.row', ['item' => $kid, 'sub' => true, 'first' => $loop->first, 'last' => $loop->last])
                    @endforeach
                @endforeach
            </table>
        </div>
    @endif
@endsection
