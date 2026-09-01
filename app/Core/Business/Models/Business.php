<?php

namespace App\Core\Business\Models;

use App\Core\Localization\Models\BusinessLocale;
use App\Models\User;
use Database\Factories\Core\Business\Models\BusinessFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'default_locale', 'timezone', 'is_active'])]
class Business extends Model
{
    /** @use HasFactory<BusinessFactory> */
    use HasFactory;

    /** @return HasMany<Branch, $this> */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    /** @return HasMany<BusinessLocale, $this> */
    public function locales(): HasMany
    {
        return $this->hasMany(BusinessLocale::class);
    }

    /** @return HasMany<User, $this> */
    public function users(): HasMany
    {
        return $this->hasMany(User::class)->businessVisible();
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
