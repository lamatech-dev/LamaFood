<?php

namespace App\Http\Controllers;

use App\Core\Analytics\Actions\RecordAnalyticsEvent;
use App\Core\Analytics\AnalyticsEventType;
use App\Core\Analytics\VisitorIdentity;
use App\Core\Business\Models\Branch;
use App\Core\Localization\LocaleRegistry;
use App\Core\Localization\TranslationState;
use App\Core\Menu\Models\MenuCategory;
use App\Core\Menu\PublicationState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PublicMenuController extends Controller
{
    public function __invoke(Request $request, string $locale, LocaleRegistry $locales, VisitorIdentity $visitors, RecordAnalyticsEvent $record): Response
    {
        $metadata = $locales->get($locale);
        $branchQuery = Branch::query()
            ->with('business')
            ->whereHas('business', fn (Builder $query): Builder => $query->where('slug', config('denardi.business_slug'))->where('is_active', true))
            ->where('is_active', true);
        if ($request->filled('branch')) {
            $branchQuery->where('slug', $request->string('branch')->toString());
        } else {
            $branchQuery->where('is_default', true);
        }
        $branch = $branchQuery->firstOrFail();
        $categories = MenuCategory::query()
            ->where('business_id', $branch->business_id)
            ->where('publication_state', PublicationState::Published)
            ->whereHas('translations', fn (Builder $query): Builder => $query->where('locale', $locale)->where('translation_state', TranslationState::Ready)->whereNotNull('name'))
            ->with([
                'translations' => fn ($query) => $query->where('locale', $locale),
                'products' => fn ($query) => $query
                    ->where('publication_state', PublicationState::Published)
                    ->whereHas('translations', fn (Builder $translation): Builder => $translation->where('locale', $locale)->where('translation_state', TranslationState::Ready)->whereNotNull('name'))
                    ->whereHas('branchSettings', fn (Builder $setting): Builder => $setting->where('branch_id', $branch->id))
                    ->with([
                        'translations' => fn ($translation) => $translation->where('locale', $locale),
                        'branchSettings' => fn ($setting) => $setting->where('branch_id', $branch->id),
                        'primaryMedia.translations' => fn ($translation) => $translation->where('locale', $locale),
                    ])->orderBy('position'),
            ])
            ->orderBy('position')
            ->get();

        $identifier = $visitors->identifier($request);
        if (! $visitors->isBot($request)) {
            $record->execute($branch->business, AnalyticsEventType::MenuView, $visitors->hash($identifier), $visitors->deviceClass($request), $locale, subjectType: 'menu');
        }

        return response()->view('public.menu', [
            'locale' => $locale,
            'localeMetadata' => $metadata,
            'locales' => $locales->all(),
            'categories' => $categories,
            'menuQuery' => array_filter(['branch' => $request->query('branch'), 'table' => $request->query('table')]),
        ])->withCookie($visitors->cookie($identifier));
    }
}
