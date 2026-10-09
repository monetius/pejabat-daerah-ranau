<?php

use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\SitePartController;
use App\Http\Controllers\SiteController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

// ---------- Public: announcements ----------
Route::get('/hebahan', [SiteController::class, 'announcements'])->name('announcements.index');
Route::get('/hebahan/{slug}', [SiteController::class, 'announcement'])->name('announcements.show');

// ---------- Admin ----------
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware(['auth', EnsureAdmin::class])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('announcements', AdminAnnouncementController::class)->except('show');

        Route::match(['post', 'put'], '/pages/preview', [AdminPageController::class, 'preview'])->name('pages.preview');
        Route::post('/pages/{page}/revisions/{revision}/restore', [AdminPageController::class, 'restore'])->name('pages.restore');
        Route::resource('pages', AdminPageController::class)->except('show');

        Route::get('/parts', [SitePartController::class, 'index'])->name('parts.index');
        Route::get('/parts/{part}/edit', [SitePartController::class, 'edit'])->name('parts.edit');
        Route::put('/parts/{part}', [SitePartController::class, 'update'])->name('parts.update');

        Route::get('/media', [MediaController::class, 'index'])->name('media.index');
        Route::post('/media', [MediaController::class, 'store'])->name('media.store');
        Route::delete('/media', [MediaController::class, 'destroy'])->name('media.destroy');
    });
});

// Laravel's `auth` middleware redirects to a route named "login".
Route::redirect('/login', '/admin/login')->name('login');

// ---------- Public: every CMS page (keep these LAST) ----------
Route::get('/', [SiteController::class, 'show'])->name('home');
Route::get('/{path}', [SiteController::class, 'show'])->where('path', '.+')->name('page');
