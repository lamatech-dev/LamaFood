<?php

namespace Tests\Feature\Core\Business;

use App\Core\Business\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GodfatherBusinessContextTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_godfather_resolves_the_instance_business_without_changing_rbac_architecture(): void
    {
        Business::factory()->create(['slug' => 'denardi', 'name' => 'Denardi']);
        Sanctum::actingAs(User::factory()->godfather()->create());

        $this->getJson('/api/admin/v1/business/context')
            ->assertOk()
            ->assertJsonPath('data.business.slug', 'denardi');
    }
}
