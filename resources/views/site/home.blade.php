@extends('layouts.site')

@section('title', 'Pejabat Daerah Ranau')
@section('body_class', 'is-home')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/home.css') }}">
@endpush

@section('content')
@verbatim
<section class="hm-hero" data-home-version="2" aria-labelledby="hm-hero-title">
    <img class="hm-hero-photo" src="/assets/img/home/hero-ranau.webp" alt="" width="1920" height="1200" fetchpriority="high">
    <div class="hm-hero-scrim" aria-hidden="true"></div>
    <div class="hm-wrap hm-hero-body">
        <p class="hm-eyebrow hm-eyebrow--light">Selamat datang ke</p>
        <h1 id="hm-hero-title">Pejabat Daerah Ranau</h1>
        <p class="hm-hero-lead">Kami menyampaikan perkhidmatan pentadbiran daerah yang telus dan mesra rakyat kepada penduduk Ranau, di kaki Gunung Kinabalu.</p>
        <div class="hm-hero-actions">
            <a class="hm-btn hm-btn--gold" href="#perkhidmatan">Perkhidmatan Online
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
            <a class="hm-btn hm-btn--ghost" href="/hubungi-kami/alamat">Hubungi Kami</a>
        </div>
    </div>
    <div class="hm-wrap hm-live-wrap">
        <div class="hm-live" aria-label="Maklumat semasa">
            <div class="hm-live-item hm-status" id="status">
                <span class="hm-status-dot" aria-hidden="true"></span>
                <span class="hm-live-text"><strong id="status-text">Waktu pejabat</strong><span class="hm-live-sub">Isnin–Jumaat · 8:00 pagi – 5:00 petang</span></span>
            </div>
            <a class="hm-live-item" href="#kalendar" data-next-holiday>
                <svg class="hm-live-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                <span class="hm-live-text"><strong data-next-holiday-title>Kalendar cuti umum</strong><span class="hm-live-sub" data-next-holiday-date>Program, acara &amp; cuti umum Sabah</span></span>
            </a>
            [[hebahan-terkini]]
        </div>
    </div>
    <small class="hm-photo-credit">Foto: www.clladventureborneo.com</small>
</section>

<section class="hm-section hm-services" id="perkhidmatan" aria-labelledby="hm-services-title">
    <div class="hm-wrap">
        <h2 id="hm-services-title" class="hm-visually-hidden">Perkhidmatan pantas</h2>
        <div class="hm-services-grid">
            <a class="hm-service" href="https://i-adu.sabah.gov.my/" data-reveal>
                <span class="hm-service-icon hm-tone-gold"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11v2a1 1 0 0 0 1 1h2l5 4V6L6 10H4a1 1 0 0 0-1 1z"/><path d="M15 9a4 4 0 0 1 0 6M18 6a8 8 0 0 1 0 12"/></svg></span>
                <span><strong>Sistem Aduan Awam</strong><span>Hantar aduan melalui i-Adu</span></span>
            </a>
            <a class="hm-service" href="/perkhidmatan-online/lesen-berniaga" data-reveal>
                <span class="hm-service-icon hm-tone-blue"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H6a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V8z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg></span>
                <span><strong>Lesen Berniaga</strong><span>Mohon lesen melalui eLESEN</span></span>
            </a>
            <a class="hm-service" href="/perkhidmatan-online/latihan-industri" data-reveal>
                <span class="hm-service-icon hm-tone-green"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2M3 13h18"/></svg></span>
                <span><strong>Latihan Industri</strong><span>Permohonan pelajar praktikal</span></span>
            </a>
            <a class="hm-service" href="/profil-daerah/kemudahan-awam" data-reveal>
                <span class="hm-service-icon hm-tone-purple"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 21V5a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v16M14 9h5a1 1 0 0 1 1 1v11M2 21h20M8 8h2M8 12h2M8 16h2"/></svg></span>
                <span><strong>Kemudahan Awam</strong><span>Sekolah, klinik &amp; balai</span></span>
            </a>
        </div>
    </div>
</section>

