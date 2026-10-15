@extends('admin.layout')
@section('title', __('Dashboard'))
@section('content')
    <div class="page-heading"><div><p class="eyebrow">{{ __('RUANG PENGURUSAN') }}</p><h1>{{ __('Dashboard') }}</h1><p class="page-intro">{{ __('Selamat datang, :name. Apa yang ingin dikemas kini hari ini?', ['name' => auth()->user()->name]) }}</p></div><a class="btn" href="{{ route('admin.announcements.create') }}">{{ __('+ Cipta hebahan') }}</a></div>
    <div class="stats">
        <div class="card stat-card"><span class="stat-label">{{ __('Jumlah halaman') }}</span><b>{{ $pageCount }}</b><a href="{{ route('admin.pages.index') }}">{{ __('Urus halaman →') }}</a></div>
        <div class="card stat-card"><span class="stat-label">{{ __('Hebahan & berita') }}</span><b>{{ $announcementCount }}</b><a href="{{ route('admin.announcements.index') }}">{{ __('Urus hebahan →') }}</a></div><div class="card stat-card accent"><span class="stat-label">{{ __('Hebahan diterbitkan') }}</span><b>{{ $publishedCount }}</b><span>{{ __('Dipaparkan kepada orang awam') }}</span></div>
    </div>
    <div class="card" style="margin-top:20px">
        <div class="row" style="justify-content:space-between">
            <strong>{{ __('Halaman dikemas kini terkini') }}</strong>
            <a class="btn" href="{{ route('admin.pages.index') }}">{{ __('Semua halaman') }}</a>
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
                    <td>{{ __('Belum ada halaman. Buka Halaman untuk menambah kandungan baharu.') }}</td>
                </tr>
            @endforelse
        </table>
    </div>
    <div class="card">
        <div class="row" style="justify-content:space-between">
            <strong>{{ __('Hebahan terkini') }}</strong>
            <a class="btn" href="{{ route('admin.announcements.create') }}">{{ __('+ Hebahan baru') }}</a>
        </div>
        <table>
            @forelse ($latest as $a)
                <tr>
                    <td><a href="{{ route('admin.announcements.edit', $a) }}">{{ $a->title }}</a></td>
                    <td><span class="badge {{ $a->publicationState() }}">{{ __($a->publicationLabel()) }}</span></td>
                    <td>{{ $a->updated_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td>{{ __('Tiada hebahan lagi.') }}</td>
                </tr>
            @endforelse
        </table>
    </div>
@endsection
