@extends('layouts.site')

@section('title', $item->title . ' - Pejabat Daerah Ranau')
@section('description', $item->summary ?: 'Hebahan Pejabat Daerah Ranau.')
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
        <li><a href="{{ route('announcements.index') }}">Hebahan</a></li>
        <li>{{ $item->title }}</li>
      </ul>
      <h1>{{ $item->title }}</h1>
    </div>
  </div>
  <section class="ds-section">
    <div class="container">
      <div class="ds-block">
        <p><small>{{ $item->published_at?->format('d/m/Y') }}</small></p>
        {!! $item->body !!}
      </div>
    </div>
  </section>
@endsection