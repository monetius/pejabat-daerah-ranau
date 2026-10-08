@extends('layouts.site')

@section('title', 'Intranet - Pejabat Daerah Ranau')
@section('description', 'Laman web rasmi Pejabat Daerah Ranau, Sabah.')
@section('body_class', 'has-banner')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/linkviral.css') }}">
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
            <li>Pautan</li>
            <li>Intranet</li>
          </ul>
          <h1>Intranet</h1>
        </div>
      </div>
      <section>
        <div class="container">
          <p class="pl-intro">
            Pautan capaian dalam talian untuk sistem-sistem kakitangan Kerajaan
            Negeri Sabah.
          </p>
          <div class="pl-grid">
            <a
              class="pl-link"
              href="https://eprestasi.sabah.gov.my/"
              target="_blank"
              rel="noopener noreferrer"
            >
              <i class="fa-solid fa-chart-line"></i>
              <div>
                <h3>E-Prestasi</h3>
                <p>Sistem penilaian prestasi kakitangan kerajaan negeri.</p>
              </div>
              <i class="fa-solid fa-arrow-up-right-from-square pl-ext"></i>
            </a>
            <a
              class="pl-link"
              href="https://spde.sabah.gov.my/"
              target="_blank"
              rel="noopener noreferrer"
            >
              <i class="fa-solid fa-diagram-project"></i>
              <div>
                <h3>SPDE</h3>
                <p>Sistem pengurusan dokumen elektronik kerajaan negeri.</p>
              </div>
              <i class="fa-solid fa-arrow-up-right-from-square pl-ext"></i>
            </a>
            <a
              class="pl-link"
              href="https://apps.sabah.gov.my/epergerakan/"
              target="_blank"
              rel="noopener noreferrer"
            >
              <i class="fa-solid fa-right-left"></i>
              <div>
                <h3>E-Pergerakan Pegawai</h3>
                <p>Sistem permohonan pergerakan pegawai kerajaan negeri.</p>
              </div>
              <i class="fa-solid fa-arrow-up-right-from-square pl-ext"></i>
            </a>
            <a
              class="pl-link"
              href="https://apps.sabah.gov.my/izinKeluarNegeri/default.asp"
              target="_blank"
              rel="noopener noreferrer"
            >
              <i class="fa-solid fa-passport"></i>
              <div>
                <h3>Izin Keluar Negeri</h3>
                <p>
                  Sistem permohonan kebenaran keluar negeri untuk kakitangan.
                </p>
              </div>
              <i class="fa-solid fa-arrow-up-right-from-square pl-ext"></i>
            </a>
          </div>
          <div class="notice-block">
            <p>
              Sistem intranet lain (cth. E-Cuti, E-Pergerakan Gaji, SM2) hanya
              boleh dicapai melalui rangkaian dalaman Kerajaan Negeri Sabah dan
              tidak disenaraikan di sini kerana tidak boleh diakses dari luar
              rangkaian tersebut.
            </p>
          </div>
        </div>
      </section>
    
@endverbatim
@endsection
