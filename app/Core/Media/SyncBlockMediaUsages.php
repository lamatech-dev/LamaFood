<?php

namespace App\Core\Media;

use App\Core\Cms\BlockSchemaRegistry;
use App\Core\Cms\Models\Block;
use App\Core\Media\Models\Media;
use Illuminate\Validation\ValidationException;

class SyncBlockMediaUsages
{
    public function __construct(private readonly BlockSchemaRegistry $schemas) {}

    public function execute(Block $block): void
    {
        $block->usages()->delete();
        $structure = $block->structure_json;

        foreach ($this->schemas->get($block->type)['structure'] as $field => $rule) {
            if (! str_contains(strtolower($field), 'mediaid') || ! array_key_exists($field, $structure)) {
                continue;
            }

            $ids = is_array($structure[$field]) ? $structure[$field] : [$structure[$field]];
            foreach ($ids as $mediaId) {
                $media = Media::query()
                    ->whereKey($mediaId)
                    ->where('business_id', $block->page->business_id)
                    ->first();
                if ($media === null) {
                    throw ValidationException::withMessages(["structure.{$field}" => ['Media must belong to the same business.']]);
                }
                $media->usages()->create([
                    'subject_type' => $block->getMorphClass(),
                    'subject_id' => $block->id,
                    'field' => "structure.{$field}",
                ]);
            }
        }
    }
}
