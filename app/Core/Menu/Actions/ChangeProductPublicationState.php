<?php

namespace App\Core\Menu\Actions;

use App\Core\Audit\AuditRecorder;
use App\Core\Localization\LocaleRegistry;
use App\Core\Localization\TranslationState;
use App\Core\Menu\Models\Product;
use App\Core\Menu\PublicationState;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ChangeProductPublicationState
{
    public function __construct(private readonly LocaleRegistry $locales, private readonly AuditRecorder $audit) {}

    public function execute(Product $product, User $actor, PublicationState $state): Product
    {
        if ($state === PublicationState::Published) {
            $product->loadMissing('translations');
            foreach ($this->locales->codes() as $locale) {
                $translation = $product->translations->firstWhere('locale', $locale);
                if ($translation === null || $translation->translation_state !== TranslationState::Ready || blank($translation->name)) {
                    throw ValidationException::withMessages(["translations.{$locale}" => ['A ready product name is required before publication.']]);
                }
            }
        }

        $before = $product->publication_state;
        $product->update(['publication_state' => $state]);
        $this->audit->record('menu.product.publication_changed', $actor, $product, $product->business_id, before: ['publication_state' => $before], after: ['publication_state' => $state]);

        return $product->fresh(['translations', 'branchSettings']);
    }
}
