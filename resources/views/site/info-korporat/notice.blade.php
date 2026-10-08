@extends('layouts.site')

@section('title', 'Notis Penafian - Pejabat Daerah Ranau')
@section('description', 'Notis penafian Pejabat Daerah Ranau berhubung penggunaan maklumat yang terdapat di dalam laman web ini.')
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
            <li>Notis Penafian</li>
          </ul>
          <h1>Notis Penafian</h1>
        </div>
      </div>
      <section class="ds-section">
        <div class="container">
          <div class="ds-block">
            <p>
              Pejabat Daerah Ranau tidak akan bertanggungjawab terhadap sebarang
              kerugian atau kerosakan yang dialami disebabkan penggunaan
              sebarang maklumat yang terdapat di dalam laman web ini.
            </p>
          </div>
        </div>
      </section>
    
@endverbatim
@endsection
