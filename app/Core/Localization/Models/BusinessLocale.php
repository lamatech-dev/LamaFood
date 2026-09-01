<?php

namespace App\Core\Localization\Models;

use App\Core\Business\Models\Business;
use App\Core\Localization\TextDirection;
use Database\Factories\Core\Localization\Models\BusinessLocaleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['business_id', 'locale', 'direction', 'name', 'native_name', 'is_default', 'is_enabled', 'is_required_for_publication'])]
class BusinessLocale extends Model
{
    /** @use HasFactory<BusinessLocaleFactory> */
    use HasFactory;

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    protected function casts(): array
    {
        return [
            'direction' => TextDirection::class,
            'is_default' => 'boolean',
            'is_enabled' => 'boolean',
            'is_required_for_publication' => 'boolean',
        ];
    }
}
