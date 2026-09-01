<?php

use App\Core\Localization\LocaleRegistry;
use App\Http\Controllers\PublicMenuController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\PublicQrRedirectController;
use App\Http\Controllers\PublicSitemapController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/admin/login', 'admin.login')->name('admin.login');
Route::view('/admin', 'admin.dashboard')->name('admin.dashboard');
Route::get('/q/{publicId}', PublicQrRedirectController::class)->name('public.qr.redirect');
Route::get('/sitemap.xml', PublicSitemapController::class)->name('public.sitemap');
Route::get('/robots.txt', fn () => response("User-agent: *\nAllow: /\nDisallow: /admin\nSitemap: ".route('public.sitemap')."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']));
Route::view('/offline', 'public.offline')->name('public.offline');

Route::get('/', function (Request $request, LocaleRegistry $locales) {
    $supported = $locales->codes();
    $preferred = $request->cookie('denardi_locale');

    if (! in_array($preferred, $supported, true)) {
        $preferred = $request->getPreferredLanguage($supported) ?: $locales->default();
    }

    return redirect('/'.$preferred);
});

Route::prefix('{locale}')->middleware('locale')->group(function (): void {
    Route::get('/', fn (string $locale, PublicPageController $controller) => $controller($locale, 'home', app(LocaleRegistry::class)))->name('public.home');
    Route::get('/menu', PublicMenuController::class)->name('public.menu');
    Route::get('/{slug}', PublicPageController::class)->whereIn('slug', ['about', 'contact', 'privacy'])->name('public.page');
});
