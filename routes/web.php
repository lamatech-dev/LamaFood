<?php

use App\Core\Localization\LocaleRegistry;
use App\Http\Controllers\PublicAnalyticsViewController;
use App\Http\Controllers\PublicMenuController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\PublicQrRedirectController;
use App\Http\Controllers\PublicSitemapController;
use Illuminate\Support\Facades\Route;

Route::view('/admin/login', 'admin.login')->name('admin.login');
Route::view('/admin/forgot-password', 'admin.forgot-password')->name('password.request');
Route::get('/admin/reset-password/{token}', fn (string $token) => view('admin.reset-password', ['token' => $token]))->name('password.reset');
Route::view('/admin', 'admin.dashboard')->middleware('auth')->name('admin.dashboard');
Route::get('/q/{publicId}', PublicQrRedirectController::class)->name('public.qr.redirect');
Route::post('/analytics/views', PublicAnalyticsViewController::class)
    ->middleware('throttle:public-analytics')
    ->name('public.analytics.views');
Route::get('/sitemap.xml', PublicSitemapController::class)->name('public.sitemap');
Route::get('/robots.txt', fn () => response("User-agent: *\nAllow: /\nDisallow: /admin\nSitemap: ".route('public.sitemap')."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']));
Route::view('/offline', 'public.offline')->name('public.offline');

Route::middleware('locale')->group(function (): void {
    Route::get('/', fn (PublicPageController $controller) => $controller('fa', 'home', app(LocaleRegistry::class)))->defaults('locale', 'fa')->name('public.home');
    Route::get('/menu', PublicMenuController::class)->defaults('locale', 'fa')->name('public.menu');
    Route::get('/{slug}', PublicPageController::class)->defaults('locale', 'fa')->whereIn('slug', ['about', 'contact', 'privacy'])->name('public.page');
});

Route::prefix('{locale}')->whereIn('locale', ['en', 'ar'])->middleware('locale')->group(function (): void {
    Route::get('/', fn (string $locale, PublicPageController $controller) => $controller($locale, 'home', app(LocaleRegistry::class)))->name('public.home.localized');
    Route::get('/menu', PublicMenuController::class)->name('public.menu.localized');
    Route::get('/{slug}', PublicPageController::class)->whereIn('slug', ['about', 'contact', 'privacy'])->name('public.page.localized');
});

Route::get('/fa/{path?}', function (?string $path = null) {
    $target = '/'.ltrim((string) $path, '/');
    $query = request()->getQueryString();

    return redirect($target.($query ? '?'.$query : ''), 301);
})->where('path', 'menu|about|contact|privacy')->name('public.fa-legacy');
