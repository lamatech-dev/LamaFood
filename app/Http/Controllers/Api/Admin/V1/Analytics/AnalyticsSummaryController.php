<?php

namespace App\Http\Controllers\Api\Admin\V1\Analytics;

use App\Core\Analytics\AnalyticsEventType;
use App\Core\Analytics\Models\AnalyticsEvent;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsSummaryController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $periods = ['today' => now()->startOfDay(), '7_days' => now()->subDays(6)->startOfDay(), '30_days' => now()->subDays(29)->startOfDay()];
        $summary = [];

        foreach ($periods as $period => $from) {
            $counts = $this->query($request)
                ->where('occurred_at', '>=', $from)
                ->selectRaw('event_type, COUNT(*) AS aggregate')
                ->groupBy('event_type')
                ->pluck('aggregate', 'event_type');

            $summary[$period] = collect(AnalyticsEventType::cases())->mapWithKeys(
                fn (AnalyticsEventType $type): array => [$type->value => (int) ($counts[$type->value] ?? 0)],
            )->all();
        }

        $from = $periods['30_days'];
        $summary['breakdowns'] = [
            'devices_30_days' => $this->query($request)
                ->where('occurred_at', '>=', $from)
                ->selectRaw('device_class, COUNT(*) AS aggregate')
                ->groupBy('device_class')
                ->orderByDesc('aggregate')
                ->get()
                ->map(fn (AnalyticsEvent $event): array => [
                    'device_class' => $event->device_class,
                    'count' => (int) $event->getAttribute('aggregate'),
                ])->values()->all(),
            'table_scans_30_days' => $this->query($request)
                ->where('occurred_at', '>=', $from)
                ->where('event_type', AnalyticsEventType::Scan)
                ->whereNotNull('qr_code_id')
                ->with('qrCode:id,public_id,branch_id,label,table_key')
                ->selectRaw('qr_code_id, COUNT(*) AS aggregate')
                ->groupBy('qr_code_id')
                ->orderByDesc('aggregate')
                ->get()
                ->filter(fn (AnalyticsEvent $event): bool => $event->qrCode?->table_key !== null)
                ->map(fn (AnalyticsEvent $event): array => [
                    'qr_code_public_id' => $event->qrCode->public_id,
                    'branch_id' => $event->qrCode->branch_id,
                    'label' => $event->qrCode->label,
                    'table_key' => $event->qrCode->table_key,
                    'count' => (int) $event->getAttribute('aggregate'),
                ])->values()->all(),
        ];

        return response()->json(['data' => $summary]);
    }

    /** @return Builder<AnalyticsEvent> */
    private function query(Request $request): Builder
    {
        /** @var User $user */
        $user = $request->user();
        $query = AnalyticsEvent::query();

        return $user->isGodfather() ? $query : $query->where('business_id', $user->business_id);
    }
}
