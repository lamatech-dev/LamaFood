<?php

namespace App\Http\Controllers;

use App\Core\Business\Models\Branch;
use App\Core\Localization\LocaleRegistry;
use App\Core\Localization\TranslationState;
use App\Core\Menu\Models\MenuCategory;
use App\Core\Menu\PublicationState;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class PublicMenuController extends Controller
{
    public function __invoke(string $locale, LocaleRegistry $locales): View
    {
        $metadata = $locales->get($locale);
        $branch = Branch::query()
            ->whereHas('business', fn (Builder $query): Builder => $query->where('slug', config('denardi.business_slug'))->where('is_active', true))
            ->where('is_default', true)
            ->where('is_active', true)
            ->firstOrFail();
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
                        'primaryMedia',
                    ])->orderBy('position'),
            ])
            ->orderBy('position')
            ->get();

        return view('public.menu', [
            'locale' => $locale,
            'localeMetadata' => $metadata,
            'locales' => $locales->all(),
            'categories' => $categories,
        ]);
    }
}