<section class="hm-stats" aria-labelledby="hm-stats-title">
    <div class="hm-wrap">
        <h2 id="hm-stats-title" class="hm-visually-hidden">Ranau dalam angka</h2>
        <div class="hm-stats-grid">
            <div class="hm-stat" data-reveal><span class="hm-stat-value">210</span><span class="hm-stat-label">kampung dalam Daerah Ranau</span></div>
            <div class="hm-stat" data-reveal><span class="hm-stat-value">15</span><span class="hm-stat-label">mukim di tiga kawasan</span></div>
            <div class="hm-stat" data-reveal><span class="hm-stat-value">4,750<small> km²</small></span><span class="hm-stat-label">Geopark Global UNESCO Kinabalu</span></div>
            <div class="hm-stat" data-reveal><span class="hm-stat-value">1964</span><span class="hm-stat-label">Taman Kinabalu diwartakan</span></div>
        </div>
    </div>
</section>

[[hebahan-berita]]

<section class="hm-section hm-profile" aria-labelledby="hm-profile-title">
    <div class="hm-wrap hm-profile-grid">
        <a class="hm-profile-feature" href="/profil-daerah/pelancongan" data-reveal>
            <img src="/assets/img/home/pelancongan.webp" alt="Air terjun di Daerah Ranau" width="820" height="1093" loading="lazy">
            <span class="hm-profile-feature-text">
                <span class="hm-eyebrow hm-eyebrow--light">Pelancongan</span>
                <strong>Terokai air terjun, ladang teh dan Kundasang di kaki Kinabalu</strong>
                <small>Foto: Mohd Nuruzzaman / Sarawak Nature Explorers</small>
            </span>
        </a>
        <div class="hm-profile-body">
            <p class="hm-eyebrow hm-eyebrow--green">Kenali Ranau</p>
            <h2 id="hm-profile-title" class="hm-title">Profil Daerah</h2>
            <p class="hm-lead">Dari senarai kampung hingga wakil rakyat: maklumat penting tentang Daerah Ranau di satu tempat.</p>
            <div class="hm-profile-tiles">
                <a class="hm-tile hm-tile--photo" href="/profil-daerah/kemudahan-awam" data-reveal>
                    <img src="/assets/img/home/kemudahan.webp" alt="" width="800" height="673" loading="lazy">
                    <strong>Kemudahan Awam</strong><span>Sekolah, klinik, balai</span>
                </a>
                <a class="hm-tile hm-tile--photo" href="/profil-daerah/senarai-kampung" data-reveal>
                    <img src="/assets/img/home/kampung.webp" alt="" width="900" height="675" loading="lazy">
                    <strong>Senarai Kampung</strong><span>210 kampung · 15 mukim</span>
                </a>
                <a class="hm-tile" href="/profil-daerah/wakil-rakyat" data-reveal>
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 7M21.5 20a6.5 6.5 0 0 0-4-6"/></svg>
                    <strong>Wakil Rakyat</strong><span>Ahli Parlimen &amp; ADUN</span>
                </a>
                <a class="hm-tile" href="/profil-daerah/data-penduduk" data-reveal>
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg>
                    <strong>Data Penduduk</strong><span>Banci 2020 &amp; data kampung</span>
                </a>
                <a class="hm-tile hm-tile--wide" href="/profil-daerah/ekasih" data-reveal>
                    <span><strong>eKasih</strong><span>Program dan data kemiskinan Daerah Ranau</span></span>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>
            <small class="hm-credit">Foto kemudahan awam: hospital.com.my</small>
        </div>
    </div>
</section>

