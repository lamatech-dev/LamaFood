<?php

namespace App\Core\Cms;

use App\Core\Cms\Models\Block;
use App\Core\Media\Models\Media;

class BlockSnapshotBuilder
{
    /** @return array<string, mixed> */
    public function build(Block $block): array
    {
        $media = Media::query()
            ->whereIn('id', $block->usages()->pluck('media_id'))
            ->with('translations')
            ->get()
            ->keyBy('id')
            ->map(fn (Media $item): array => [
                'path' => $item->path,
                'optimized_path' => $item->optimized_path,
                'thumbnail_path' => $item->thumbnail_path,
                'translations' => $item->translations->keyBy('locale')->map->only(['alt', 'title'])->all(),
            ])->all();

        return [
            'public_id' => $block->public_id,
            'type' => $block->type,
            'position' => $block->position,
            'structure' => $block->structure_json,
            'translations' => $block->translations->keyBy('locale')->map->only(['content_json'])->all(),
            'media' => $media,
        ];
    }
}
