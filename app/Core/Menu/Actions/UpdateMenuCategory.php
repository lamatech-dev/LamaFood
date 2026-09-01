<?php

namespace App\Core\Menu\Actions;

use App\Core\Audit\AuditRecorder;
use App\Core\Localization\LocaleRegistry;
use App\Core\Localization\TranslationState;
use App\Core\Menu\Models\MenuCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdateMenuCategory
{
    public function __construct(private readonly LocaleRegistry $locales, private readonly AuditRecorder $audit) {}

    /** @param array<string, array<string, mixed>> $translations */
    public function execute(MenuCategory $category, User $actor, string $slug, int $position, array $translations, ?MenuCategory $parent, bool $isFeatured): MenuCategory
    {
        if ($parent !== null && ($parent->business_id !== $category->business_id || $parent->id === $category->id || $parent->parent_id === $category->id)) {
            throw ValidationException::withMessages(['parent_id' => ['The selected parent category is invalid.']]);
        }
        if (MenuCategory::query()->where('business_id', $category->business_id)->where('slug', $slug)->whereKeyNot($category->id)->exists()) {
            throw ValidationException::withMessages(['slug' => ['The slug is already used by another category.']]);
        }

        return DB::transaction(function () use ($category, $actor, $slug, $position, $translations, $parent, $isFeatured): MenuCategory {
            $before = $category->load('translations')->toArray();
            $category->update(['slug' => $slug, 'position' => $position, 'parent_id' => $parent?->id, 'is_featured' => $isFeatured]);
            foreach ($this->locales->codes() as $locale) {
                $translation = $translations[$locale] ?? [];
                $category->translations()->updateOrCreate(['locale' => $locale], [
                    'name' => $translation['name'] ?? null,
                    'description' => $translation['description'] ?? null,
                    'seo_title' => $translation['seo_title'] ?? null,
                    'seo_description' => $translation['seo_description'] ?? null,
                    'translation_state' => $translation['translation_state'] ?? TranslationState::Draft,
                ]);
            }
            $this->audit->record('menu.category.updated', $actor, $category, $category->business_id, before: $before, after: $category->fresh('translations')->toArray());

            return $category->fresh(['translations', 'parent']);
        });
    }
}
