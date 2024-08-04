<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\UrlsController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/google', [GoogleAuthController::class, 'googleConnectorProfilePage'])->name('gsc');

    //-- Google Search Console
    Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
    Route::post('/auth/google/disconnect', [GoogleAuthController::class, 'disconnect'])->name('auth.google.disconnect');

    Route::post('/auth/google/sync', [GoogleAuthController::class, 'syncSitemaps'])->name('sitemaps.sync');
    Route::post('/auth/google/resync', [GoogleAuthController::class, 'resyncSitemaps'])->name('sitemaps.resync');

    //-- Sitemap Controlls
    Route::post('sitemaps/{sitemap}/queue', [QueueController::class, 'queueSitemap'])->name('sitemap.queue');
    Route::get('/fetch-urls', [UrlsController::class, 'getUrlsByDomain']);
    Route::post('sitemaps/{sitemap}/toggle-auto-scan', [QueueController::class, 'toggleAutoScan'])->name('sitemaps.toggleAutoScan');

    //-- Navigational pages
    Route::get('/dashboard', [UrlsController::class, 'index'])->name('dashboard');
});

require __DIR__ . '/auth.php';
