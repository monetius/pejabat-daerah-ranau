@extends('layouts.site')

@section('title', 'Carta Organisasi - Pejabat Daerah Ranau')
@section('description', 'Laman web rasmi Pejabat Daerah Ranau, Sabah.')
@section('body_class', 'has-banner')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/chart.css') }}">
@endpush

@section('content')
@verbatim

      <div class="page-header">
        <div class="page-header-media">
          <img src="/assets/img/banner.jpg" alt="" />
        </div>
        <div class="page-header-scrim"></div>
        <div class="container">
          <ul class="breadcrumb">
            <li><a href="/">Laman Utama</a></li>
            <li>Info Korporat</li>
            <li>Carta Organisasi</li>
          </ul>
          <h1>Carta Organisasi</h1>
        </div>
      </div>
      <section class="carta-section">
        <div class="container">
          <figure class="carta-figure">
            <!-- ganti gambar kalau ada yg baru -->
            <img
              src="/assets/img/carta-organisasi.png"
              alt="gambar organization chart"
            />
          </figure>
        </div>
      </section>
    
@endverbatim
@endsection
