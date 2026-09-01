<?php

namespace Database\Factories\Core\Localization\Models;

use App\Core\Business\Models\Business;
use App\Core\Localization\Models\BusinessLocale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessLocale>
 */
class BusinessLocaleFactory extends Factory
{
    protected $model = BusinessLocale::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'locale' => 'fa',
            'direction' => 'rtl',
            'name' => 'Persian',
            'native_name' => 'فارسی',
            'is_default' => true,
            'is_enabled' => true,
            'is_required_for_publication' => true,
        ];
    }
}
