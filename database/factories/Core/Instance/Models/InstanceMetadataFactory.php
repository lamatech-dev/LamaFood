<?php

namespace Database\Factories\Core\Instance\Models;

use App\Core\Instance\Models\InstanceMetadata;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<InstanceMetadata>
 */
class InstanceMetadataFactory extends Factory
{
    protected $model = InstanceMetadata::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'instance_id' => (string) Str::ulid(),
            'business_id' => null,
            'license_id' => null,
            'install_channel' => 'managed',
            'core_version' => '0.1.0',
            'schema_version' => 1,
            'metadata' => [],
        ];
    }
}
