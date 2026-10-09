@extends('admin.layout')
@section('title', 'Halaman')
@section('content')
    <div class="row" style="justify-content:space-between;margin-bottom:12px">
        <h1 style="margin:0">Halaman</h1>
        <a class="btn" href="{{ route('admin.pages.create') }}">+ Halaman baru</a>
    </div>

    <form method="GET" class="row" style="margin-bottom:16px">
        <input type="text" name="q" value="{{ $q }}" placeholder="Cari tajuk atau URL..." style="max-width:320px">
        <button class="btn gray">Cari</button>
        @if ($q !== '')<a href="{{ route('admin.pages.index') }}">Padam carian</a>@endif
    </form>

    @forelse ($groups as $section => $items)
        <div class="card">
            <strong>{{ $section }}</strong>
            <table>
                @foreach ($items as $item)
                    <tr>
                        <td><a href="{{ route('admin.pages.edit', $item) }}">{{ $item->title }}</a></td>
                        <td><code>/{{ $item->path }}</code></td>
                        <td><span class="badge {{ $item->status }}">{{ $item->status }}</span></td>
                        <td class="row">
                            <a href="{{ $item->url() }}" target="_blank">Lihat</a>
                            <a href="{{ route('admin.pages.edit', $item) }}">Sunting</a>
                            @unless ($item->is_system)
                                <form method="POST" action="{{ route('admin.pages.destroy', $item) }}"
                                    onsubmit="return confirm('Padam halaman ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn red" style="padding:3px 10px">Padam</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    @empty
        <div class="card">
            Tiada halaman dijumpai.
            @if ($q === '')<br>Jalankan <code>php artisan cms:import</code> untuk import semua halaman sedia ada ke dalam
            CMS.@endif
        </div>
    @endforelse
@endsection