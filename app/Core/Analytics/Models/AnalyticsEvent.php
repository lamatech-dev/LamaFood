<?php

namespace App\Core\Analytics\Models;

use App\Core\Analytics\AnalyticsEventType;
use App\Core\Business\Models\Branch;
use App\Core\Business\Models\Business;
use App\Core\Qr\Models\QrCode;
use Database\Factories\Core\Analytics\Models\AnalyticsEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['business_id', 'branch_id', 'qr_code_id', 'event_type', 'locale', 'device_class', 'visitor_hash', 'subject_type', 'subject_public_id', 'occurred_at'])]
class AnalyticsEvent extends Model
{
    /** @use HasFactory<AnalyticsEventFactory> */
    use HasFactory;

    public $timestamps = false;

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

    /** @return BelongsTo<QrCode, $this> */
    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(QrCode::class);
    }

    protected function casts(): array
    {
        return ['event_type' => AnalyticsEventType::class, 'occurred_at' => 'immutable_datetime'];
    }
}
