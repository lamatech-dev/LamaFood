<?php

namespace App\Core\Menu\Actions;

use App\Core\Audit\AuditRecorder;
use App\Core\Localization\LocaleRegistry;
use App\Core\Localization\TranslationState;
use App\Core\Menu\Models\MenuCategory;
use App\Core\Menu\PublicationState;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ChangeCategoryPublicationState
{
    public function __construct(private readonly LocaleRegistry $locales, private readonly AuditRecorder $audit) {}

    public function execute(MenuCategory $category, User $actor, PublicationState $state): MenuCategory
    {
        if ($state === PublicationState::Published) {
            $category->loadMissing('translations');
            foreach ($this->locales->codes() as $locale) {
                $translation = $category->translations->firstWhere('locale', $locale);
                if ($translation === null || $translation->translation_state !== TranslationState::Ready || blank($translation->name)) {
                    throw ValidationException::withMessages(["translations.{$locale}" => ['A ready category name is required before publication.']]);
                }
            }
        }

        $before = $category->publication_state;
        $category->update(['publication_state' => $state]);
        $this->audit->record('menu.category.publication_changed', $actor, $category, $category->business_id, before: ['publication_state' => $before], after: ['publication_state' => $state]);

        return $category->fresh('translations');
    }
}
