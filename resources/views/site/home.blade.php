@extends('layouts.site')

@section('body_class', 'is-home')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/home.css') }}">
@endpush

@section('content')
    <main id="main">
        <div class="hero">
            <div class="hero-layers" aria-hidden="true">
                <div class="hero-layer hero-layer-back" data-speed="0.5">
                    <img src="assets/img/hero/gunung-belakang.webp" alt="" />
                </div>
                <div class="hero-layer hero-layer-mid" data-speed="0.36">
                    <img src="assets/img/hero/gunung-tengah.webp" alt="" />
                </div>
                <div class="hero-layer hero-layer-building" data-speed="0.22">
                    <img src="assets/img/hero/bangunan.webp" alt="" />
                </div>
                <div class="hero-layer hero-layer-front" data-speed="0.1">
                    <img src="assets/img/hero/gunung-depan.webp" alt="" />
                </div>
            </div>
            <div class="hero-scrim"></div>
            <div class="hero-content">
                <div class="hero-text">
                    <p class="hero-eyebrow">Selamat Datang ke</p>
                    <h1>Pejabat Daerah Ranau</h1>
                    <p>
                        Kami menyampaikan perkhidmatan pentadbiran daerah yang telus dan
                        mesra rakyat kepada penduduk Ranau, di kaki Gunung Kinabalu.
                    </p>
                </div>
            </div>
            <img class="hero-teh hero-teh--light" src="assets/img/hero/tehijo.webp" alt="" aria-hidden="true" />
            <img class="hero-teh hero-teh--dark" src="assets/img/hero/tehijo-gelap.webp" alt="" aria-hidden="true" />

            <a href="#kandungan-utama" class="hero-scroll-cue" aria-label="Tatal ke bawah">
                <span>Tatal untuk melihat</span>
                <i class="fa-solid fa-chevron-down"></i>
            </a>
        </div>

        <div id="kandungan-utama"></div>

        <section class="section-tint">
            <div class="container">
                <div class="section-head">
                    <h2>Profil Daerah</h2>
                    <p>Maklumat mengenai Daerah Ranau</p>
                </div>
                <div class="pd-carousel" id="pdCarousel" aria-roledescription="carousel" aria-label="Profil Daerah">
                    <div class="pd-stage">
                        <button class="pd-nav pd-prev" type="button" aria-label="Sebelumnya">
                            <i class="fa-solid fa-arrow-left"></i>
                        </button>
                        <div class="pd-cards">
                            <a class="pd-card" href="/profil-daerah/kemudahan-awam" data-title="Senarai Kemudahan Awam"
                                data-desc="Direktori sekolah, klinik, balai dan kemudahan awam lain di seluruh Daerah Ranau."
                                data-credit="Sumber imej: hospital.com.my" aria-label="Senarai Kemudahan Awam">
                                <img class="pd-media" src="assets/img/hospital.jpg" alt="Hospital Ranau" />
                            </a>
                            <a class="pd-card" href="/profil-daerah/senarai-kampung" data-title="Senarai Kampung"
                                data-desc="Senarai kampung dalam Daerah Ranau beserta maklumat ketua kampung."
                                data-credit="Sumber imej: www.clladventureborneo.com" aria-label="Senarai Kampung">
                                <img class="pd-media" src="/assets/img/ranau.jpg" alt="Senarai Kampung" />
                            </a>
                            <a class="pd-card" href="/profil-daerah/wakil-rakyat" data-title="Wakil Rakyat"
                                data-desc="Kenali wakil rakyat yang mewakili penduduk Daerah Ranau."
                                aria-label="Wakil Rakyat">
                                <img class="pd-media" src="/assets/img/menara_parlimen.JPG" alt="Wakil Rakyat" />
                            </a>
                            <a class="pd-card" href="/profil-daerah/ekasih" data-title="eKasih"
                                data-desc="Maklumat program dan data kemiskinan melalui sistem eKasih." aria-label="eKasih">
                                <img class="pd-media" src="/assets/img/ekasih.png" alt="eKasih" />
                            </a>
                            <a class="pd-card" href="/profil-daerah/pelancongan" data-title="Pelancongan"
                                data-desc="Terokai tarikan pelancong dan penginapan di sekitar Ranau dan Kundasang."
                                data-credit="Sumber imej: Mohd Nuruzzaman / Sarawak Nature Explorers (Facebook), 7 Jun 2025"
                                aria-label="Pelancongan">
                                <img class="pd-media" src="assets/img/airterjun.webp" alt="Air terjun" />
                            </a>
                            <a class="pd-card" href="/profil-daerah/data-penduduk"
                                data-title="Data Penduduk Daerah Ranau"
                                data-desc="Statistik dan taburan penduduk mengikut kaum dan kawasan."
                                aria-label="Data Penduduk Daerah Ranau">
                                <img class="pd-media" src="/assets/img/drone_shot.jpg" alt="Data Penduduk Daerah Ranau" />
                            </a>
                        </div>
                    </div>
                    <div class="pd-info">
                        <span class="pd-tag">Profil Daerah</span>
                        <h3 class="pd-title" aria-live="polite"></h3>
                        <p class="pd-desc"></p>
                        <small class="pd-credit"></small>
                        <a class="btn pd-link" href="#">Lihat Butiran <span aria-hidden="true">→</span></a>
                        <span class="pd-count" aria-hidden="true"></span>
                    </div>
                    <button class="pd-nav pd-next" type="button" aria-label="Seterusnya">
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </section>

        [[hebahan-berita]]

        <section class="section-tint">
            <div class="container">
                <div class="section-head">
                    <h2>Galeri &amp; Pautan</h2>
                    <p>Gambar, video dan pautan berguna</p>
                </div>
                <div class="home-gallery-layout" id="home-gallery">
                    <div class="quick-access-grid quick-access-grid--four">
                        <a class="quick-access-card" href="/galeri-pautan/gambar">
                            <div class="quick-access-media">
                                <div class="showcase-media ph-3" aria-hidden="true">
                                    <i class="fa-solid fa-images"></i>
                                </div>
                            </div>
                            <div class="quick-access-body">
                                <span class="quick-access-tag">Galeri &amp; Pautan</span>
                                <h3>Gambar</h3>
                                <span class="quick-access-link">Lihat Butiran<span class="arrow"
                                        aria-hidden="true">→</span></span>
                            </div>
                        </a>
                        <a class="quick-access-card" href="/galeri-pautan/video">
                            <div class="quick-access-media">
                                <div class="showcase-media ph-1" aria-hidden="true">
                                    <img src="/assets/img/banner" alt="doge">
                                </div>
                            </div>
                            <div class="quick-access-body">
                                <span class="quick-access-tag">Galeri &amp; Pautan</span>
                                <h3>Video</h3>
                                <span class="quick-access-link">Lihat Butiran<span class="arrow"
                                        aria-hidden="true">→</span></span>
                            </div>
                        </a>
                        <a class="quick-access-card" href="/galeri-pautan/intranet">
                            <div class="quick-access-media">
                                <div class="showcase-media ph-2" aria-hidden="true">
                                    <i class="fa-solid fa-network-wired"></i>
                                </div>
                            </div>
                            <div class="quick-access-body">
                                <span class="quick-access-tag">Galeri &amp; Pautan</span>
                                <h3>Intranet</h3>
                                <span class="quick-access-link">Lihat Butiran<span class="arrow"
                                        aria-hidden="true">→</span></span>
                            </div>
                        </a>
                        <a class="quick-access-card" href="/galeri-pautan/agensi">
                            <div class="quick-access-media">
                                <div class="showcase-media ph-3" aria-hidden="true">
                                    <i class="fa-solid fa-link"></i>
                                </div>
                            </div>
                            <div class="quick-access-body">
                                <span class="quick-access-tag">Galeri &amp; Pautan</span>
                                <h3>Pautan Agensi</h3>
                                <span class="quick-access-link">Lihat Butiran<span class="arrow"
                                        aria-hidden="true">→</span></span>
                            </div>
                        </a>
                    </div>
                    @include('partials.home-calendar')
                </div>
                <div class="affiliated-links" data-affiliated-carousel role="region" aria-roledescription="carousel"
                    aria-labelledby="affiliated-links-title">
                    <div class="affiliated-head">
                        <div>
                            <h3 id="affiliated-links-title">Pautan Berkaitan</h3>
                            <p id="affiliated-links-note">Pautan luar dibuka dalam tab baharu.</p>
                        </div>
                    </div>
                    <ul class="affiliated-grid" id="affiliated-links-track" tabindex="0" aria-label="Pautan agensi berkaitan">
                        <li>
                            <a class="affiliated-card" href="https://www.malaysia.gov.my/my" target="_blank"
                                rel="noopener noreferrer" aria-describedby="affiliated-links-note">
                                <span class="affiliated-icon"><img src="/assets/img/affiliates/mygovernment.png" alt="" width="48" height="48" loading="lazy" decoding="async"></span>
                                <div class="affiliated-body">
                                    <h4>MyGovernment</h4>
                                    <p>Portal Rasmi Kerajaan Malaysia</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="affiliated-card" href="https://www.sabah-net.com/" target="_blank"
                                rel="noopener noreferrer" aria-describedby="affiliated-links-note">
                                <span class="affiliated-icon"><img src="/assets/img/affiliates/sabah-net.png" alt="" width="48" height="48" loading="lazy" decoding="async"></span>
                                <div class="affiliated-body">
                                    <h4>Sabah Net</h4>
                                    <p>Sabah Net Sdn. Bhd.</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="affiliated-card" href="https://jtdi.sabah.gov.my/" target="_blank"
                                rel="noopener noreferrer" aria-describedby="affiliated-links-note">
                                <span class="affiliated-icon"><img src="/assets/img/affiliates/jtdi.webp" alt="" width="48" height="48" loading="lazy" decoding="async"></span>
                                <div class="affiliated-body">
                                    <h4>JTDI Sabah</h4>
                                    <p>Jabatan Teknologi Digital dan Inovasi Negeri Sabah</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="affiliated-card" href="https://jpan.sabah.gov.my/" target="_blank"
                                rel="noopener noreferrer" aria-describedby="affiliated-links-note">
                                <span class="affiliated-icon"><img src="/assets/img/affiliates/jpan.png" alt="" width="48" height="48" loading="lazy" decoding="async"></span>
                                <div class="affiliated-body">
                                    <h4>JPAN Sabah</h4>
                                    <p>Jabatan Perkhidmatan Awam Negeri Sabah</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="affiliated-card" href="https://www.jpa.gov.my/" target="_blank"
                                rel="noopener noreferrer" aria-describedby="affiliated-links-note">
                                <span class="affiliated-icon"><img src="/assets/img/affiliates/jpa.png" alt="" width="48" height="48" loading="lazy" decoding="async"></span>
                                <div class="affiliated-body">
                                    <h4>JPA Malaysia</h4>
                                    <p>Jabatan Perkhidmatan Awam Malaysia</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="affiliated-card" href="https://www.sprm.gov.my/" target="_blank"
                                rel="noopener noreferrer" aria-describedby="affiliated-links-note">
                                <span class="affiliated-icon"><img src="/assets/img/affiliates/sprm.png" alt="" width="48" height="48" loading="lazy" decoding="async"></span>
                                <div class="affiliated-body">
                                    <h4>SPRM</h4>
                                    <p>Suruhanjaya Pencegahan Rasuah Malaysia</p>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
    </main>
@endsection