@extends('layouts.site')

@section('title', 'Soalan Lazim')
@section('description', 'Jawapan kepada soalan lazim tentang maklumat, perkhidmatan dan cara menghubungi Pejabat Daerah Ranau.')
@section('body_class', '')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/dasar.css') }}">
@endpush

@section('content')
@verbatim
  <div class="page-header page-header--plain">
    <div class="container">
      <ul class="breadcrumb"><li><a href="/">Laman Utama</a></li><li>Soalan Lazim</li></ul>
      <h1>Soalan Lazim</h1>
      <p>Maklumat ringkas untuk membantu anda menggunakan portal Pejabat Daerah Ranau.</p>
    </div>
  </div>
  <section class="ds-section">
    <div class="container">
      <div class="ds-block">
        <h2>Bagaimanakah saya boleh menghubungi Pejabat Daerah Ranau?</h2>
        <p>Hubungi <a href="tel:088875500">088-875500</a> atau e-mel <a href="mailto:pd.ranau@sabah.gov.my">pd.ranau@sabah.gov.my</a>. Maklumat alamat dan lokasi pejabat tersedia di halaman <a href="/hubungi-kami/alamat">Hubungi Kami</a>.</p>
      </div>
      <div class="ds-block">
        <h2>Di manakah saya boleh melihat hebahan dan berita terkini?</h2>
        <p>Layari halaman <a href="/hebahan">Hebahan &amp; Berita</a> untuk melihat pengumuman, notis dan berita yang telah diterbitkan.</p>
      </div>
      <div class="ds-block">
        <h2>Bagaimanakah saya boleh mencari kemudahan awam di Ranau?</h2>
        <p>Buka <a href="/profil-daerah/kemudahan-awam">Senarai Kemudahan Awam</a>. Gunakan carian dan penapis untuk mencari kemudahan, kemudian pilih butang peta untuk melihat lokasinya.</p>
      </div>
      <div class="ds-block">
        <h2>Di manakah senarai kampung dan maklumat ketua kampung?</h2>
        <p>Maklumat yang tersedia boleh dirujuk di halaman <a href="/profil-daerah/senarai-kampung">Senarai Kampung</a>. Gunakan ruangan carian untuk mencari nama kampung.</p>
      </div>
      <div class="ds-block">
        <h2>Di manakah maklumat permohonan dan perkhidmatan dalam talian?</h2>
        <p>Rujuk <a href="/perkhidmatan-online/lesen-berniaga">Permohonan Lesen Berniaga</a> atau <a href="/perkhidmatan-online/latihan-industri">Permohonan Latihan Industri</a> untuk maklumat berkaitan. Untuk pertanyaan lanjut, sila <a href="/hubungi-kami/alamat">hubungi pejabat</a>.</p>
      </div>
      <div class="ds-block">
        <h2>Bagaimanakah saya boleh mencari halaman lain dalam portal ini?</h2>
        <p>Gunakan menu navigasi atau <a href="/peta-laman">Peta Laman</a> untuk melihat senarai halaman mengikut bahagian.</p>
      </div>
    </div>
  </section>
@endverbatim
@endsection
