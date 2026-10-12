@extends('admin.layout')
@section('title', 'Papan Pemuka')
@section('content')
    <div class="page-heading"><div><p class="eyebrow">RUANG PENGURUSAN</p><h1>Papan Pemuka</h1><p class="page-intro">Selamat datang, {{ auth()->user()->name }}. Apa yang ingin dikemas kini hari ini?</p></div><a class="btn" href="{{ route('admin.announcements.create') }}">+ Cipta hebahan</a></div>
    <div class="stats">
        <div class="card stat-card"><span class="stat-label">Jumlah halaman</span><b>{{ $pageCount }}</b><a href="{{ route('admin.pages.index') }}">Urus halaman &rarr;</a></div>
        <div class="card stat-card"><span class="stat-label">Hebahan &amp; berita</span><b>{{ $announcementCount }}</b><a href="{{ route('admin.announcements.index') }}">Urus hebahan &rarr;</a></div><div class="card stat-card accent"><span class="stat-label">Hebahan diterbitkan</span><b>{{ $publishedCount }}</b><span>Dipaparkan kepada orang awam</span></div>
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
                    <td><span class="badge {{ $a->publicationState() }}">{{ $a->publicationLabel() }}</span></td>
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