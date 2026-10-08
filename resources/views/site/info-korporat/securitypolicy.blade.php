@extends('layouts.site')

@section('title', 'Dasar Keselamatan - Pejabat Daerah Ranau')
@section('description', 'Dasar keselamatan Pejabat Daerah Ranau - perlindungan data dan keselamatan storan maklumat peribadi.')
@section('body_class', 'has-banner')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/dasar.css') }}">
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
            <li>Dasar Keselamatan</li>
          </ul>
          <h1>Dasar Keselamatan</h1>
        </div>
      </div>
      <section class="ds-section">
        <div class="container">
          <div class="ds-block">
            <h2>Perlindungan Data</h2>
            <p>
              Teknologi terkini termasuk penyulitan data adalah digunakan untuk
              melindungi data yang dikemukakan dan pematuhan kepada standard
              keselamatan yang ketat adalah terpakai untuk menghalang capaian
              yang tidak dibenarkan.
            </p>
          </div>
          <div class="ds-block">
            <h2>Keselamatan Storan</h2>
            <p>
              Semua storan elektronik dan penghantaran data peribadi akan
              dilindungi dan disimpan dengan menggunakan teknologi keselamatan
              yang sesuai.
            </p>
          </div>
        </div>
      </section>
    
@endverbatim
@endsection
