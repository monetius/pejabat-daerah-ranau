@extends('layouts.site')

@section('title', 'eKasih - Pejabat Daerah Ranau')
@section('description', 'Laman web rasmi Pejabat Daerah Ranau, Sabah.')
@section('body_class', '')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/ekasih.css') }}">
@endpush

@section('content')
  @verbatim

    <div class="page-header page-header--plain">
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
          <h2>Maklumat eKasih Daerah Ranau 2026</h2>
          <p>
            Jumlah Ketua Isi Rumah (KIR) eKasih mengikut status bagi Daerah Ranau, setakat 30 September 2026.
          </p>
        </div>

        <div class="ek-stats ek-stats-current">
          <div class="ek-stat ek-miskin">
            <span class="ek-stat-icon"><i class="fa-solid fa-house-chimney-crack" aria-hidden="true"></i></span>
            <p class="ek-stat-label">Miskin</p>
            <p class="ek-stat-num">2,928</p>
            <p class="ek-stat-pct">51.7% daripada jumlah KIR</p>
          </div>
          <div class="ek-stat ek-keluar">
            <span class="ek-stat-icon"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i></span>
            <p class="ek-stat-label">Terkeluar</p>
            <p class="ek-stat-num">2,739</p>
            <p class="ek-stat-pct">48.3% daripada jumlah KIR</p>
          </div>
          <div class="ek-stat ek-all">
            <span class="ek-stat-icon"><i class="fa-solid fa-users" aria-hidden="true"></i></span>
            <p class="ek-stat-label">Jumlah keseluruhan</p>
            <p class="ek-stat-num">5,667</p>
            <p class="ek-stat-pct">KIR eKasih setakat 30 September 2026</p>
          </div>
        </div>
        <div class="ek-bar-wrap" role="img" aria-label="Pecahan KIR eKasih setakat 30 September 2026: Miskin 2,928 (51.7%); Terkeluar 2,739 (48.3%).">
          <div class="ek-bar">
            <span class="ek-seg ek-miskin" style="width: 51.7%" title="Miskin: 2,928 (51.7%)"></span>
            <span class="ek-seg ek-keluar" style="width: 48.3%" title="Terkeluar: 2,739 (48.3%)"></span>
          </div>
          <ul class="ek-legend">
            <li><span class="ek-dot ek-miskin"></span>Miskin</li>
            <li><span class="ek-dot ek-keluar"></span>Terkeluar</li>
          </ul>
        </div>
      </div>
    </section>

    <section>
      <div class="container">
        <div class="section-head">
          <h2>Rujukan rasmi ICU JPM: 15 Januari 2026</h2>
          <p>Statistik status kemiskinan nasional dan negeri Sabah setakat 15 Januari 2026 (dinamik), berdasarkan laporan ICU JPM.</p>
        </div>
        <div class="ek-table-wrap">
          <table class="ek-table ek-dun ek-icu-reference">
            <caption class="sr-only">KIR miskin dan miskin tegar bagi Malaysia dan Sabah setakat 15 Januari 2026</caption>
            <thead>
              <tr>
                <th scope="col">Kawasan</th>
                <th scope="col">Miskin (KIR)</th>
                <th scope="col">Miskin tegar (KIR)</th>
                <th scope="col">Jumlah (KIR)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th scope="row">Malaysia (nasional)</th>
                <td>374,292</td>
                <td>50,178</td>
                <td class="ek-total">424,470</td>
              </tr>
              <tr>
                <th scope="row">Sabah (negeri)</th>
                <td>67,133</td>
                <td>10,854</td>
                <td class="ek-total">77,987</td>
              </tr>
            </tbody>
          </table>
        </div>
        <p class="ek-source">
          Jumlah dalam laporan ini ialah gabungan KIR miskin dan miskin tegar. Angka ini mempunyai skop dan tarikh berbeza daripada statistik Daerah Ranau di atas.
          <a href="https://www.icu.gov.my/pdf/stat_ekasih/MAP_Laporan_Statistik_Kemiskinan_Nasional_15012026..pdf">Lihat laporan rasmi ICU JPM Januari 2026 (PDF)</a>.
        </p>
      </div>
    </section>

    <section class="section-tint">
      <div class="container">
        <div class="section-head">
          <h2>Rekod eKasih mengikut DUN: Jun 2023</h2>
          <p>
            Taburan KIR mengikut Dewan Undangan Negeri (DUN) bagi rekod Jun 2023. Jadual sejarah ini berasingan daripada ringkasan daerah setakat 30 September 2026.
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
            bawah PGK makanan dikategorikan sebagai miskin tegar. Laporan ICU JPM setakat 15 Januari 2026 memaparkan rujukan PGK Malaysia di bawah. Nilai PGK isi rumah adalah dalam RM sebulan.
          </p>
        </div>

        <h3 class="ek-subhead">PGK Malaysia: rujukan laporan 15 Januari 2026</h3>
        <div class="ek-table-wrap">
          <table class="ek-table ek-national-pgk">
            <caption class="sr-only">PGK Malaysia dalam laporan ICU JPM setakat 15 Januari 2026, dalam RM sebulan</caption>
            <thead>
              <tr>
                <th scope="col">Kategori</th>
                <th scope="col">PGK Malaysia (RM sebulan)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th scope="row">Miskin (PGK keseluruhan)</th>
                <td>2,705</td>
              </tr>
              <tr>
                <th scope="row">Miskin tegar (PGK makanan)</th>
                <td>1,236</td>
              </tr>
            </tbody>
          </table>
        </div>
        <p class="ek-source">
          Rujukan nasional: <a href="https://www.icu.gov.my/pdf/stat_ekasih/MAP_Laporan_Statistik_Kemiskinan_Nasional_15012026..pdf">Laporan ICU JPM Januari 2026 (PDF)</a>.
          Jadual terperinci mengikut negeri dan strata di bawah ialah rujukan tahun 2022 daripada dokumen KKDW.
        </p>

        <h3 class="ek-subhead">PGK isi rumah mengikut negeri dan strata, 2022</h3>
        <div class="ek-table-wrap">
          <table class="ek-table ek-pgk">
            <caption class="sr-only">Pendapatan Garis Kemiskinan (PGK) isi rumah mengikut negeri dan strata, 2022 (RM sebulan bagi isi rumah)</caption>
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
                <td>2,660</td>
                <td>2,342</td>
                <td>2,589</td>
                <td>1,197</td>
                <td>1,199</td>
                <td>1,198</td>
              </tr>
              <tr>
                <th scope="row">Johor</th>
                <td>2,665</td>
                <td>2,481</td>
                <td>2,627</td>
                <td>1,143</td>
                <td>1,232</td>
                <td>1,161</td>
              </tr>
              <tr>
                <th scope="row">Kedah</th>
                <td>2,303</td>
                <td>2,206</td>
                <td>2,271</td>
                <td>1,109</td>
                <td>1,253</td>
                <td>1,156</td>
              </tr>
              <tr>
                <th scope="row">Kelantan</th>
                <td>2,415</td>
                <td>2,199</td>
                <td>2,297</td>
                <td>1,193</td>
                <td>1,199</td>
                <td>1,196</td>
              </tr>
              <tr>
                <th scope="row">Melaka</th>
                <td>2,721</td>
                <td>2,122</td>
                <td>2,670</td>
                <td>1,251</td>
                <td>1,171</td>
                <td>1,244</td>
              </tr>
              <tr>
                <th scope="row">Negeri Sembilan</th>
                <td>2,617</td>
                <td>1,876</td>
                <td>2,402</td>
                <td>1,240</td>
                <td>1,006</td>
                <td>1,173</td>
              </tr>
              <tr>
                <th scope="row">Pahang</th>
                <td>2,675</td>
                <td>2,235</td>
                <td>2,480</td>
                <td>1,250</td>
                <td>1,145</td>
                <td>1,204</td>
              </tr>
              <tr>
                <th scope="row">Pulau Pinang</th>
                <td>2,258</td>
                <td>2,136</td>
                <td>2,250</td>
                <td>1,022</td>
                <td>1,214</td>
                <td>1,036</td>
              </tr>
              <tr>
                <th scope="row">Perak</th>
                <td>2,332</td>
                <td>2,199</td>
                <td>2,297</td>
                <td>1,066</td>
                <td>1,136</td>
                <td>1,084</td>
              </tr>
              <tr>
                <th scope="row">Perlis</th>
                <td>2,199</td>
                <td>2,069</td>
                <td>2,140</td>
                <td>1,145</td>
                <td>1,132</td>
                <td>1,139</td>
              </tr>
              <tr>
                <th scope="row">Selangor</th>
                <td>2,848</td>
                <td>2,421</td>
                <td>2,830</td>
                <td>1,274</td>
                <td>1,262</td>
                <td>1,274</td>
              </tr>
              <tr>
                <th scope="row">Terengganu</th>
                <td>2,799</td>
                <td>2,662</td>
                <td>2,751</td>
                <td>1,352</td>
                <td>1,394</td>
                <td>1,367</td>
              </tr>
              <tr class="is-sabah">
                <th scope="row">Sabah</th>
                <td>2,706</td>
                <td>2,795</td>
                <td>2,742</td>
                <td>1,180</td>
                <td>1,275</td>
                <td>1,218</td>
              </tr>
              <tr>
                <th scope="row">Sarawak</th>
                <td>2,860</td>
                <td>2,263</td>
                <td>2,618</td>
                <td>1,420</td>
                <td>1,119</td>
                <td>1,298</td>
              </tr>
              <tr>
                <th scope="row">W.P. Kuala Lumpur</th>
                <td>2,816</td>
                <td>&ndash;</td>
                <td>2,816</td>
                <td>1,109</td>
                <td>&ndash;</td>
                <td>1,109</td>
              </tr>
              <tr>
                <th scope="row">W.P. Labuan</th>
                <td>2,606</td>
                <td>2,196</td>
                <td>2,576</td>
                <td>1,265</td>
                <td>1,301</td>
                <td>1,268</td>
              </tr>
              <tr>
                <th scope="row">W.P. Putrajaya</th>
                <td>2,450</td>
                <td>&ndash;</td>
                <td>2,450</td>
                <td>1,206</td>
                <td>&ndash;</td>
                <td>1,206</td>
              </tr>
            </tbody>
          </table>
        </div>

        <h3 class="ek-subhead">
          PGK per kapita mengikut negeri dan strata, 2022
        </h3>
        <div class="ek-table-wrap">
          <table class="ek-table ek-pgk">
            <caption class="sr-only">Pendapatan Garis Kemiskinan (PGK) per kapita mengikut negeri dan strata, 2022 (RM seorang sebulan)</caption>
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
                <td>747</td>
                <td>602</td>
                <td>715</td>
                <td>328</td>
                <td>303</td>
                <td>322</td>
              </tr>
              <tr>
                <th scope="row">Johor</th>
                <td>758</td>
                <td>652</td>
                <td>735</td>
                <td>317</td>
                <td>318</td>
                <td>317</td>
              </tr>
              <tr>
                <th scope="row">Kedah</th>
                <td>649</td>
                <td>587</td>
                <td>629</td>
                <td>307</td>
                <td>330</td>
                <td>315</td>
              </tr>
              <tr>
                <th scope="row">Kelantan</th>
                <td>606</td>
                <td>532</td>
                <td>566</td>
                <td>292</td>
                <td>287</td>
                <td>289</td>
              </tr>
              <tr>
                <th scope="row">Melaka</th>
                <td>728</td>
                <td>560</td>
                <td>713</td>
                <td>326</td>
                <td>306</td>
                <td>325</td>
              </tr>
              <tr>
                <th scope="row">Negeri Sembilan</th>
                <td>739</td>
                <td>564</td>
                <td>689</td>
                <td>340</td>
                <td>297</td>
                <td>328</td>
              </tr>
              <tr>
                <th scope="row">Pahang</th>
                <td>750</td>
                <td>628</td>
                <td>696</td>
                <td>341</td>
                <td>316</td>
                <td>330</td>
              </tr>
              <tr>
                <th scope="row">Pulau Pinang</th>
                <td>719</td>
                <td>564</td>
                <td>708</td>
                <td>315</td>
                <td>314</td>
                <td>315</td>
              </tr>
              <tr>
                <th scope="row">Perak</th>
                <td>708</td>
                <td>623</td>
                <td>686</td>
                <td>312</td>
                <td>312</td>
                <td>312</td>
              </tr>
              <tr>
                <th scope="row">Perlis</th>
                <td>593</td>
                <td>569</td>
                <td>582</td>
                <td>299</td>
                <td>301</td>
                <td>300</td>
              </tr>
              <tr>
                <th scope="row">Selangor</th>
                <td>775</td>
                <td>592</td>
                <td>768</td>
                <td>345</td>
                <td>312</td>
                <td>343</td>
              </tr>
              <tr>
                <th scope="row">Terengganu</th>
                <td>631</td>
                <td>580</td>
                <td>613</td>
                <td>299</td>
                <td>301</td>
                <td>300</td>
              </tr>
              <tr class="is-sabah">
                <th scope="row">Sabah</th>
                <td>694</td>
                <td>636</td>
                <td>671</td>
                <td>291</td>
                <td>284</td>
                <td>288</td>
              </tr>
              <tr>
                <th scope="row">Sarawak</th>
                <td>751</td>
                <td>599</td>
                <td>689</td>
                <td>359</td>
                <td>287</td>
                <td>330</td>
              </tr>
              <tr>
                <th scope="row">W.P. Kuala Lumpur</th>
                <td>900</td>
                <td>&ndash;</td>
                <td>900</td>
                <td>347</td>
                <td>&ndash;</td>
                <td>347</td>
              </tr>
              <tr>
                <th scope="row">W.P. Labuan</th>
                <td>684</td>
                <td>527</td>
                <td>672</td>
                <td>320</td>
                <td>305</td>
                <td>318</td>
              </tr>
              <tr>
                <th scope="row">W.P. Putrajaya</th>
                <td>664</td>
                <td>&ndash;</td>
                <td>664</td>
                <td>318</td>
                <td>&ndash;</td>
                <td>318</td>
              </tr>
            </tbody>
          </table>
        </div>

        <p class="ek-source">
          Sumber jadual terperinci PGK 2022: Portal Pembasmian Kemiskinan (eKasih), seperti diterbitkan oleh Kementerian Kemajuan Desa dan Wilayah (KKDW).
          <a href="https://www.rurallink.gov.my/wp-content/uploads/2025/06/PGK.pdf">Lihat dokumen PGK 2022 (PDF)</a>.
          Statistik Daerah Ranau merujuk 30 September 2026; statistik nasional dan Sabah daripada ICU JPM merujuk 15 Januari 2026; rekod DUN merujuk Jun 2023.
        </p>
      </div>
    </section>

  @endverbatim
@endsection
