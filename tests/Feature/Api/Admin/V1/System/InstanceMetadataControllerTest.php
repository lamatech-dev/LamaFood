<?php

namespace Tests\Feature\Api\Admin\V1\System;

use App\Core\Instance\Models\InstanceMetadata;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InstanceMetadataControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_returns_foundation_metadata_without_license_enforcement(): void
    {
        Sanctum::actingAs(User::factory()->godfather()->create());

        $response = $this->getJson('/api/admin/v1/system/instance-metadata');

        $response->assertOk()
            ->assertJsonPath('data.license_id', null)
            ->assertJsonPath('data.install_channel', 'managed')
            ->assertJsonPath('data.schema_version', 1)
            ->assertJsonMissingPath('data.enforcement');
        $this->assertSame(1, InstanceMetadata::query()->count());
    }
}
