<?php

namespace App\Core\Qr\Models;

use App\Core\Analytics\Models\AnalyticsEvent;
use App\Core\Business\Models\Branch;
use App\Core\Business\Models\Business;
use App\Core\Qr\QrCodeType;
use App\Models\User;
use Database\Factories\Core\Qr\Models\QrCodeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['public_id', 'business_id', 'branch_id', 'type', 'label', 'table_key', 'is_active', 'created_by'])]
class QrCode extends Model
{
    /** @use HasFactory<QrCodeFactory> */
    use HasFactory;

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /** @return BelongsTo<Branch, $this> */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<AnalyticsEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(AnalyticsEvent::class);
    }

    protected function casts(): array
    {
        return ['type' => QrCodeType::class, 'is_active' => 'boolean'];
    }
}
