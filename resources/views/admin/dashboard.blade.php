@extends('admin.layout')
@section('title', 'Papan Pemuka')
@section('content')
    <h1>Papan Pemuka</h1>
    <div class="stats">
        <div class="card"><b>{{ $pageCount }}</b>Halaman</div>
        <div class="card"><b>{{ $announcementCount }}</b>Hebahan</div>
    </div>
    <div class="card" style="margin-top:20px">
        <div class="row" style="justify-content:space-between">
            <strong>Halaman dikemas kini terkini</strong>
            <a class="btn" href="{{ route('admin.pages.index') }}">Semua halaman</a>
        </div>
        <table>
            @forelse ($recentPages as $p)
                <tr>
                    <td><a href="{{ route('admin.pages.edit', $p) }}">{{ $p->title }}</a></td>
                    <td>/{{ $p->path }}</td>
                    <td>{{ $p->updated_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td>Tiada halaman. Jalankan <code>php artisan cms:import</code> untuk import halaman sedia ada.</td>
                </tr>
            @endforelse
        </table>
    </div>
    <div class="card">
        <div class="row" style="justify-content:space-between">
            <strong>Hebahan terkini</strong>
            <a class="btn" href="{{ route('admin.announcements.create') }}">+ Hebahan baru</a>
        </div>
        <table>
            @forelse ($latest as $a)
                <tr>
                    <td><a href="{{ route('admin.announcements.edit', $a) }}">{{ $a->title }}</a></td>
                    <td><span class="badge {{ $a->status }}">{{ $a->status }}</span></td>
                    <td>{{ $a->updated_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td>Tiada hebahan lagi.</td>
                </tr>
            @endforelse
        </table>
    </div>
@endsection