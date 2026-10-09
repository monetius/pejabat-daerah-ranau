@extends('layouts.site')

@section('title', 'Hebahan - Pejabat Daerah Ranau')
@section('description', 'Hebahan dan pengumuman terkini Pejabat Daerah Ranau.')
@section('body_class', 'has-banner')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/dasar.css') }}">
@endpush

@section('content')
  <div class="page-header">
    <div class="page-header-media"><img src="/assets/img/banner.jpg" alt="" /></div>
    <div class="page-header-scrim"></div>
    <div class="container">
      <ul class="breadcrumb">
        <li><a href="/">Laman Utama</a></li>
        <li>Hebahan</li>
      </ul>
      <h1>Hebahan</h1>
    </div>
  </div>
  <section class="ds-section">
    <div class="container">
      @forelse ($items as $item)
        <div class="ds-block">
          <h2><a href="{{ route('announcements.show', $item->slug) }}">{{ $item->title }}</a></h2>
          <p><small>{{ $item->published_at?->format('d/m/Y') }}</small></p>
          @if ($item->summary)
          <p>{{ $item->summary }}</p>@endif
        </div>
      @empty
        <div class="ds-block">
          <p>Tiada hebahan buat masa ini.</p>
        </div>
      @endforelse
      {{ $items->links() }}
    </div>
  </section>
@endsection