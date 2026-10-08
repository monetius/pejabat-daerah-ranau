@extends('layouts.site')

@section('title', 'Pautan Agensi - Pejabat Daerah Ranau')
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
            <li>Pautan Agensi</li>
          </ul>
          <h1>Pautan Agensi</h1>
        </div>
      </div>
      <section>
        <div class="container">
          <p class="pl-intro">
            Pautan rasmi ke sistem dan portal agensi kerajaan yang berkaitan
            dengan Pejabat Daerah Ranau.
          </p>
          <div class="pl-grid">
            <a
              class="pl-link"
              href="https://iportfolio.sabah.gov.my/"
              target="_blank"
              rel="noopener noreferrer"
            >
              <i class="fa-solid fa-id-badge"></i>
              <div>
                <h3>iPortfolio</h3>
                <p>Sistem rekod portfolio jawatan perkhidmatan negeri Sabah.</p>
              </div>
              <i class="fa-solid fa-arrow-up-right-from-square pl-ext"></i>
            </a>
            <a
              class="pl-link"
              href="https://s2a.sabah.gov.my/"
              target="_blank"
              rel="noopener noreferrer"
            >
              <i class="fa-solid fa-boxes-stacked"></i>
              <div>
                <h3>Sistem Aset (S2A)</h3>
                <p>Sistem pengurusan aset kerajaan negeri Sabah.</p>
              </div>
              <i class="fa-solid fa-arrow-up-right-from-square pl-ext"></i>
            </a>
            <a
              class="pl-link"
              href="https://emove.sabah.gov.my/login"
              target="_blank"
              rel="noopener noreferrer"
            >
              <i class="fa-solid fa-truck-fast"></i>
              <div>
                <h3>E-Move</h3>
                <p>Sistem pergerakan aset dan inventori.</p>
              </div>
              <i class="fa-solid fa-arrow-up-right-from-square pl-ext"></i>
            </a>
            <a
              class="pl-link"
              href="https://spans.sabah.gov.my/syor/"
              target="_blank"
              rel="noopener noreferrer"
            >
              <i class="fa-solid fa-file-signature"></i>
              <div>
                <h3>E-Spans Syor</h3>
                <p>Sistem cadangan dan syor perkhidmatan awam negeri.</p>
              </div>
              <i class="fa-solid fa-arrow-up-right-from-square pl-ext"></i>
            </a>
            <a
              class="pl-link"
              href="https://announcement.sabah.gov.my/"
              target="_blank"
              rel="noopener noreferrer"
            >
              <i class="fa-solid fa-bullhorn"></i>
              <div>
                <h3>Sabah Gov Announcement</h3>
                <p>Portal pengumuman rasmi Kerajaan Negeri Sabah.</p>
              </div>
              <i class="fa-solid fa-arrow-up-right-from-square pl-ext"></i>
            </a>
            <a
              class="pl-link"
              href="https://webmail.sabah.gov.my/"
              target="_blank"
              rel="noopener noreferrer"
            >
              <i class="fa-solid fa-envelope-open-text"></i>
              <div>
                <h3>Webmail</h3>
                <p>E-mel rasmi kakitangan Kerajaan Negeri Sabah.</p>
              </div>
              <i class="fa-solid fa-arrow-up-right-from-square pl-ext"></i>
            </a>
            <a
              class="pl-link"
              href="https://appjpan.sabah.gov.my/INSAN-ITS/login.asp"
              target="_blank"
              rel="noopener noreferrer"
            >
              <i class="fa-solid fa-user-graduate"></i>
              <div>
                <h3>INSAN-ITS</h3>
                <p>Sistem latihan dan pembangunan insan JPAN.</p>
              </div>
              <i class="fa-solid fa-arrow-up-right-from-square pl-ext"></i>
            </a>
            <a
              class="pl-link"
              href="https://kampung.sabah.gov.my/"
              target="_blank"
              rel="noopener noreferrer"
            >
              <i class="fa-solid fa-house-chimney"></i>
              <div>
                <h3>Sistem Aplikasi Profil Kampung</h3>
                <p>Sistem profil dan data kampung-kampung di Sabah.</p>
              </div>
              <i class="fa-solid fa-arrow-up-right-from-square pl-ext"></i>
            </a>
            <a
              class="pl-link"
              href="https://www.malaysia.gov.my/portal/index"
              target="_blank"
              rel="noopener noreferrer"
            >
              <i class="fa-solid fa-landmark"></i>
              <div>
                <h3>MyGovernment</h3>
                <p>Portal rasmi Kerajaan Malaysia.</p>
              </div>
              <i class="fa-solid fa-arrow-up-right-from-square pl-ext"></i>
            </a>
            <a
              class="pl-link"
              href="https://jpan.sabah.gov.my/"
              target="_blank"
              rel="noopener noreferrer"
            >
              <i class="fa-solid fa-building-columns"></i>
              <div>
                <h3>Jabatan Perkhidmatan Awam Negeri Sabah</h3>
                <p>Portal rasmi JPAN Sabah.</p>
              </div>
              <i class="fa-solid fa-arrow-up-right-from-square pl-ext"></i>
            </a>
            <a
              class="pl-link"
              href="https://www.jpa.gov.my/"
              target="_blank"
              rel="noopener noreferrer"
            >
              <i class="fa-solid fa-building-flag"></i>
              <div>
                <h3>Jabatan Perkhidmatan Awam Malaysia</h3>
                <p>Portal rasmi Jabatan Perkhidmatan Awam Malaysia.</p>
              </div>
              <i class="fa-solid fa-arrow-up-right-from-square pl-ext"></i>
            </a>
            <a
              class="pl-link"
              href="https://www.sprm.gov.my"
              target="_blank"
              rel="noopener noreferrer"
            >
              <i class="fa-solid fa-scale-balanced"></i>
              <div>
                <h3>Suruhanjaya Pencegahan Rasuah Malaysia</h3>
                <p>Portal rasmi SPRM.</p>
              </div>
              <i class="fa-solid fa-arrow-up-right-from-square pl-ext"></i>
            </a>
            <a
              class="pl-link"
              href="https://kplb.sabah.gov.my/"
              target="_blank"
              rel="noopener noreferrer"
            >
              <i class="fa-solid fa-tree-city"></i>
              <div>
                <h3>KPLB</h3>
                <p>Kementerian Pembangunan Luar Bandar Sabah.</p>
              </div>
              <i class="fa-solid fa-arrow-up-right-from-square pl-ext"></i>
            </a>
          </div>
        </div>
      </section>
    
@endverbatim
@endsection
