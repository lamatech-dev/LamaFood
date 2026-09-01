<?php

namespace App\Providers;

use App\Core\Localization\LocaleRegistry;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(static fn (User $user): ?bool => $user->isGodfather() ? true : null);

        RateLimiter::for('login', static function (Request $request): Limit {
            $identifier = Str::lower($request->string('identifier')->toString());

            return Limit::perMinute(5)->by(Str::transliterate($identifier.'|'.$request->ip()));
        });

        Route::pattern('locale', app(LocaleRegistry::class)->routePattern());
    }
}
