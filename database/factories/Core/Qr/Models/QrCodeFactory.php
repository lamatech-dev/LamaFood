<?php

namespace Database\Factories\Core\Qr\Models;

use App\Core\Business\Models\Branch;
use App\Core\Qr\Models\QrCode;
use App\Core\Qr\QrCodeType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<QrCode> */
class QrCodeFactory extends Factory
{
    protected $model = QrCode::class;

    public function definition(): array
    {
        $branch = Branch::factory();

        return [
            'public_id' => (string) Str::ulid(),
            'business_id' => fn (array $attributes): int => Branch::query()->findOrFail($attributes['branch_id'])->business_id,
            'branch_id' => $branch,
            'type' => QrCodeType::Menu,
            'label' => fake()->words(2, true),
            'table_key' => null,
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }
}
