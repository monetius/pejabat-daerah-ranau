@extends('layouts.site')

@section('title', 'Data Penduduk Daerah Ranau - Pejabat Daerah Ranau')
@section('description', 'Laman web rasmi Pejabat Daerah Ranau, Sabah.')
@section('body_class', 'has-banner')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/kampungsiurang.css') }}">
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
            <li>Profil Daerah</li>
            <li>Data Penduduk Daerah Ranau</li>
          </ul>
          <h1>Data Penduduk Daerah Ranau</h1>
        </div>
      </div>
      <section>
        <div class="container">
          <p class="dp-intro">
            Rujukan data dan statistik rasmi penduduk Daerah Ranau, disediakan
            oleh Pejabat Daerah Ranau dan Jabatan Perangkaan Malaysia.
          </p>
          <div class="dp-links">
            <a
              class="dp-link"
              href="https://docs.google.com/spreadsheets/d/1oedU74vESM9kpjvrGLwVMM3J1ciyc0I33k6zDL5RF6g/edit?usp=sharing"
              target="_blank"
              rel="noopener noreferrer"
            >
              <i class="fa-solid fa-table"></i>
              <div>
                <h3>Data Penduduk Mengikut Kampung (2019)</h3>
                <p>
                  Hamparan data taburan penduduk mengikut kampung di Daerah
                  Ranau.
                </p>
              </div>
              <i class="fa-solid fa-arrow-up-right-from-square dp-ext"></i>
            </a>
            <a
              class="dp-link"
              href="https://drive.google.com/file/d/1rw5ZGE-ymZFc9NIIh0Ae29zJW_6_4vRo/view?usp=sharing"
              target="_blank"
              rel="noopener noreferrer"
            >
              <i class="fa-solid fa-file-lines"></i>
              <div>
                <h3>
                  Banci Penduduk dan Perumahan Malaysia 2020 – Daerah Ranau
                </h3>
                <p>
                  Laporan rasmi Banci Penduduk dan Perumahan Malaysia 2020 bagi
                  Daerah Ranau.
                </p>
              </div>
              <i class="fa-solid fa-arrow-up-right-from-square dp-ext"></i>
            </a>
          </div>
        </div>
      </section>
    
@endverbatim
@endsection
