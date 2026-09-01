<?php

namespace Database\Factories\Core\Business\Models;

use App\Core\Business\Models\Branch;
use App\Core\Business\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'name' => fake()->city(),
            'slug' => fake()->unique()->slug(2),
            'timezone' => 'Asia/Tehran',
            'is_default' => false,
            'is_active' => true,
        ];
    }
}
