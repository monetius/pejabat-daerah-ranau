@extends('admin.layout')
@section('title', __('Halaman'))
@section('content')
    <div class="row" style="justify-content:space-between;margin-bottom:12px">
        <h1 style="margin:0">{{ __('Halaman') }}</h1>
        <a class="btn" href="{{ route('admin.pages.create') }}">{{ __('+ Halaman baru') }}</a>
    </div>

    <form method="GET" class="row" style="margin-bottom:16px">
        <input type="text" name="q" value="{{ $q }}" placeholder="{{ __('Cari tajuk atau URL...') }}" style="max-width:320px">
        <button class="btn gray">{{ __('Cari') }}</button>
        @if ($q !== '')<a href="{{ route('admin.pages.index') }}">{{ __('Padam carian') }}</a>@endif
    </form>

    @forelse ($groups as $section => $items)
        <div class="card">
            <strong>{{ $section === 'Lain-lain' ? __('Lain-lain') : $section }}</strong>
            <table>
                @foreach ($items as $item)
                    <tr>
                        <td><a href="{{ route('admin.pages.edit', $item) }}">{{ $item->title }}</a></td>
                        <td><code>/{{ $item->path }}</code></td>
                        <td><span class="badge {{ $item->status }}">{{ __($item->status === 'published' ? 'Diterbitkan' : 'Draf') }}</span></td>
                        <td class="row">
                            <a href="{{ $item->url() }}" target="_blank">{{ __('Lihat') }}</a>
                            <a href="{{ route('admin.pages.edit', $item) }}">{{ __('Edit') }}</a>
                            @unless ($item->is_system)
                                <form method="POST" action="{{ route('admin.pages.destroy', $item) }}"
                                    onsubmit="return confirm(@js(__('Padam halaman ini?')))">
                                    @csrf @method('DELETE')
                                    <button class="btn red" style="padding:3px 10px">{{ __('Padam') }}</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    @empty
        <div class="card">
            {{ __('Tiada halaman dijumpai.') }}
            @if ($q === '')<br>{{ __('Klik “Halaman Baru” untuk menyediakan kandungan baharu.') }}@endif
        </div>
    @endforelse
@endsection
