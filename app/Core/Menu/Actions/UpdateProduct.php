<?php

namespace App\Core\Menu\Actions;

use App\Core\Audit\AuditRecorder;
use App\Core\Localization\LocaleRegistry;
use App\Core\Localization\TranslationState;
use App\Core\Media\Models\Media;
use App\Core\Menu\Models\MenuCategory;
use App\Core\Menu\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdateProduct
{
    public function __construct(private readonly LocaleRegistry $locales, private readonly AuditRecorder $audit) {}

    /**
     * @param  array<string, array<string, mixed>>  $translations
     * @param  array<string, bool>  $flags
     */
    public function execute(Product $product, MenuCategory $category, User $actor, string $slug, int $position, array $translations, array $flags, ?Media $primaryMedia): Product
    {
        if ($product->business_id !== $category->business_id || ($primaryMedia !== null && $primaryMedia->business_id !== $product->business_id)) {
            throw ValidationException::withMessages(['product' => ['Category and media must belong to the same business.']]);
        }
        if (Product::query()->where('business_id', $product->business_id)->where('slug', $slug)->whereKeyNot($product->id)->exists()) {
            throw ValidationException::withMessages(['slug' => ['The slug is already used by another product.']]);
        }

        return DB::transaction(function () use ($product, $category, $actor, $slug, $position, $translations, $flags, $primaryMedia): Product {
            $before = $product->load('translations')->toArray();
            $product->update([
                'category_id' => $category->id,
                'primary_media_id' => $primaryMedia?->id,
                'slug' => $slug,
                'position' => $position,
                'is_featured' => $flags['is_featured'] ?? false,
                'is_new' => $flags['is_new'] ?? false,
                'is_best_seller' => $flags['is_best_seller'] ?? false,
            ]);
            foreach ($this->locales->codes() as $locale) {
                $translation = $translations[$locale] ?? [];
                $product->translations()->updateOrCreate(['locale' => $locale], [
                    'name' => $translation['name'] ?? null,
                    'description' => $translation['description'] ?? null,
                    'ingredients' => $translation['ingredients'] ?? null,
                    'allergen_notice' => $translation['allergen_notice'] ?? null,
                    'translation_state' => $translation['translation_state'] ?? TranslationState::Draft,
                ]);
            }
            $this->audit->record('menu.product.updated', $actor, $product, $product->business_id, before: $before, after: $product->fresh('translations')->toArray());

            return $product->fresh(['translations', 'category.translations', 'branchSettings', 'primaryMedia']);
        });
    }
}
