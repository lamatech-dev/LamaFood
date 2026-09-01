<?php

namespace App\Core\Menu\Actions;

use App\Core\Audit\AuditRecorder;
use App\Core\Localization\LocaleRegistry;
use App\Core\Localization\TranslationState;
use App\Core\Menu\Models\MenuCategory;
use App\Core\Menu\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateProduct
{
    public function __construct(private readonly LocaleRegistry $locales, private readonly AuditRecorder $audit) {}

    /**
     * @param  array<string, array<string, mixed>>  $translations
     * @param  array<string, bool>  $flags
     */
    public function execute(MenuCategory $category, User $actor, string $slug, int $position, array $translations, array $flags = []): Product
    {
        return DB::transaction(function () use ($category, $actor, $slug, $position, $translations, $flags): Product {
            $product = Product::query()->create([
                'public_id' => (string) Str::ulid(),
                'business_id' => $category->business_id,
                'category_id' => $category->id,
                'slug' => $slug,
                'position' => $position,
                'is_featured' => $flags['is_featured'] ?? false,
                'is_new' => $flags['is_new'] ?? false,
                'is_best_seller' => $flags['is_best_seller'] ?? false,
            ]);
            foreach ($this->locales->codes() as $locale) {
                $translation = $translations[$locale] ?? [];
                $product->translations()->create([
                    'locale' => $locale,
                    'name' => $translation['name'] ?? null,
                    'description' => $translation['description'] ?? null,
                    'ingredients' => $translation['ingredients'] ?? null,
                    'allergen_notice' => $translation['allergen_notice'] ?? null,
                    'translation_state' => $translation['translation_state'] ?? TranslationState::Draft,
                ]);
            }
            $this->audit->record('menu.product.created', $actor, $product, $category->business_id, after: $product->toArray());

            return $product->load(['translations', 'category']);
        });
    }
}
