<?php

namespace Tests\Feature\Api\Admin\V1\Cms;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BlockSchemaControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_returns_401_when_unauthenticated(): void
    {
        $this->getJson('/api/admin/v1/cms/block-schemas')->assertUnauthorized();
    }

    public function test_godfather_can_read_explicit_structure_and_translation_schemas(): void
    {
        Sanctum::actingAs(User::factory()->godfather()->create());

        $this->getJson('/api/admin/v1/cms/block-schemas')
            ->assertOk()
            ->assertJsonPath('data.hero.structure.mediaId', 'integer?')
            ->assertJsonPath('data.hero.content.title', 'string');
    }
}
