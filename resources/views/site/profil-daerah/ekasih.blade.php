@extends('layouts.site')

@section('title', 'eKasih - Pejabat Daerah Ranau')
@section('description', 'Laman web rasmi Pejabat Daerah Ranau, Sabah.')
@section('body_class', 'has-banner')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/ekasih.css') }}">
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
            <li>eKasih</li>
          </ul>
          <h1>eKasih</h1>
        </div>
      </div>
      <section>
        <div class="container">
          <div class="section-head">
            <h2>Maklumat eKasih Daerah Ranau 2023</h2>
            <p>
              Jumlah Ketua Isi Rumah (KIR) eKasih mengikut status bagi Daerah
              Ranau, setakat Jun 2023.
            </p>
          </div>

          <div class="ek-stats">
            <div class="ek-stat ek-tegar">
              <span class="ek-stat-icon"
                ><i class="fa-solid fa-triangle-exclamation"></i
              ></span>
              <p class="ek-stat-label">Miskin Tegar</p>
              <p class="ek-stat-num">1,345</p>
              <p class="ek-stat-pct">29.8% daripada jumlah KIR</p>
            </div>
            <div class="ek-stat ek-miskin">
              <span class="ek-stat-icon"
                ><i class="fa-solid fa-house-chimney-crack"></i
              ></span>
              <p class="ek-stat-label">Miskin</p>
              <p class="ek-stat-num">2,101</p>
              <p class="ek-stat-pct">46.6% daripada jumlah KIR</p>
            </div>
            <div class="ek-stat ek-keluar">
              <span class="ek-stat-icon"
                ><i class="fa-solid fa-arrow-right-from-bracket"></i
              ></span>
              <p class="ek-stat-label">Terkeluar</p>
              <p class="ek-stat-num">1,067</p>
              <p class="ek-stat-pct">23.6% daripada jumlah KIR</p>
            </div>
            <div class="ek-stat ek-all">
              <span class="ek-stat-icon"
                ><i class="fa-solid fa-users"></i
              ></span>
              <p class="ek-stat-label">Jumlah keseluruhan</p>
              <p class="ek-stat-num">4,513</p>
              <p class="ek-stat-pct">KIR eKasih setakat Jun 2023</p>
            </div>
          </div>

          <div
            class="ek-bar-wrap"
            role="img"
            aria-label="Pecahan KIR eKasih mengikut status"
          >
            <div class="ek-bar">
              <span
                class="ek-seg ek-tegar"
                style="width: 29.8%"
                title="Miskin Tegar: 1,345"
              ></span
              ><span
                class="ek-seg ek-miskin"
                style="width: 46.55%"
                title="Miskin: 2,101"
              ></span
              ><span
                class="ek-seg ek-keluar"
                style="width: 23.64%"
                title="Terkeluar: 1,067"
              ></span>
            </div>
            <ul class="ek-legend">
              <li><span class="ek-dot ek-tegar"></span>Miskin Tegar</li>
              <li><span class="ek-dot ek-miskin"></span>Miskin</li>
              <li><span class="ek-dot ek-keluar"></span>Terkeluar</li>
            </ul>
          </div>
        </div>
      </section>

      <section class="section-tint">
        <div class="container">
          <div class="section-head">
            <h2>Status eKasih mengikut DUN</h2>
            <p>
              Taburan status eKasih bagi setiap Dewan Undangan Negeri (DUN) di
              Daerah Ranau, setakat Jun 2023.
            </p>
          </div>
          <div class="ek-table-wrap">
            <table class="ek-table ek-dun">
              <caption class="sr-only">
                Status eKasih mengikut DUN Daerah Ranau setakat Jun 2023
              </caption>
              <thead>
                <tr>
                  <th rowspan="2" scope="col">DUN</th>
                  <th colspan="3" scope="colgroup" class="grp">Status</th>
                  <th rowspan="2" scope="col">Jumlah</th>
                </tr>
                <tr>
                  <th scope="col">Miskin tegar</th>
                  <th scope="col">Miskin</th>
                  <th scope="col">Terkeluar</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <th scope="row">N.36 Kundasang</th>
                  <td>690</td>
                  <td>1,018</td>
                  <td>425</td>
                  <td class="ek-total">2,133</td>
                </tr>
                <tr>
                  <th scope="row">N.37 Karanaan</th>
                  <td>212</td>
                  <td>363</td>
                  <td>200</td>
                  <td class="ek-total">775</td>
                </tr>
                <tr>
                  <th scope="row">N.38 Paginatan</th>
                  <td>442</td>
                  <td>720</td>
                  <td>443</td>
                  <td class="ek-total">1,605</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <section>
        <div class="container">
          <div class="section-head">
            <h2>Pendapatan Garis Kemiskinan (PGK)</h2>
            <p>
              PGK ialah paras pendapatan yang digunakan untuk menentukan status
              kemiskinan sesebuah isi rumah. Isi rumah dengan pendapatan di
              bawah PGK keseluruhan dikategorikan sebagai miskin, manakala di
              bawah PGK makanan dikategorikan sebagai miskin tegar. Jadual di
              bawah menunjukkan PGK mengikut negeri dan strata bagi tahun 2019.
            </p>
          </div>

          <h3 class="ek-subhead">PGK mengikut negeri dan strata, 2019</h3>
          <div class="ek-table-wrap">
            <table class="ek-table ek-pgk">
              <caption class="sr-only">
                Pendapatan Garis Kemiskinan (PGK) mengikut negeri dan strata,
                2019
              </caption>
              <thead>
                <tr>
                  <th rowspan="2" scope="col">Negeri</th>
                  <th colspan="3" scope="colgroup" class="grp">
                    Miskin (&lt; PGK keseluruhan, RM)
                  </th>
                  <th colspan="3" scope="colgroup" class="grp">
                    Miskin tegar (&lt; PGK makanan, RM)
                  </th>
                </tr>
                <tr>
                  <th scope="col">Bandar</th>
                  <th scope="col">Luar bandar</th>
                  <th scope="col">Jumlah</th>
                  <th scope="col">Bandar</th>
                  <th scope="col">Luar bandar</th>
                  <th scope="col">Jumlah</th>
                </tr>
              </thead>
              <tbody>
                <tr class="is-my">
                  <th scope="row">Malaysia</th>
                  <td>2,210</td>
                  <td>2,199</td>
                  <td>2,208</td>
                  <td>1,176</td>
                  <td>1,142</td>
                  <td>1,169</td>
                </tr>
                <tr>
                  <th scope="row">Johor</th>
                  <td>2,517</td>
                  <td>2,455</td>
                  <td>2,505</td>
                  <td>1,267</td>
                  <td>1,257</td>
                  <td>1,165</td>
                </tr>
                <tr>
                  <th scope="row">Kedah</th>
                  <td>2,267</td>
                  <td>2,218</td>
                  <td>2,254</td>
                  <td>1,219</td>
                  <td>1,201</td>
                  <td>1,214</td>
                </tr>
                <tr>
                  <th scope="row">Kelantan</th>
                  <td>2,158</td>
                  <td>2,119</td>
                  <td>2,139</td>
                  <td>1,184</td>
                  <td>1,177</td>
                  <td>1,181</td>
                </tr>
                <tr>
                  <th scope="row">Melaka</th>
                  <td>2,387</td>
                  <td>2,140</td>
                  <td>2,375</td>
                  <td>1,289</td>
                  <td>1,070</td>
                  <td>1,279</td>
                </tr>
                <tr>
                  <th scope="row">Negeri Sembilan</th>
                  <td>3,138</td>
                  <td>1,930</td>
                  <td>2,088</td>
                  <td>1,241</td>
                  <td>1,136</td>
                  <td>1,216</td>
                </tr>
                <tr>
                  <th scope="row">Pahang</th>
                  <td>2,344</td>
                  <td>2,146</td>
                  <td>2,270</td>
                  <td>1,265</td>
                  <td>1,093</td>
                  <td>1,201</td>
                </tr>
                <tr>
                  <th scope="row">Pulau Pinang</th>
                  <td>1,974</td>
                  <td>2,379</td>
                  <td>1,989</td>
                  <td>993</td>
                  <td>1,281</td>
                  <td>1,004</td>
                </tr>
                <tr>
                  <th scope="row">Perak</th>
                  <td>2,089</td>
                  <td>2,025</td>
                  <td>2,077</td>
                  <td>1,118</td>
                  <td>1,035</td>
                  <td>1,102</td>
                </tr>
                <tr>
                  <th scope="row">Perlis</th>
                  <td>2,012</td>
                  <td>1,871</td>
                  <td>1,967</td>
                  <td>1,176</td>
                  <td>1,042</td>
                  <td>1,133</td>
                </tr>
                <tr>
                  <th scope="row">Selangor</th>
                  <td>2,029</td>
                  <td>1,901</td>
                  <td>2,022</td>
                  <td>1,171</td>
                  <td>1,074</td>
                  <td>1,166</td>
                </tr>
                <tr>
                  <th scope="row">Terengganu</th>
                  <td>2,520</td>
                  <td>2,477</td>
                  <td>2,507</td>
                  <td>1,312</td>
                  <td>1,311</td>
                  <td>1,312</td>
                </tr>
                <tr class="is-sabah">
                  <th scope="row">Sabah</th>
                  <td>2,506</td>
                  <td>2,589</td>
                  <td>2,537</td>
                  <td>1,160</td>
                  <td>1,209</td>
                  <td>1,179</td>
                </tr>
                <tr>
                  <th scope="row">Sarawak</th>
                  <td>2,243</td>
                  <td>1,979</td>
                  <td>2,131</td>
                  <td>1,160</td>
                  <td>1,009</td>
                  <td>1,096</td>
                </tr>
                <tr>
                  <th scope="row">W.P. Kuala Lumpur</th>
                  <td>2,216</td>
                  <td>–</td>
                  <td>2,216</td>
                  <td>1,110</td>
                  <td>–</td>
                  <td>1,110</td>
                </tr>
                <tr>
                  <th scope="row">W.P. Labuan</th>
                  <td>2,627</td>
                  <td>2,684</td>
                  <td>2,633</td>
                  <td>1,318</td>
                  <td>1,328</td>
                  <td>1,319</td>
                </tr>
                <tr>
                  <th scope="row">W.P. Putrajaya</th>
                  <td>2,128</td>
                  <td>–</td>
                  <td>2,128</td>
                  <td>1,074</td>
                  <td>–</td>
                  <td>1,074</td>
                </tr>
              </tbody>
            </table>
          </div>

          <h3 class="ek-subhead">
            PGK per kapita mengikut negeri dan strata, 2019
          </h3>
          <div class="ek-table-wrap">
            <table class="ek-table ek-pgk">
              <caption class="sr-only">
                Pendapatan Garis Kemiskinan (PGK) per kapita mengikut negeri dan
                strata, 2019
              </caption>
              <thead>
                <tr>
                  <th rowspan="2" scope="col">Negeri</th>
                  <th colspan="3" scope="colgroup" class="grp">
                    Miskin (&lt; PGK keseluruhan, RM)
                  </th>
                  <th colspan="3" scope="colgroup" class="grp">
                    Miskin tegar (&lt; PGK makanan, RM)
                  </th>
                </tr>
                <tr>
                  <th scope="col">Bandar</th>
                  <th scope="col">Luar bandar</th>
                  <th scope="col">Jumlah</th>
                  <th scope="col">Bandar</th>
                  <th scope="col">Luar bandar</th>
                  <th scope="col">Jumlah</th>
                </tr>
              </thead>
              <tbody>
                <tr class="is-my">
                  <th scope="row">Malaysia</th>
                  <td>601</td>
                  <td>567</td>
                  <td>594</td>
                  <td>315</td>
                  <td>290</td>
                  <td>310</td>
                </tr>
                <tr>
                  <th scope="row">Johor</th>
                  <td>701</td>
                  <td>658</td>
                  <td>692</td>
                  <td>347</td>
                  <td>331</td>
                  <td>344</td>
                </tr>
                <tr>
                  <th scope="row">Kedah</th>
                  <td>619</td>
                  <td>583</td>
                  <td>609</td>
                  <td>327</td>
                  <td>311</td>
                  <td>323</td>
                </tr>
                <tr>
                  <th scope="row">Kelantan</th>
                  <td>523</td>
                  <td>497</td>
                  <td>510</td>
                  <td>282</td>
                  <td>272</td>
                  <td>277</td>
                </tr>
                <tr>
                  <th scope="row">Melaka</th>
                  <td>634</td>
                  <td>568</td>
                  <td>631</td>
                  <td>337</td>
                  <td>280</td>
                  <td>334</td>
                </tr>
                <tr>
                  <th scope="row">Negeri Sembilan</th>
                  <td>596</td>
                  <td>567</td>
                  <td>589</td>
                  <td>341</td>
                  <td>328</td>
                  <td>338</td>
                </tr>
                <tr>
                  <th scope="row">Pahang</th>
                  <td>653</td>
                  <td>590</td>
                  <td>630</td>
                  <td>348</td>
                  <td>296</td>
                  <td>328</td>
                </tr>
                <tr>
                  <th scope="row">Pulau Pinang</th>
                  <td>581</td>
                  <td>583</td>
                  <td>581</td>
                  <td>289</td>
                  <td>311</td>
                  <td>289</td>
                </tr>
                <tr>
                  <th scope="row">Perak</th>
                  <td>523</td>
                  <td>569</td>
                  <td>612</td>
                  <td>328</td>
                  <td>287</td>
                  <td>320</td>
                </tr>
                <tr>
                  <th scope="row">Perlis</th>
                  <td>549</td>
                  <td>493</td>
                  <td>531</td>
                  <td>317</td>
                  <td>271</td>
                  <td>302</td>
                </tr>
                <tr>
                  <th scope="row">Selangor</th>
                  <td>534</td>
                  <td>501</td>
                  <td>532</td>
                  <td>305</td>
                  <td>280</td>
                  <td>303</td>
                </tr>
                <tr>
                  <th scope="row">Terengganu</th>
                  <td>555</td>
                  <td>546</td>
                  <td>552</td>
                  <td>286</td>
                  <td>287</td>
                  <td>286</td>
                </tr>
                <tr class="is-sabah">
                  <th scope="row">Sabah</th>
                  <td>599</td>
                  <td>576</td>
                  <td>590</td>
                  <td>274</td>
                  <td>265</td>
                  <td>271</td>
                </tr>
                <tr>
                  <th scope="row">Sarawak</th>
                  <td>579</td>
                  <td>552</td>
                  <td>568</td>
                  <td>294</td>
                  <td>276</td>
                  <td>286</td>
                </tr>
                <tr>
                  <th scope="row">W.P. Kuala Lumpur</th>
                  <td>685</td>
                  <td>–</td>
                  <td>685</td>
                  <td>339</td>
                  <td>–</td>
                  <td>339</td>
                </tr>
                <tr>
                  <th scope="row">W.P. Labuan</th>
                  <td>630</td>
                  <td>642</td>
                  <td>632</td>
                  <td>313</td>
                  <td>313</td>
                  <td>313</td>
                </tr>
                <tr>
                  <th scope="row">W.P. Putrajaya</th>
                  <td>583</td>
                  <td>–</td>
                  <td>593</td>
                  <td>291</td>
                  <td>–</td>
                  <td>291</td>
                </tr>
              </tbody>
            </table>
          </div>

          <p class="ek-source">
            Sumber: Unit Perancang Ekonomi (EPU), 2019 &middot; eKasih, setakat
            Jun 2023
          </p>
        </div>
      </section>
    
@endverbatim
@endsection
