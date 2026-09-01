<?php

namespace App\Core\Cms;

use App\Core\Cms\Models\Page;
use App\Core\Localization\LocaleRegistry;
use App\Core\Localization\TranslationState;

class PageReadiness
{
    public function __construct(
        private readonly LocaleRegistry $locales,
        private readonly BlockPayloadValidator $validator,
    ) {}

    /** @return array{ready: bool, locales: array<string, array{ready: bool, issues: list<string>}>} */
    public function report(Page $page): array
    {
        $page->loadMissing(['translations', 'blocks.translations']);
        $report = [];

        foreach ($this->locales->codes() as $locale) {
            $issues = [];
            $pageTranslation = $page->translations->firstWhere('locale', $locale);
            if ($pageTranslation === null || $pageTranslation->translation_state !== TranslationState::Ready || blank($pageTranslation->title)) {
                $issues[] = 'page_translation_incomplete';
            }

            foreach ($page->blocks->where('is_enabled', true) as $block) {
                $translation = $block->translations->firstWhere('locale', $locale);
                if ($translation === null || $translation->translation_state !== TranslationState::Ready) {
                    $issues[] = "block_{$block->public_id}_translation_incomplete";
                    continue;
                }
                try {
                    $this->validator->validateContent($block->type, $translation->content_json, true);
                } catch (\Throwable) {
                    $issues[] = "block_{$block->public_id}_translation_invalid";
                }
            }
            $report[$locale] = ['ready' => $issues === [], 'issues' => $issues];
        }

        return ['ready' => collect($report)->every(fn (array $locale): bool => $locale['ready']), 'locales' => $report];
    }
}
