<?php

namespace App\Core\Cms\Actions;

use App\Core\Audit\AuditRecorder;
use App\Core\Business\Models\Business;
use App\Core\Cms\Models\Page;
use App\Core\Localization\LocaleRegistry;
use App\Core\Localization\TranslationState;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreatePage
{
    public function __construct(
        private readonly LocaleRegistry $locales,
        private readonly AuditRecorder $audit,
    ) {}

    /** @param array<string, array<string, mixed>> $translations */
    public function execute(Business $business, User $actor, string $slug, string $template, array $translations): Page
    {
        return DB::transaction(function () use ($business, $actor, $slug, $template, $translations): Page {
            $page = Page::query()->create([
                'public_id' => (string) Str::ulid(),
                'business_id' => $business->id,
                'slug' => $slug,
                'template' => $template,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            foreach ($this->locales->codes() as $locale) {
                $translation = $translations[$locale] ?? [];
                $page->translations()->create([
                    'locale' => $locale,
                    'title' => $translation['title'] ?? null,
                    'meta_title' => $translation['meta_title'] ?? null,
                    'meta_description' => $translation['meta_description'] ?? null,
                    'og_title' => $translation['og_title'] ?? null,
                    'og_description' => $translation['og_description'] ?? null,
                    'translation_state' => $translation['translation_state'] ?? TranslationState::Draft,
                ]);
            }

            $this->audit->record('cms.page.created', $actor, $page, $business->id, after: $page->fresh()->toArray());

            return $page->load('translations');
        });
    }
}
