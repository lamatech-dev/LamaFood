<?php

namespace Database\Factories\Core\Business\Models;

use App\Core\Business\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Business>
 */
class BusinessFactory extends Factory
{
    protected $model = Business::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(2),
            'default_locale' => 'fa',
            'timezone' => 'Asia/Tehran',
            'is_active' => true,
        ];
    }
}
