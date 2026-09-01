<?php

namespace App\Core\Menu\Actions;

use App\Core\Audit\AuditRecorder;
use App\Core\Business\Models\Business;
use App\Core\Localization\LocaleRegistry;
use App\Core\Localization\TranslationState;
use App\Core\Menu\Models\MenuCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateMenuCategory
{
    public function __construct(private readonly LocaleRegistry $locales, private readonly AuditRecorder $audit) {}

    /** @param array<string, array<string, mixed>> $translations */
    public function execute(Business $business, User $actor, string $slug, int $position, array $translations): MenuCategory
    {
        return DB::transaction(function () use ($business, $actor, $slug, $position, $translations): MenuCategory {
            $category = MenuCategory::query()->create([
                'public_id' => (string) Str::ulid(),
                'business_id' => $business->id,
                'slug' => $slug,
                'position' => $position,
            ]);
            foreach ($this->locales->codes() as $locale) {
                $translation = $translations[$locale] ?? [];
                $category->translations()->create([
                    'locale' => $locale,
                    'name' => $translation['name'] ?? null,
                    'description' => $translation['description'] ?? null,
                    'seo_title' => $translation['seo_title'] ?? null,
                    'seo_description' => $translation['seo_description'] ?? null,
                    'translation_state' => $translation['translation_state'] ?? TranslationState::Draft,
                ]);
            }
            $this->audit->record('menu.category.created', $actor, $category, $business->id, after: $category->toArray());

            return $category->load('translations');
        });
    }
}