<section class="hm-section hm-dark" id="kalendar" aria-labelledby="hm-gallery-title">
    <div class="hm-wrap">
        <div class="hm-head">
            <div>
                <p class="hm-eyebrow hm-eyebrow--gold">Galeri &amp; Pautan</p>
                <h2 id="hm-gallery-title" class="hm-title">Aktiviti terkini di Ranau</h2>
            </div>
            <a class="hm-more" href="/galeri-pautan/gambar">Semua album
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
        <div class="hm-albums">
            <a class="hm-album" href="/galeri-pautan/gambar" data-reveal>
                <img src="/assets/img/home/album-town-hall.webp" alt="" width="800" height="533" loading="lazy">
                <span><small>8 Feb 2024 · 8 foto</small><strong>Town Hall Pelan Pembangunan Strategik 2040</strong></span>
            </a>
            <a class="hm-album" href="/galeri-pautan/gambar" data-reveal>
                <img src="/assets/img/home/album-kilang-cendawan.webp" alt="" width="800" height="600" loading="lazy">
                <span><small>30 Jan 2024 · 11 foto</small><strong>Perasmian Kilang Cendawan Mesilau</strong></span>
            </a>
            <a class="hm-album" href="/galeri-pautan/gambar" data-reveal>
                <img src="/assets/img/home/album-str.webp" alt="" width="600" height="450" loading="lazy">
                <span><small>5 Ogos 2023 · 6 foto</small><strong>Sumbangan Tunai Rahmah Daerah Ranau</strong></span>
            </a>
            <a class="hm-album" href="/galeri-pautan/gambar" data-reveal>
                <img src="/assets/img/home/album-maal-hijrah.webp" alt="" width="800" height="600" loading="lazy">
                <span><small>1 Ogos 2023 · 6 foto</small><strong>Persediaan Sambutan Maal Hijrah</strong></span>
            </a>
        </div>
        <div class="home-gallery-layout" id="home-gallery">
            <div class="quick-access-grid quick-access-grid--four">
                <a class="quick-access-card" href="/galeri-pautan/gambar" aria-describedby="quick-access-gambar-description">
                    <span class="quick-access-title">
                        <span class="quick-access-icon" aria-hidden="true"><i class="fa-solid fa-images"></i></span>
                        <span class="quick-access-heading"><span class="quick-access-tag">Galeri &amp; Pautan</span><span class="quick-access-name">Gambar</span></span>
                    </span>
                    <span class="quick-access-detail">
                        <span class="quick-access-description" id="quick-access-gambar-description">Lihat koleksi foto program, aktiviti rasmi dan suasana Daerah Ranau.</span>
                        <span class="quick-access-link" aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
                    </span>
                </a>
                <a class="quick-access-card" href="/galeri-pautan/video" aria-describedby="quick-access-video-description">
                    <span class="quick-access-title">
                        <span class="quick-access-icon" aria-hidden="true"><i class="fa-solid fa-video"></i></span>
                        <span class="quick-access-heading"><span class="quick-access-tag">Galeri &amp; Pautan</span><span class="quick-access-name">Video</span></span>
                    </span>
                    <span class="quick-access-detail">
                        <span class="quick-access-description" id="quick-access-video-description">Tonton rakaman aktiviti, liputan program dan video rasmi Pejabat Daerah.</span>
                        <span class="quick-access-link" aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
                    </span>
                </a>
                <a class="quick-access-card" href="/galeri-pautan/intranet" aria-describedby="quick-access-intranet-description">
                    <span class="quick-access-title">
                        <span class="quick-access-icon" aria-hidden="true"><i class="fa-solid fa-network-wired"></i></span>
                        <span class="quick-access-heading"><span class="quick-access-tag">Galeri &amp; Pautan</span><span class="quick-access-name">Intranet</span></span>
                    </span>
                    <span class="quick-access-detail">
                        <span class="quick-access-description" id="quick-access-intranet-description">Akses sistem dan perkhidmatan dalam talian untuk warga pejabat.</span>
                        <span class="quick-access-link" aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
                    </span>
                </a>
                <a class="quick-access-card" href="/galeri-pautan/agensi" aria-describedby="quick-access-agensi-description">
                    <span class="quick-access-title">
                        <span class="quick-access-icon" aria-hidden="true"><i class="fa-solid fa-link"></i></span>
                        <span class="quick-access-heading"><span class="quick-access-tag">Galeri &amp; Pautan</span><span class="quick-access-name">Pautan Agensi</span></span>
                    </span>
                    <span class="quick-access-detail">
                        <span class="quick-access-description" id="quick-access-agensi-description">Layari portal rasmi jabatan dan agensi kerajaan berkaitan.</span>
                        <span class="quick-access-link" aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
                    </span>
                </a>
            </div>
            [[kalendar]]
        </div>
    </div>
