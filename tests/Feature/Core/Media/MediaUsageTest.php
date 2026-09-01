<?php

namespace Tests\Feature\Core\Media;

use App\Core\Business\Models\Business;
use App\Core\Cms\Models\Page;
use App\Core\Media\MediaStatus;
use App\Core\Media\Models\Media;
use App\Core\Media\Models\MediaUsage;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MediaUsageTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_database_prevents_deleting_media_that_is_still_in_use(): void
    {
        $business = Business::factory()->create();
        $page = Page::query()->create([
            'public_id' => (string) Str::ulid(),
            'business_id' => $business->id,
            'slug' => 'home',
        ]);
        $media = Media::query()->create([
            'public_id' => (string) Str::ulid(),
            'business_id' => $business->id,
            'disk' => 'public',
            'path' => 'test/image.jpg',
            'mime' => 'image/jpeg',
            'size' => 100,
            'checksum' => str_repeat('a', 64),
            'status' => MediaStatus::Ready,
        ]);
        MediaUsage::query()->create([
            'media_id' => $media->id,
            'subject_type' => $page->getMorphClass(),
            'subject_id' => $page->id,
            'field' => 'hero.mediaId',
        ]);

        $this->expectException(QueryException::class);
        $media->delete();
    }
}
