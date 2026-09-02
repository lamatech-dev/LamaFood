<?php

namespace App\Providers;

use App\Core\Analytics\VisitorIdentity;
use App\Core\Backup\Contracts\DatabaseDumper;
use App\Core\Backup\MySqlDatabaseDumper;
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
        $this->app->bind(DatabaseDumper::class, MySqlDatabaseDumper::class);
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

        RateLimiter::for('password-reset', static function (Request $request): Limit {
            $email = Str::lower($request->string('email')->toString());

            return Limit::perMinute(3)->by(Str::transliterate($email.'|'.$request->ip()));
        });

        RateLimiter::for('password-reset-confirm', static function (Request $request): Limit {
            $email = Str::lower($request->string('email')->toString());

            return Limit::perMinute(5)->by(Str::transliterate($email.'|'.$request->ip()));
        });

        RateLimiter::for('public-analytics', static function (Request $request): Limit {
            $visitor = (string) $request->cookie(VisitorIdentity::CookieName, 'anonymous');
            $key = hash_hmac('sha256', $visitor.'|'.$request->ip(), (string) config('app.key'));

            return Limit::perMinute(120)->by($key);
        });

        Route::pattern('locale', app(LocaleRegistry::class)->routePattern());
    }
}
