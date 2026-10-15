@extends('layouts.site')

@section('title', 'Hebahan & Berita - Pejabat Daerah Ranau')
@section('description', 'Semua hebahan, berita dan notis rasmi Pejabat Daerah Ranau.')
@section('body_class', 'has-banner')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/dasar.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/hebahan.css') }}">
@endpush

@section('content')
  <div class="page-header">
    <div class="page-header-media"><img src="/assets/img/banner.jpg" alt="" /></div>
    <div class="page-header-scrim"></div>
    <div class="container">
      <ul class="breadcrumb">
        <li><a href="/">Laman Utama</a></li>
        <li>Hebahan &amp; Berita</li>
      </ul>
      <h1>Hebahan &amp; Berita</h1>
    </div>
  </div>

  <section class="ds-section hb-page">
    <div class="container">
      <div class="hb-toolbar">
        <div class="hb-filters">
          <a href="{{ route('announcements.index', array_filter(['q' => $q])) }}" class="{{ $category === '' ? 'on' : '' }}">Semua</a>
          @foreach (\App\Models\Announcement::CATEGORIES as $key => $label)
            <a href="{{ route('announcements.index', array_filter(['kategori' => $key, 'q' => $q])) }}"
              class="{{ $category === $key ? 'on' : '' }}">{{ $label }}</a>
          @endforeach
        </div>
        <form class="hb-search" method="GET" action="{{ route('announcements.index') }}">
          @if ($category !== '')<input type="hidden" name="kategori" value="{{ $category }}">@endif
          <input type="search" name="q" value="{{ $q }}" placeholder="Cari hebahan atau berita..." aria-label="Cari">
          <button type="submit">Cari</button>
        </form>
      </div>

      @if ($items->isEmpty())
        <p class="hb-empty">Tiada hasil ditemui.</p>
      @else
        <div class="hb-list">
          @foreach ($items as $item)
            <a class="hb-item" href="{{ route('announcements.show', $item->slug) }}">
              <div class="hb-item-media">@include('partials.news-media', ['item' => $item])</div>
              <div class="hb-item-body">
                <div class="hb-meta">
                  <span class="hb-tag hb-tag-{{ $item->category }}">{{ $item->categoryLabel() }}</span>
                  <time>{{ $item->published_at?->format('d/m/Y') }}</time>
                  @include('partials.news-author', ['item' => $item])
                </div>
                <h3>{{ $item->title }}</h3>
                @if ($item->summary)<p>{{ $item->summary }}</p>@endif
              </div>
            </a>
          @endforeach
        </div>

        @if ($items->hasPages())
          <nav class="hb-pager" aria-label="Halaman">
            @if ($items->onFirstPage())<span class="dis">← Sebelum</span>@else<a href="{{ $items->previousPageUrl() }}">← Sebelum</a>@endif
            <span>Halaman {{ $items->currentPage() }} / {{ $items->lastPage() }}</span>
            @if ($items->hasMorePages())<a href="{{ $items->nextPageUrl() }}">Seterusnya →</a>@else<span class="dis">Seterusnya →</span>@endif
          </nav>
        @endif
      @endif
    </div>
  </section>
@endsection
