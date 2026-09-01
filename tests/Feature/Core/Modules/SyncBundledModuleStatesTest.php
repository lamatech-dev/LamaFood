<?php

namespace Tests\Feature\Core\Modules;

use App\Core\Business\Models\Business;
use App\Core\Modules\SyncBundledModuleStates;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SyncBundledModuleStatesTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_registers_bundled_foundation_module_without_runtime_installer(): void
    {
        $business = Business::factory()->create();

        app(SyncBundledModuleStates::class)->execute($business);

        $this->assertDatabaseHas('bundled_module_states', [
            'business_id' => $business->id,
            'module_key' => 'foundation',
            'module_version' => '1.0.0',
            'schema_version' => 1,
            'is_enabled' => true,
        ]);
    }
}
