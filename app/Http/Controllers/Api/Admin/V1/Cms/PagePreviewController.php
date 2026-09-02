<?php

namespace App\Http\Controllers\Api\Admin\V1\Cms;

use App\Core\Cms\BlockSnapshotBuilder;
use App\Core\Cms\Models\Page;
use App\Core\Localization\LocaleRegistry;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PagePreviewController extends Controller
{
    public function __invoke(Request $request, string $page, string $locale, LocaleRegistry $locales, BlockSnapshotBuilder $blocks): Response
    {
        $metadata = $locales->get($locale);
        /** @var User $user */
        $user = $request->user();
        $query = Page::query()->where('public_id', $page);
        if (! $user->isGodfather()) {
            $query->where('business_id', $user->business_id);
        }
        $model = $query->with(['translations', 'blocks.translations'])->firstOrFail();
        $translation = $model->translations->firstWhere('locale', $locale);
        abort_if($translation === null, 404);
        $snapshotBlocks = $model->blocks->where('is_enabled', true)->map($blocks->build(...))->values();

        return response()->view('public.page', [
            'locale' => $locale,
            'localeMetadata' => $metadata,
            'locales' => $locales->all(),
            'localeRegistry' => $locales,
            'slug' => $model->slug,
            'translation' => $translation->only(['title', 'meta_title', 'meta_description', 'og_title', 'og_description']),
            'blocks' => $snapshotBlocks,
            'isPreview' => true,
        ])->header('Cache-Control', 'private, no-store');
    }
}
