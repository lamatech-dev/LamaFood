<?php

namespace App\Core\Business;

use App\Core\Business\Models\Business;
use App\Models\User;

class BusinessContextResolver
{
    public function forUser(User $user): Business
    {
        if ($user->business !== null) {
            return $user->business;
        }

        abort_unless($user->isGodfather(), 422, 'A business context is required.');

        return Business::query()
            ->where('slug', config('denardi.business_slug'))
            ->where('is_active', true)
            ->firstOrFail();
    }
}
