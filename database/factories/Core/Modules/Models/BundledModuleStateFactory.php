<?php

namespace Database\Factories\Core\Modules\Models;

use App\Core\Business\Models\Business;
use App\Core\Modules\Models\BundledModuleState;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BundledModuleState>
 */
class BundledModuleStateFactory extends Factory
{
    protected $model = BundledModuleState::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'module_key' => 'foundation',
            'module_version' => '1.0.0',
            'schema_version' => 1,
            'is_enabled' => true,
            'configuration' => [],
        ];
    }
}
