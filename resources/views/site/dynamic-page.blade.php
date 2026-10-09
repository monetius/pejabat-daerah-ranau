@extends('layouts.site')

@section('title', $page->title . ' - Pejabat Daerah Ranau')
@section('description', $page->meta_description ?: 'Laman web rasmi Pejabat Daerah Ranau, Sabah.')
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
        <li>{{ $page->title }}</li>
      </ul>
      <h1>{{ $page->title }}</h1>
    </div>
  </div>
  <section class="ds-section">
    <div class="container">
      <div class="ds-block">
        {!! $page->body !!}
      </div>
    </div>
  </section>
@endsection