<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'site.home');

Route::view('/info-korporat/privacypolicy', 'site.info-korporat.privacypolicy');
Route::view('/info-korporat/securitypolicy', 'site.info-korporat.securitypolicy');
Route::view('/info-korporat/notice', 'site.info-korporat.notice');