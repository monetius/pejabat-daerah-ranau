@extends('layouts.site')

@section('title', 'Pelancongan - Pejabat Daerah Ranau')
@section('description', 'Peta pelancongan dan senarai tarikan, aktiviti serta penginapan di Daerah Ranau, Sabah.')
@section('body_class', 'has-banner')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/pelancongan.css') }}">
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
            <li>Pelancongan</li>
          </ul>
          <h1>Pelancongan</h1>
        </div>
      </div>
      <section>
        <div class="container">
          <div class="tr-intro">
            <h2>Terokai Ranau, di kaki Gunung Kinabalu</h2>
            <p>
              Daerah Ranau terkenal dengan Gunung Kinabalu (4,095m), ladang
              tenusu, teh Sabah, sungai untuk rafting, air panas serta homestay
              yang mesra. Gunakan peta di bawah untuk merancang perjalanan anda
              dari Bundu Tuhan hingga ke Luanti Baru.
            </p>
          </div>
          <div class="tr-stats">
            <div class="tr-stat"><b>4,095m</b><span>Gunung Kinabalu</span></div>
            <div class="tr-stat">
              <b>6</b><span>Kawasan tarikan utama</span>
            </div>
            <div class="tr-stat">
              <b>40+</b><span>Tarikan &amp; aktiviti</span>
            </div>
            <div class="tr-stat"><b>80+</b><span>Pilihan penginapan</span></div>
          </div>

          <div class="tr-map" id="peta">
            <figure>
              <img
                src="/assets/img/pelancongan/peta-tarikan.webp"
                alt="Peta Pelancongan Ranau (Ranau Tourist Fun Map) menunjukkan tarikan di Bundu Tuhan, Kundasang, Kibbas, Pekan Ranau, Lohan dan Luanti Baru"
                loading="lazy"
              />
              <figcaption>
                <span
                  >Ranau Tourist Fun Map: jarak antara kawasan adalah
                  anggaran.</span
                ><a
                  href="/assets/img/pelancongan/peta-tarikan.webp"
                  target="_blank"
                  rel="noopener"
                  >Buka saiz penuh</a
                >
              </figcaption>
            </figure>
          </div>

          <h2 class="tr-h">Tarikan mengikut kawasan</h2>
          <p class="tr-sub">
            Disusun mengikut laluan dari Kinabalu Park menghala ke Telupid.
          </p>
          <div class="tr-grid">
            <article class="tr-card">
              <h3>Bundu Tuhan</h3>
              <small>Kawasan kaki Gunung Kinabalu</small>
              <ul>
                <li>Wasai Togindang Trail</li>
                <li>Aura Binambayangan Cave's</li>
                <li>Tukad Gonipis</li>
                <li>Tudan Camping &amp; Recreation</li>
              </ul>
            </article>
            <article class="tr-card">
              <h3>Kundasang</h3>
              <small>Ladang tenusu &amp; bukit pemandangan</small>
              <ul>
                <li>Maragang Hill</li>
                <li>Desa Dairy Farm</li>
                <li>Mesilou Atamis Homestay</li>
                <li>Sosodikon Hill</li>
                <li>Tinorindak Hill</li>
                <li>Selfie Corner</li>
                <li>Aki-Aki Trail</li>
                <li>Walai Tokou Homestay</li>
                <li>B-Inspired Abode</li>
              </ul>
            </article>
            <article class="tr-card">
              <h3>Kibbas</h3>
              <small>Desa kampung &amp; taman</small>
              <ul>
                <li>Arnab Village</li>
                <li>Magical Garden</li>
                <li>Tambiau Forest House</li>
                <li>Kopi Apai</li>
              </ul>
            </article>
            <article class="tr-card">
              <h3>Pekan Ranau</h3>
              <small>Produk tempatan &amp; kraf</small>
              <ul>
                <li>Bombon Marakau</li>
                <li>Bukit Kimolohing</li>
                <li>Serunding Tuhau</li>
                <li>Persatuan Bunga-Kraf</li>
                <li>Inap Desa Kilimu</li>
                <li>RH Serunding Kubis / Lada</li>
                <li>Daralamas River Canyon</li>
              </ul>
            </article>
            <article class="tr-card">
              <h3>Lohan</h3>
              <small>Pengembaraan &amp; air panas</small>
              <ul>
                <li>Turuntungon Riverside Cabin</li>
                <li>Timbua Water Rafting</li>
                <li>Nalumad Eco Tourism</li>
                <li>Perancangan River Rafting</li>
                <li>Poring Hot Spring</li>
                <li>Taman Mini Rekreasi Kembara</li>
                <li>Sably Goat Haven</li>
                <li>Ranau Paragliding &amp; ATV</li>
              </ul>
            </article>
            <article class="tr-card">
              <h3>Luanti Baru</h3>
              <small>Arah Telupid</small>
              <ul>
                <li>Luanti Fish Village</li>
                <li>Bunnies Secret Garden</li>
                <li>Sabah Tea</li>
                <li>Tagal Kg. Nalapak</li>
                <li>Malambun Campsite</li>
                <li>Kraftangan Kg. Bitoon</li>
              </ul>
            </article>
          </div>

          <div class="tr-more">
            <h3>Tarikan dan lokasi lain</h3>
            <ul class="tr-chips">
              <li>Kinabalu Park HQ</li>
              <li>Kundasang War Memorial</li>
              <li>Gua Kimondou</li>
              <li>Nuluh Giring-Giring</li>
              <li>Dompurungon Hill</li>
              <li>Nunuk Ragang</li>
              <li>Bukit Kapur, Kg. Tobok Lama</li>
              <li>Bukit Lugas, Kg. Waang</li>
              <li>Komplek Sukan Ranau</li>
              <li>Tamu Ranau</li>
            </ul>
          </div>

          <h2 class="tr-h" id="penginapan">Tempat penginapan</h2>
          <p class="tr-sub">
            Senarai hotel, chalet, homestay dan tapak perkhemahan beserta nombor
            telefon untuk tempahan.
          </p>
          <div class="tr-stay">
            <div class="tr-map">
              <figure>
                <img
                  src="/assets/img/pelancongan/peta-penginapan.webp"
                  alt="Peta pelancongan Ranau dengan senarai tempat penginapan dan nombor telefon"
                  loading="lazy"
                />
                <figcaption>
                  <span>Lihat saiz penuh untuk membaca nombor telefon.</span
                  ><a
                    href="/assets/img/pelancongan/peta-penginapan.webp"
                    target="_blank"
                    rel="noopener"
                    >Buka saiz penuh</a
                  >
                </figcaption>
              </figure>
            </div>
            <p class="tr-note">
              Sila sahkan harga, ketersediaan dan nombor telefon terus dengan
              pengendali penginapan sebelum membuat tempahan.
            </p>
          </div>
          <p class="tr-credit">
            Peta dengan kerjasama Ranau Tourism Association (RaTa), Tourism
            Malaysia dan Best of Borneo Sabah. Untuk pertanyaan, sila
            <a href="/hubungi-kami/alamat"
              >hubungi Pejabat Daerah Ranau</a
            >.
          </p>
        </div>
      </section>
    
@endverbatim
@endsection
