@extends('layouts.site')

@section('title', $item->title . ' - Pejabat Daerah Ranau')
@section('description', $item->summary ?: 'Hebahan Pejabat Daerah Ranau.')
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
        <li><a href="{{ route('announcements.index') }}">Hebahan &amp; Berita</a></li>
        <li>{{ $item->title }}</li>
      </ul>
      <h1>{{ $item->title }}</h1>
    </div>
  </div>

  <section class="ds-section hb-page">
    <div class="container">
      <article class="hb-article">
        <div class="hb-meta">
          <span class="hb-tag hb-tag-{{ $item->category }}">{{ $item->categoryLabel() }}</span>
          <time>{{ $item->published_at?->format('d/m/Y') }}</time>
        </div>
        @if ($item->image)
          <div class="hb-article-media"><img src="{{ $item->image }}" alt=""></div>
        @endif
        <div class="hb-article-body">{!! $item->body !!}</div>
        <a class="hb-back" href="{{ route('announcements.index') }}">← Semua hebahan &amp; berita</a>
      </article>

      @if ($more->isNotEmpty())
        <div class="hb-more">
          <h2>Hebahan &amp; berita lain</h2>
          <div class="hb-list">
            @foreach ($more as $m)
              <a class="hb-item" href="{{ route('announcements.show', $m->slug) }}">
                <div class="hb-item-media">@include('partials.news-media', ['item' => $m])</div>
                <div class="hb-item-body">
                  <div class="hb-meta">
                    <span class="hb-tag hb-tag-{{ $m->category }}">{{ $m->categoryLabel() }}</span>
                    <time>{{ $m->published_at?->format('d/m/Y') }}</time>
                  </div>
                  <h3>{{ $m->title }}</h3>
                </div>
              </a>
            @endforeach
          </div>
        </div>
      @endif
    </div>
  </section>
@endsection
