<?php

use App\Core\Localization\LocaleRegistry;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\PublicMenuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
