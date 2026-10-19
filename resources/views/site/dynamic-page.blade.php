@extends('layouts.site')

@section('title', $page->title . ' - Pejabat Daerah Ranau')
@section('description', $page->meta_description ?: 'Laman web rasmi Pejabat Daerah Ranau, Sabah.')
@section('body_class', '')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/dasar.css') }}">
@endpush

@section('content')
  <div class="page-header page-header--plain">
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