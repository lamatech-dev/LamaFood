<?php

namespace App\Http\Controllers;

use App\Core\Cms\Models\Page;
use App\Core\Cms\PageStatus;
use App\Core\Localization\LocaleRegistry;
use App\Core\Seo\BusinessStructuredData;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class PublicPageController extends Controller
{
    public function __construct(private readonly BusinessStructuredData $structuredData) {}

    public function __invoke(string $locale, string $slug, LocaleRegistry $locales): View
    {
        $metadata = $locales->get($locale);
        $page = Page::query()
            ->whereHas('business', fn (Builder $query): Builder => $query->where('slug', config('denardi.business_slug'))->where('is_active', true))
            ->where('slug', $slug)
            ->where('status', PageStatus::Published)
            ->with(['business', 'publishedRevision'])
            ->firstOrFail();
        $snapshot = $page->publishedRevision?->snapshot_json;
        abort_if(! is_array($snapshot), 404);
        $translation = Arr::get($snapshot, "translations.{$locale}");
        abort_if(! is_array($translation), 404);
        $snapshotBlocks = $snapshot['blocks'] ?? [];
        $snapshotBlocks = is_array($snapshotBlocks) ? array_values($snapshotBlocks) : [];
        /** @var list<mixed> $snapshotBlocks */

        return view('public.page', [
            'locale' => $locale,
            'localeMetadata' => $metadata,
            'locales' => $locales->all(),
            'localeRegistry' => $locales,
            'slug' => $slug,
            'structuredData' => $this->structuredData->forBusiness($page->business, $locale),
            'translation' => $translation,
            'blocks' => collect($snapshotBlocks)->filter(
                fn (mixed $block): bool => is_array($block) && is_array(Arr::get($block, "translations.{$locale}.content_json")),
            )->values(),
        ]);
    }
}
