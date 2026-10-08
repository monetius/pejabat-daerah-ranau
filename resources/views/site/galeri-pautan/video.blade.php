@extends('layouts.site')

@section('title', 'Video - Pejabat Daerah Ranau')
@section('description', 'Video Pejabat Daerah Ranau, Sabah: panduan eLESEN, lagu tema Sabah Maju Jaya, lagu integriti dan lagu kebangsaan.')
@section('body_class', 'has-banner')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/video.css') }}">
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
            <li>Galeri</li>
            <li>Video</li>
          </ul>
          <h1>Video</h1>
        </div>
      </div>
      <section>
        <div class="container">
          <p class="vd-intro">Koleksi video Pejabat Daerah Ranau (5 video). Klik gambar untuk menonton di halaman ini.</p>
          <div class="vd-grid">
            <article class="vd-card">
              <button class="vd-media" type="button" data-id="i8llvj0nSUY" data-title="Panduan Mengisi Permohonan Lesen Berniaga (eLESEN) bagi Daerah Ranau" aria-label="Main video: Panduan Mengisi Permohonan Lesen Berniaga (eLESEN) bagi Daerah Ranau"><img src="https://i.ytimg.com/vi/i8llvj0nSUY/hqdefault.jpg" alt="" loading="lazy" /><span class="vd-play"><i class="fa-solid fa-play"></i></span></button>
              <div class="vd-body"><h3>Panduan Mengisi Permohonan Lesen Berniaga (eLESEN) bagi Daerah Ranau</h3><a href="https://www.youtube.com/watch?v=i8llvj0nSUY" target="_blank" rel="noopener">Tonton di YouTube <i class="fa-solid fa-arrow-up-right-from-square"></i></a></div>
            </article>
            <article class="vd-card">
              <button class="vd-media" type="button" data-id="H9418OCsGpY" data-title="Video Lagu Tema &quot;Sabah Maju Jaya&quot; Tahun 2023" aria-label="Main video: Video Lagu Tema &quot;Sabah Maju Jaya&quot; Tahun 2023"><img src="https://i.ytimg.com/vi/H9418OCsGpY/hqdefault.jpg" alt="" loading="lazy" /><span class="vd-play"><i class="fa-solid fa-play"></i></span></button>
              <div class="vd-body"><h3>Video Lagu Tema &quot;Sabah Maju Jaya&quot; Tahun 2023</h3><a href="https://www.youtube.com/watch?v=H9418OCsGpY" target="_blank" rel="noopener">Tonton di YouTube <i class="fa-solid fa-arrow-up-right-from-square"></i></a></div>
            </article>
            <article class="vd-card">
              <button class="vd-media" type="button" data-id="Gfs5pyUGDvc" data-title="Lagu Integriti Negeri Sabah 2023" aria-label="Main video: Lagu Integriti Negeri Sabah 2023"><img src="https://i.ytimg.com/vi/Gfs5pyUGDvc/hqdefault.jpg" alt="" loading="lazy" /><span class="vd-play"><i class="fa-solid fa-play"></i></span></button>
              <div class="vd-body"><h3>Lagu Integriti Negeri Sabah 2023</h3><a href="https://www.youtube.com/watch?v=Gfs5pyUGDvc" target="_blank" rel="noopener">Tonton di YouTube <i class="fa-solid fa-arrow-up-right-from-square"></i></a></div>
            </article>
            <article class="vd-card">
              <button class="vd-media" type="button" data-id="0ENs7ZJ6Opc" data-title="Lagu Negaraku" aria-label="Main video: Lagu Negaraku"><img src="https://i.ytimg.com/vi/0ENs7ZJ6Opc/hqdefault.jpg" alt="" loading="lazy" /><span class="vd-play"><i class="fa-solid fa-play"></i></span></button>
              <div class="vd-body"><h3>Lagu Negaraku</h3><a href="https://www.youtube.com/watch?v=0ENs7ZJ6Opc" target="_blank" rel="noopener">Tonton di YouTube <i class="fa-solid fa-arrow-up-right-from-square"></i></a></div>
            </article>
            <article class="vd-card">
              <button class="vd-media" type="button" data-id="lawA78Jwk-U" data-title="Lagu Sabah Tanah Airku" aria-label="Main video: Lagu Sabah Tanah Airku"><img src="https://i.ytimg.com/vi/lawA78Jwk-U/hqdefault.jpg" alt="" loading="lazy" /><span class="vd-play"><i class="fa-solid fa-play"></i></span></button>
              <div class="vd-body"><h3>Lagu Sabah Tanah Airku</h3><a href="https://www.youtube.com/watch?v=lawA78Jwk-U" target="_blank" rel="noopener">Tonton di YouTube <i class="fa-solid fa-arrow-up-right-from-square"></i></a></div>
            </article>
          </div>
          <p class="vd-more"><a href="https://www.youtube.com/channel/UCvwWvTwbqAfZPhrta6clnBA/videos" target="_blank" rel="noopener">Lihat semua video di saluran YouTube Pejabat Daerah Ranau <i class="fa-solid fa-arrow-up-right-from-square"></i></a></p>
        </div>
      </section>
      <script>
        document.querySelectorAll(".vd-media").forEach(function (b) {
          b.addEventListener("click", function () {
            var f = document.createElement("iframe");
            f.src = "https://www.youtube-nocookie.com/embed/" + b.dataset.id + "?autoplay=1&rel=0";
            f.title = b.dataset.title;
            f.allow = "autoplay; encrypted-media; picture-in-picture; fullscreen";
            f.allowFullscreen = true;
            b.replaceWith(f.parentNode ? f : (function () { var w = document.createElement("div"); w.className = "vd-media"; w.appendChild(f); return w; })());
          });
        });
      </script>
    
@endverbatim
@endsection
