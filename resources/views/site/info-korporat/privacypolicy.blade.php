@extends('layouts.site')

@section('title')Dasar Privasi - Pejabat Daerah Ranau@endsection
@section('description')Dasar privasi Pejabat Daerah Ranau - penggunaan dan perlindungan maklumat yang dikemukakan oleh pengunjung laman web.@endsection
@section('body_class', 'has-banner')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/dasar.css') }}">
@endpush

@section('content')
      <div class="page-header">
        <div class="page-header-media">
          <img src="/assets/img/banner.jpg" alt="" />
        </div>
        <div class="page-header-scrim"></div>
        <div class="container">
          <ul class="breadcrumb">
            <li><a href="/">Laman Utama</a></li>
            <li>Info Korporat</li>
            <li>Dasar Privasi</li>
          </ul>
          <h1>Dasar Privasi</h1>
        </div>
      </div>
      <section class="ds-section">
        <div class="container">
          <div class="ds-block">
            <h2>Dasar Privasi Privasi Anda</h2>
            <p>
              Halaman ini menerangkan dasar privasi yang merangkumi penggunaan
              dan perlindungan maklumat yang dikemukakan oleh pengunjung.
              Sekiranya anda membuat transaksi atau menghantar e-mel yang
              mengandungi maklumat peribadi, maklumat ini mungkin akan dikongsi
              bersama dengan agensi awam lain untuk membantu penyediaan
              perkhidmatan yang lebih berkesan dan efektif. Contohnya seperti di
              dalam menyelesaikan aduan yang memerlukan maklum balas daripada
              agensi-agensi lain.
            </p>
          </div>
          <div class="ds-block">
            <h2>Maklumat Yang Dikumpul</h2>
            <p>
              Tiada maklumat peribadi akan dikumpul semasa anda melayari laman
              web ini kecuali maklumat yang dikemukakan oleh anda melalui e-mel.
            </p>
          </div>
          <div class="ds-block">
            <h2>
              Apa yang akan Berlaku jika Saya Membuat Pautan kepada Laman Web
              yang Lain?
            </h2>
            <p>
              Laman web ini mempunyai pautan ke laman web lain. Dasar privasi
              ini hanya terpakai untuk laman web ini sahaja. Perlu diingatkan
              bahawa laman web yang terdapat dalam pautan mungkin mempunyai
              dasar privasi yang berbeza dan pengunjung dinasihatkan supaya
              meneliti dan memahami dasar privasi bagi setiap laman web yang
              dilayari.
            </p>
          </div>
          <div class="ds-block">
            <h2>Pindaan Dasar</h2>
            <p>
              Sekiranya dasar privasi ini dipinda, pindaan akan dikemas kini di
              halaman ini. Dengan sering melayari halaman ini, anda akan dikemas
              kini dengan maklumat yang dikumpul, cara ia digunakan dan dalam
              keadaan tertentu, bagaimana maklumat dikongsi bersama pihak yang
              lain.
            </p>
          </div>
        </div>
      </section>
    @endsection
