<?php

namespace App\Core\Analytics\Actions;

use App\Core\Analytics\AnalyticsEventType;
use App\Core\Analytics\Models\AnalyticsEvent;
use App\Core\Business\Models\Business;
use App\Core\Qr\Models\QrCode;
use Illuminate\Support\Facades\DB;

class RecordAnalyticsEvent
{
    public function execute(
        Business $business,
        AnalyticsEventType $type,
        string $visitorHash,
        string $deviceClass,
        ?string $locale = null,
        ?QrCode $qrCode = null,
        ?string $subjectType = null,
        ?string $subjectPublicId = null,
    ): ?AnalyticsEvent {
        return DB::transaction(function () use ($business, $type, $visitorHash, $deviceClass, $locale, $qrCode, $subjectType, $subjectPublicId): ?AnalyticsEvent {
            if ($type === AnalyticsEventType::Scan && $qrCode !== null) {
                QrCode::query()->whereKey($qrCode->id)->lockForUpdate()->firstOrFail();
                $duplicateExists = AnalyticsEvent::query()
                    ->where('qr_code_id', $qrCode->id)
                    ->where('event_type', AnalyticsEventType::Scan)
                    ->where('visitor_hash', $visitorHash)
                    ->where('occurred_at', '>=', now()->subMinutes(30))
                    ->exists();

                if ($duplicateExists) {
                    return null;
                }
            }

            return AnalyticsEvent::query()->create([
                'business_id' => $business->id,
                'branch_id' => $qrCode?->branch_id,
                'qr_code_id' => $qrCode?->id,
                'event_type' => $type,
                'locale' => $locale,
                'device_class' => $deviceClass,
                'visitor_hash' => $visitorHash,
                'subject_type' => $subjectType,
                'subject_public_id' => $subjectPublicId,
                'occurred_at' => now(),
            ]);
        });
    }
}
