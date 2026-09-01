<?php

namespace App\Http\Controllers;

use App\Core\Cms\Models\Page;
use App\Core\Cms\PageStatus;
use App\Core\Localization\LocaleRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;

class PublicSitemapController extends Controller
{
    public function __invoke(LocaleRegistry $locales): Response
    {
        $routableSlugs = ['home', 'about', 'contact', 'privacy'];
        $pages = Page::query()
            ->whereHas('business', fn (Builder $query): Builder => $query->where('slug', config('denardi.business_slug'))->where('is_active', true))
            ->where('status', PageStatus::Published)
            ->whereIn('slug', $routableSlugs)
            ->with('publishedRevision')
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (Page $page): bool => $page->publishedRevision !== null)
            ->values();

        return response()->view('public.sitemap', [
            'locales' => $locales->codes(),
            'pages' => $pages,
        ])->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
