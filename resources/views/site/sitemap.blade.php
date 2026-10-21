@extends('layouts.site')

@section('title', 'Peta Laman - Pejabat Daerah Ranau')
@section('body_class', 'information-page')
@section('description', 'Senarai halaman dan berita yang diterbitkan di portal Pejabat Daerah Ranau.')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/dasar.css') }}?v={{ filemtime(public_path('assets/css/dasar.css')) }}">
@endpush

@section('content')
  <div class="page-header page-header--plain">
    <div class="container">
      <ul class="breadcrumb"><li><a href="/">Laman Utama</a></li><li>Peta Laman</li></ul>
      <h1>Peta Laman</h1>
      <p>Cari maklumat dan perkhidmatan mengikut bahagian portal.</p>
    </div>
  </div>
  <section class="ds-section">
    <div class="container sitemap-grid">
      @foreach ($pages as $section => $items)
        <div class="ds-block sitemap-group">
          <h2>{{ $section }}</h2>
          <ul>
            @foreach ($items as $page)
              <li><a href="{{ $page->url() }}">{{ $page->title }}</a></li>
            @endforeach
          </ul>
        </div>
      @endforeach
      <div class="ds-block sitemap-group">
        <h2>Hebahan &amp; Berita</h2>
        <ul>
          <li><a href="{{ route('announcements.index') }}">Semua hebahan dan berita</a></li>
          @foreach ($announcements as $announcement)
            <li><a href="{{ route('announcements.show', $announcement->slug) }}">{{ $announcement->title }}</a></li>
          @endforeach
        </ul>
      </div>
    </div>
  </section>
@endsection
