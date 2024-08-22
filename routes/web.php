<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\UrlsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\MailerController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/submit-bug-report', [MailerController::class, 'submitBugReport'])->name('submit.bug.report');


Route::get('/privacy-policy', function () {
    return view('privacy');
});

Route::get('/terms', function () {
    return view('terms');
});

Route::get('/about', function () {
    return view('about');
});

Route::get('/accessibility', function () {
    return view('accessibility');
});

Route::get('/contact', function () {
    return view('contact');
});
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');



Route::middleware('auth', 'checkSuspended')->group(function () {
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


    //-- Indexing
    Route::post('/dashboard/indexing', [GoogleAuthController::class, 'submitIndexList'])->name('submit.indexing');

    //-- Navigational pages
    Route::get('/dashboard', [UrlsController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'checkSuspended', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/admin/sitemaps', [AdminController::class, 'sitemaps'])->name('admin.sitemaps');
    Route::get('/admin/urls', [AdminController::class, 'urls'])->name('admin.urls');

    Route::get('/admin/users/search', [AdminController::class, 'searchUsers'])->name('search.users');
    Route::post('/admin/users/suspend', [AdminController::class, 'toggleSuspend'])->name('toggle.suspend');
    Route::post('/admin/users/reset-password', [AdminController::class, 'resetPassword'])->name('reset.password');
    Route::delete('/admin/users/{user}', [AdminController::class, 'deleteUser'])->name('delete.user');

    Route::post('/admin/sitemaps/toggle-auto-scan', [AdminController::class, 'toggleAutoScan'])->name('toggle.auto.scan');
    Route::get('/admin/sitemaps/search', [AdminController::class, 'searchSitemaps'])->name('admin.search.sitemaps');
    Route::post('/admin/sitemaps/{sitemap}/queue', [QueueController::class, 'queueSitemap'])->name('admin.sitemap.queue');


    Route::get('/admin/workers', [AdminController::class, 'GoogleServiceWorkers'])->name('service.workers');
    Route::post('/admin/workers', [AdminController::class, 'AddGoogleServiceWorkers'])->name('add.service.workers');
});

//-- tests
//Route::get('/send-test-email', 'App\Http\Controllers\TestEmailController@sendTestEmail');


require __DIR__ . '/auth.php';
