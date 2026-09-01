<?php

namespace App\Core\Cms\Actions;

use App\Core\Audit\AuditRecorder;
use App\Core\Cms\BlockPayloadValidator;
use App\Core\Cms\Models\Block;
use App\Core\Localization\LocaleRegistry;
use App\Core\Localization\TranslationState;
use App\Core\Media\SyncBlockMediaUsages;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UpdateBlock
{
    public function __construct(
        private readonly LocaleRegistry $locales,
        private readonly BlockPayloadValidator $validator,
        private readonly AuditRecorder $audit,
        private readonly SyncBlockMediaUsages $mediaUsages,
    ) {}

    /**
     * @param  array<string, mixed>  $structure
     * @param  array<string, array<string, mixed>>  $translations
     */
    public function execute(Block $block, User $actor, string $type, bool $isEnabled, array $structure, array $translations): Block
    {
        $this->validator->validateStructure($type, $structure);
        foreach ($translations as $translation) {
            $this->validator->validateContent($type, (array) ($translation['content'] ?? []), ($translation['translation_state'] ?? 'draft') === TranslationState::Ready->value);
        }

        return DB::transaction(function () use ($block, $actor, $type, $isEnabled, $structure, $translations): Block {
            $before = $block->load('translations')->toArray();
            $block->update(['type' => $type, 'is_enabled' => $isEnabled, 'structure_json' => $structure, 'updated_by' => $actor->id]);
            foreach ($this->locales->codes() as $locale) {
                $translation = $translations[$locale] ?? [];
                $state = TranslationState::tryFrom((string) ($translation['translation_state'] ?? 'draft')) ?? TranslationState::Draft;
                $block->translations()->updateOrCreate(['locale' => $locale], [
                    'content_json' => $translation['content'] ?? [],
                    'translation_state' => $state,
                    'validated_at' => $state === TranslationState::Ready ? now() : null,
                ]);
            }
            $this->mediaUsages->execute($block);
            $block->page()->increment('revision');
            $this->audit->record('cms.block.updated', $actor, $block, $block->page->business_id, before: $before, after: $block->fresh('translations')->toArray());

            return $block->fresh('translations');
        });
    }
}