</section>

<section class="hm-section hm-links" aria-label="Pautan berkaitan">
    <div class="hm-wrap">
        <div class="affiliated-links" data-affiliated-carousel role="region" aria-roledescription="carousel" aria-labelledby="affiliated-links-title">
            <div class="affiliated-head">
                <div>
                    <h3 id="affiliated-links-title">Pautan Berkaitan</h3>
                    <p id="affiliated-links-note">Pautan luar dibuka dalam tab baharu.</p>
                </div>
            </div>
            <ul class="affiliated-grid" id="affiliated-links-track" tabindex="0" aria-label="Pautan agensi berkaitan">
                <li>
                    <a class="affiliated-card" href="https://www.malaysia.gov.my/my" target="_blank" rel="noopener noreferrer" aria-describedby="affiliated-links-note">
                        <span class="affiliated-icon"><img src="/assets/img/affiliates/mygovernment.png" alt="" width="48" height="48" loading="lazy" decoding="async"></span>
                        <div class="affiliated-body"><h4>MyGovernment</h4><p>Portal Rasmi Kerajaan Malaysia</p></div>
                    </a>
                </li>
                <li>
                    <a class="affiliated-card" href="https://www.sabah-net.com/" target="_blank" rel="noopener noreferrer" aria-describedby="affiliated-links-note">
                        <span class="affiliated-icon"><img src="/assets/img/affiliates/sabah-net.png" alt="" width="48" height="48" loading="lazy" decoding="async"></span>
                        <div class="affiliated-body"><h4>Sabah Net</h4><p>Sabah Net Sdn. Bhd.</p></div>
                    </a>
                </li>
                <li>
                    <a class="affiliated-card" href="https://jtdi.sabah.gov.my/" target="_blank" rel="noopener noreferrer" aria-describedby="affiliated-links-note">
                        <span class="affiliated-icon"><img src="/assets/img/affiliates/jtdi.webp" alt="" width="48" height="48" loading="lazy" decoding="async"></span>
                        <div class="affiliated-body"><h4>JTDI Sabah</h4><p>Jabatan Teknologi Digital dan Inovasi Negeri Sabah</p></div>
                    </a>
                </li>
                <li>
                    <a class="affiliated-card" href="https://jpan.sabah.gov.my/" target="_blank" rel="noopener noreferrer" aria-describedby="affiliated-links-note">
                        <span class="affiliated-icon"><img src="/assets/img/affiliates/jpan.png" alt="" width="48" height="48" loading="lazy" decoding="async"></span>
                        <div class="affiliated-body"><h4>JPAN Sabah</h4><p>Jabatan Perkhidmatan Awam Negeri Sabah</p></div>
                    </a>
                </li>
                <li>
                    <a class="affiliated-card" href="https://www.jpa.gov.my/" target="_blank" rel="noopener noreferrer" aria-describedby="affiliated-links-note">
                        <span class="affiliated-icon"><img src="/assets/img/affiliates/jpa.png" alt="" width="48" height="48" loading="lazy" decoding="async"></span>
                        <div class="affiliated-body"><h4>JPA Malaysia</h4><p>Jabatan Perkhidmatan Awam Malaysia</p></div>
                    </a>
                </li>
                <li>
                    <a class="affiliated-card" href="https://www.sprm.gov.my/" target="_blank" rel="noopener noreferrer" aria-describedby="affiliated-links-note">
                        <span class="affiliated-icon"><img src="/assets/img/affiliates/sprm.png" alt="" width="48" height="48" loading="lazy" decoding="async"></span>
                        <div class="affiliated-body"><h4>SPRM</h4><p>Suruhanjaya Pencegahan Rasuah Malaysia</p></div>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</section>
@endverbatim
@endsection
