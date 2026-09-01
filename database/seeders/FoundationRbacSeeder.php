<?php

namespace Database\Seeders;

use App\Core\Authorization\ProvisionFoundationRbac;
use App\Core\Business\Models\Business;
use Illuminate\Database\Seeder;

class FoundationRbacSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(ProvisionFoundationRbac $provision): void
    {
        Business::query()->each(static fn (Business $business) => $provision->execute($business));
    }
}
