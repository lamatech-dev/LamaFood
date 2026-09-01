<?php

namespace App\Http\Middleware;

use App\Core\Localization\LocaleRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPublicLocale
{
    public function __construct(private readonly LocaleRegistry $locales) {}

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = (string) $request->route('locale');

        abort_unless(in_array($locale, $this->locales->codes(), true), 404);

        app()->setLocale($locale);
        $request->attributes->set('text_direction', $this->locales->get($locale)['direction']);

        return $next($request);
    }
}
