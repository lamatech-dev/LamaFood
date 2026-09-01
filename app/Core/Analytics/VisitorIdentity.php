<?php

namespace App\Core\Analytics;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;

class VisitorIdentity
{
    public const CookieName = 'denardi_visitor';

    public function identifier(Request $request): string
    {
        $identifier = (string) $request->cookie(self::CookieName, '');

        return preg_match('/\A[a-zA-Z0-9]{40}\z/', $identifier) === 1 ? $identifier : Str::random(40);
    }

    public function hash(string $identifier): string
    {
        return hash_hmac('sha256', $identifier, (string) config('app.key'));
    }

    public function cookie(string $identifier): Cookie
    {
        return cookie(self::CookieName, $identifier, 60 * 24 * 365, '/', null, request()->isSecure(), true, false, 'lax');
    }

    public function isBot(Request $request): bool
    {
        return preg_match('/bot|crawler|spider|slurp|preview|headless|monitor/i', (string) $request->userAgent()) === 1;
    }

    public function deviceClass(Request $request): string
    {
        $agent = (string) $request->userAgent();

        return match (true) {
            preg_match('/ipad|tablet/i', $agent) === 1 => 'tablet',
            preg_match('/mobile|iphone|android/i', $agent) === 1 => 'mobile',
            default => 'desktop',
        };
    }
}
