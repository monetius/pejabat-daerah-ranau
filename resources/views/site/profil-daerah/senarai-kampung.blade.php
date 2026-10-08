@extends('layouts.site')

@section('title', 'Senarai Kampung - Pejabat Daerah Ranau')
@section('description', 'Senarai kampung di Daerah Ranau mengikut DUN dan mukim.')
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
            <li>Senarai Kampung</li>
          </ul>
          <h1>Senarai Kampung</h1>
        </div>
      </div>

      <div class="sk-intro">
        <div class="container">
          <p class="sk-lead">
            Senarai kampung di Daerah Ranau mengikut Dewan Undangan Negeri (DUN)
            dan mukim. Jumlah keseluruhan: <strong>210 kampung</strong> dalam
            <strong>15 mukim</strong>.
          </p>
          <div class="sk-tools">
            <label class="sk-search"
              ><i class="fa-solid fa-magnifying-glass"></i>
              <input
                type="search"
                id="sk-q"
                placeholder="Cari nama kampung..."
                autocomplete="off"
                aria-label="Cari nama kampung"
              />
            </label>
            <nav class="sk-jump" aria-label="Lompat ke DUN">
              <a href="#karanaan"
                ><i class="fa-solid fa-location-dot"></i> DUN Karanaan
                <span class="sk-count">46</span></a
              ><a href="#paginatan"
                ><i class="fa-solid fa-location-dot"></i> DUN Paginatan
                <span class="sk-count">80</span></a
              ><a href="#kundasang"
                ><i class="fa-solid fa-location-dot"></i> DUN Kundasang
                <span class="sk-count">84</span></a
              >
            </nav>
          </div>
          <p
            id="sk-status"
            class="sk-status"
            role="status"
            aria-live="polite"
            hidden
          ></p>
        </div>
      </div>

      <section id="karanaan" class="sk-dun">
        <div class="container">
          <div class="sk-dun-head">
            <span class="sk-tag">DUN</span>
            <h2>Karanaan</h2>
            <p>4 mukim &middot; 46 kampung</p>
          </div>
          <div class="sk-grid">
            <article class="sk-card" data-mukim="Tagudon">
              <header>
                <h3>Tagudon</h3>
                <span class="sk-badge">14</span>
              </header>
              <ul>
                <li>Himbaan Bundu Tuhan</li>
                <li>Karanaan</li>
                <li>Kemburongoh</li>
                <li>Pahu</li>
                <li>Piasau</li>
                <li>Ratau</li>
                <li>Sosondoton</li>
                <li>Tagudon Lama</li>
                <li>Terolobou</li>
                <li>Tinatasan</li>
                <li>Toboh Lama</li>
                <li>Toboh Pahu</li>
                <li>Tudan I</li>
                <li>Tudan II</li>
              </ul>
            </article>
            <article class="sk-card" data-mukim="Liwagu">
              <header>
                <h3>Liwagu</h3>
                <span class="sk-badge">11</span>
              </header>
              <ul>
                <li>Kandawayon</li>
                <li>Kesiladan</li>
                <li>Kiaburi</li>
                <li>Kimolohing</li>
                <li>Lingkudau</li>
                <li>Longut Baru</li>
                <li>Mininsalu</li>
                <li>Muhibbah</li>
                <li>Paka II</li>
                <li>Rapak/Takurik</li>
                <li>Tiang Baru</li>
              </ul>
            </article>
            <article class="sk-card" data-mukim="Tambiau">
              <header>
                <h3>Tambiau</h3>
                <span class="sk-badge">12</span>
              </header>
              <ul>
                <li>Kaparingan</li>
                <li>Kibbas</li>
                <li>Kiwawoi</li>
                <li>Lipasu Baru</li>
                <li>Lipasu Lama</li>
                <li>Mohimboyon</li>
                <li>Paka I</li>
                <li>Purakagis</li>
                <li>Sarapong</li>
                <li>Tambiau</li>
                <li>Toboh Baru</li>
                <li>Waang</li>
              </ul>
            </article>
            <article class="sk-card" data-mukim="Randagong">
              <header>
                <h3>Randagong</h3>
                <span class="sk-badge">9</span>
              </header>
              <ul>
                <li>Mumpait</li>
                <li>Kesisingon</li>
                <li>Kinarasan</li>
                <li>Langkau</li>
                <li>Maukab</li>
                <li>Poropot</li>
                <li>Randagong Lama</li>
                <li>Sumalang</li>
                <li>Tungou</li>
              </ul>
            </article>
          </div>
        </div>
      </section>
      <section id="paginatan" class="sk-dun">
        <div class="container">
          <div class="sk-dun-head">
            <span class="sk-tag">DUN</span>
            <h2>Paginatan</h2>
            <p>6 mukim &middot; 80 kampung</p>
          </div>
          <div class="sk-grid">
            <article class="sk-card" data-mukim="Tanah Rata">
              <header>
                <h3>Tanah Rata</h3>
                <span class="sk-badge">21</span>
              </header>
              <ul>
                <li>Badukan</li>
                <li>Bahab</li>
                <li>Kigiok</li>
                <li>Kilimu</li>
                <li>Kinapulidan</li>
                <li>Kituntul Baru</li>
                <li>Kituntul Lama</li>
                <li>Kokob Baru</li>
                <li>Lasing</li>
                <li>Libang</li>
                <li>Lipoi/Tegoyog</li>
                <li>Lukapon</li>
                <li>Marakau</li>
                <li>Matan</li>
                <li>Mindahuon</li>
                <li>Rugading</li>
                <li>Silou</li>
                <li>Sinarut</li>
                <li>Tagudon Baru</li>
                <li>Tanah Merah</li>
                <li>Tudangan</li>
              </ul>
            </article>
            <article class="sk-card" data-mukim="Paus">
              <header>
                <h3>Paus</h3>
                <span class="sk-badge">20</span>
              </header>
              <ul>
                <li>Bitton</li>
                <li>Bunakon</li>
                <li>Kodop Baru</li>
                <li>Lungkidau</li>
                <li>Mentapuk</li>
                <li>Nunuk Ragang</li>
                <li>Paginatan</li>
                <li>Paus</li>
                <li>Soborong</li>
                <li>Terikon/Tadsom</li>
                <li>Tinanom</li>
                <li>Toupus</li>
                <li>Balisauk</li>
                <li>Kigiwit</li>
                <li>Maringkan</li>
                <li>Matupang</li>
                <li>Panulangon</li>
                <li>Taba</li>
                <li>Tompios</li>
                <li>Wakaku</li>
              </ul>
            </article>
            <article class="sk-card" data-mukim="Bongkud/Lohan">
              <header>
                <h3>Bongkud/Lohan</h3>
                <span class="sk-badge">17</span>
              </header>
              <ul>
                <li>Bangkod</li>
                <li>Karanaan Baru</li>
                <li>Kinaratuan</li>
                <li>Kinasaraban Baru</li>
                <li>Lohan I</li>
                <li>Lohan Skim 2</li>
                <li>Lohan Ulu</li>
                <li>Lutut</li>
                <li>Minihas</li>
                <li>Namaus/Bongkud</li>
                <li>Nampasan Baru</li>
                <li>Napong I</li>
                <li>Napong II</li>
                <li>Narawang</li>
                <li>Poring</li>
                <li>Simpangan Poring</li>
                <li>Waluhu</li>
              </ul>
            </article>
            <article class="sk-card" data-mukim="Nalapak/Sagindai">
              <header>
                <h3>Nalapak/Sagindai</h3>
                <span class="sk-badge">9</span>
              </header>
              <ul>
                <li>Kebuh Baru</li>
                <li>Luanti Baru</li>
                <li>Muruk</li>
                <li>Nabutan</li>
                <li>Nalapak</li>
                <li>Sagindai Baru</li>
                <li>Sedul I</li>
                <li>Sedul II</li>
                <li>Segindai Lama</li>
              </ul>
            </article>
            <article class="sk-card" data-mukim="Paginatan">
              <header>
                <h3>Paginatan</h3>
                <span class="sk-badge">2</span>
              </header>
              <ul>
                <li>Mangkadait</li>
                <li>Miruru</li>
              </ul>
            </article>
            <article class="sk-card" data-mukim="Kapangian/Suminimpod">
              <header>
                <h3>Kapangian/Suminimpod</h3>
                <span class="sk-badge">11</span>
              </header>
              <ul>
                <li>Bayag</li>
                <li>Gana-Gana</li>
                <li>Gaur</li>
                <li>Giring-Giring</li>
                <li>Kepangian</li>
                <li>Kitaie</li>
                <li>Logut Lama</li>
                <li>Nampasan Lama</li>
                <li>Niasan</li>
                <li>Suminimpod</li>
                <li>Tiang Lama</li>
              </ul>
            </article>
          </div>
        </div>
      </section>
      <section id="kundasang" class="sk-dun">
        <div class="container">
          <div class="sk-dun-head">
            <span class="sk-tag">DUN</span>
            <h2>Kundasang</h2>
            <p>5 mukim &middot; 84 kampung</p>
          </div>
          <div class="sk-grid">
            <article class="sk-card" data-mukim="Timbua">
              <header>
                <h3>Timbua</h3>
                <span class="sk-badge">23</span>
              </header>
              <ul>
                <li>Daramakan</li>
                <li>Kantas Baru</li>
                <li>Kemberoi</li>
                <li>Kioyep</li>
                <li>Lobou Baru / Merungin Scheme</li>
                <li>Lobou Lama</li>
                <li>Lobou Timbua</li>
                <li>Merungin I</li>
                <li>Merungin II</li>
                <li>Monggis</li>
                <li>Nawanon</li>
                <li>Pahu Pinawantai</li>
                <li>Pinampadan</li>
                <li>Pinawantai</li>
                <li>Pugi</li>
                <li>Tarawas</li>
                <li>Tibabar</li>
                <li>Timbua/Tebut</li>
                <li>Tinutuan</li>
                <li>Togop Darat</li>
                <li>Togop Laut</li>
                <li>Tumbalang</li>
                <li>Turuntungon</li>
              </ul>
            </article>
            <article class="sk-card" data-mukim="Kaingaran">
              <header>
                <h3>Kaingaran</h3>
                <span class="sk-badge">20</span>
              </header>
              <ul>
                <li>Agan Ulu Sugut</li>
                <li>Betong</li>
                <li>Giring Ulu Sugut</li>
                <li>Gusi Ulu Sugut</li>
                <li>Kaingaran Ulu Sugut</li>
                <li>Karagasan</li>
                <li>Kawiyan</li>
                <li>Kilanas Ulu Sugut</li>
                <li>Kiwakau Sugut</li>
                <li>Kotog Ulu Sugut</li>
                <li>Manikulau</li>
                <li>Mansalu Ulu Sugut</li>
                <li>Meridi Sugut</li>
                <li>Namaus</li>
                <li>Paka Ulu Sugut</li>
                <li>Pamaitan</li>
                <li>Patau Ulu Sugut</li>
                <li>Pinutudan</li>
                <li>Tinangian Baru</li>
                <li>Tundangon</li>
              </ul>
            </article>
            <article class="sk-card" data-mukim="Kundasang">
              <header>
                <h3>Kundasang</h3>
                <span class="sk-badge">20</span>
              </header>
              <ul>
                <li>Cinta Mata</li>
                <li>Desa Aman</li>
                <li>Dumpiring Atas</li>
                <li>Dumpiring Bawah</li>
                <li>Gondohon</li>
                <li>Kalangaan</li>
                <li>Kauluan</li>
                <li>Kinandusan</li>
                <li>Kinasaraban</li>
                <li>Kundasang Lama</li>
                <li>Lembah Permai</li>
                <li>Mesilou</li>
                <li>Naradau</li>
                <li>Pinausok</li>
                <li>Ruhukon</li>
                <li>Semuroh</li>
                <li>Siba Bundu Tuhan</li>
                <li>Sinisian</li>
                <li>Sokid Bundu Tuhan</li>
                <li>Tamalang</li>
              </ul>
            </article>
            <article class="sk-card" data-mukim="Malinsau">
              <header>
                <h3>Malinsau</h3>
                <span class="sk-badge">12</span>
              </header>
              <ul>
                <li>Kapuakan</li>
                <li>Linapasan Ulu Sugut</li>
                <li>Malinsau Darat/Nasakot</li>
                <li>Malinsau/Mampakot</li>
                <li>Mangkapoh 1</li>
                <li>Mokodou Ulu Sugut</li>
                <li>Sinurai Ulu Sugut</li>
                <li>Sumbilangon</li>
                <li>Tanid Sugut</li>
                <li>Tinaum Ulu Sugut</li>
                <li>Wayan</li>
                <li>Tinindoi</li>
              </ul>
            </article>
            <article class="sk-card" data-mukim="Perancangan">
              <header>
                <h3>Perancangan</h3>
                <span class="sk-badge">9</span>
              </header>
              <ul>
                <li>Debut</li>
                <li>Kilanas Baru</li>
                <li>Kirokot</li>
                <li>Langsat</li>
                <li>Nalumad</li>
                <li>Perancangan</li>
                <li>Singgaron Baru</li>
                <li>Takutan</li>
                <li>Togis</li>
              </ul>
            </article>
          </div>
        </div>
      </section>
    
@endverbatim
@endsection

@push('scripts')
@verbatim
<script>
      (function () {
        var q = document.getElementById("sk-q");
        var status = document.getElementById("sk-status");
        var cards = document.querySelectorAll(".sk-card");
        var duns = document.querySelectorAll(".sk-dun");
        function norm(s) {
          return s.toLowerCase().trim();
        }
        q.addEventListener("input", function () {
          var t = norm(q.value),
            hits = 0;
          cards.forEach(function (c) {
            var any = false;
            c.querySelectorAll("li").forEach(function (li) {
              var m = !t || norm(li.textContent).indexOf(t) !== -1;
              li.hidden = !m;
              if (m) {
                any = true;
                if (t) hits++;
              }
            });
            c.hidden = !any;
          });
          duns.forEach(function (d) {
            d.hidden = !d.querySelector(".sk-card:not([hidden])");
          });
          if (!t) {
            status.hidden = true;
            return;
          }
          status.hidden = false;
          status.textContent = hits
            ? hits + " kampung dijumpai."
            : "Tiada kampung dijumpai.";
        });
      })();
    </script>
@endverbatim
@endpush
