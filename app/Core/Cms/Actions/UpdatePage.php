<?php

namespace App\Core\Cms\Actions;

use App\Core\Audit\AuditRecorder;
use App\Core\Cms\Models\Page;
use App\Core\Cms\PageStatus;
use App\Core\Localization\LocaleRegistry;
use App\Core\Localization\TranslationState;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdatePage
{
    public function __construct(private readonly LocaleRegistry $locales, private readonly AuditRecorder $audit) {}

    /** @param array<string, array<string, mixed>> $translations */
    public function execute(Page $page, User $actor, string $slug, string $template, array $translations): Page
    {
        if ($page->status === PageStatus::Archived) {
            throw ValidationException::withMessages(['page' => ['An archived page cannot be edited.']]);
        }
        if (Page::query()->where('business_id', $page->business_id)->where('slug', $slug)->whereKeyNot($page->id)->exists()) {
            throw ValidationException::withMessages(['slug' => ['The slug is already used by another page.']]);
        }

        return DB::transaction(function () use ($page, $actor, $slug, $template, $translations): Page {
            $before = $page->load('translations')->toArray();
            $page->update(['slug' => $slug, 'template' => $template, 'updated_by' => $actor->id]);
            foreach ($this->locales->codes() as $locale) {
                $translation = $translations[$locale] ?? [];
                $page->translations()->updateOrCreate(['locale' => $locale], [
                    'title' => $translation['title'] ?? null,
                    'meta_title' => $translation['meta_title'] ?? null,
                    'meta_description' => $translation['meta_description'] ?? null,
                    'og_title' => $translation['og_title'] ?? null,
                    'og_description' => $translation['og_description'] ?? null,
                    'translation_state' => $translation['translation_state'] ?? TranslationState::Draft,
                ]);
            }
            $page->increment('revision');
            $this->audit->record('cms.page.updated', $actor, $page, $page->business_id, before: $before, after: $page->fresh('translations')->toArray());

            return $page->fresh(['translations', 'blocks.translations', 'publishedRevision']);
        });
    }
}
