<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'site.home');

// hubungi-kami
Route::view('/hubungi-kami/alamat', 'site.hubungi-kami.alamat');

// info-korporat
Route::view('/info-korporat/carta-organisasi', 'site.info-korporat.carta-organisasi');
Route::view('/info-korporat/hebahan-integriti', 'site.info-korporat.hebahan-integriti');
Route::view('/info-korporat/mengenai-kami', 'site.info-korporat.mengenai-kami');




// profil-daerah
Route::view('/profil-daerah/data-penduduk', 'site.profil-daerah.data-penduduk');
Route::view('/profil-daerah/ekasih', 'site.profil-daerah.ekasih');
Route::view('/profil-daerah/kemudahan-awam', 'site.profil-daerah.kemudahan-awam');
Route::view('/profil-daerah/pelancongan', 'site.profil-daerah.pelancongan');
Route::view('/profil-daerah/senarai-kampung', 'site.profil-daerah.senarai-kampung');
Route::view('/profil-daerah/wakil-rakyat', 'site.profil-daerah.wakil-rakyat');

// perkhidmatan-online
Route::view('/perkhidmatan-online/latihan-industri', 'site.perkhidmatan-online.latihan-industri');
Route::view('/perkhidmatan-online/lesen-berniaga', 'site.perkhidmatan-online.lesen-berniaga');

// galeri-pautan
Route::view('/galeri-pautan/agensi', 'site.galeri-pautan.agensi');
Route::view('/galeri-pautan/gambar', 'site.galeri-pautan.gambar');
Route::view('/galeri-pautan/intranet', 'site.galeri-pautan.intranet');
Route::view('/galeri-pautan/video', 'site.galeri-pautan.video');


// policies
Route::view('/info-korporat/notice', 'site.info-korporat.notice');
Route::view('/info-korporat/privacypolicy', 'site.info-korporat.privacypolicy');
Route::view('/info-korporat/securitypolicy', 'site.info-korporat.securitypolicy');