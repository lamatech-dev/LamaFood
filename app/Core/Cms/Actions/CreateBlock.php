<?php

namespace App\Core\Cms\Actions;

use App\Core\Audit\AuditRecorder;
use App\Core\Cms\BlockPayloadValidator;
use App\Core\Cms\Models\Block;
use App\Core\Cms\Models\Page;
use App\Core\Localization\LocaleRegistry;
use App\Core\Localization\TranslationState;
use App\Models\User;
use App\Core\Media\SyncBlockMediaUsages;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateBlock
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
    public function execute(Page $page, User $actor, string $type, int $position, array $structure, array $translations): Block
    {
        $this->validator->validateStructure($type, $structure);

        foreach ($translations as $translation) {
            $this->validator->validateContent(
                $type,
                (array) ($translation['content'] ?? []),
                ($translation['translation_state'] ?? 'draft') === TranslationState::Ready->value,
            );
        }

        return DB::transaction(function () use ($page, $actor, $type, $position, $structure, $translations): Block {
            $block = $page->blocks()->create([
                'public_id' => (string) Str::ulid(),
                'type' => $type,
                'position' => $position,
                'structure_json' => $structure,
                'schema_version' => 1,
                'is_enabled' => true,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            foreach ($this->locales->codes() as $locale) {
                $translation = $translations[$locale] ?? [];
                $state = TranslationState::tryFrom((string) ($translation['translation_state'] ?? 'draft')) ?? TranslationState::Draft;
                $block->translations()->create([
                    'locale' => $locale,
                    'content_json' => $translation['content'] ?? [],
                    'translation_state' => $state,
                    'validated_at' => $state === TranslationState::Ready ? now() : null,
                ]);
            }

            $this->mediaUsages->execute($block);

            $page->increment('revision');
            $this->audit->record('cms.block.created', $actor, $block, $page->business_id, after: $block->fresh()->toArray());

            return $block->load('translations');
        });
    }
}
