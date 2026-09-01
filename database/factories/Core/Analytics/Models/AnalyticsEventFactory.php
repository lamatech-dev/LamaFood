<?php

namespace Database\Factories\Core\Analytics\Models;

use App\Core\Analytics\AnalyticsEventType;
use App\Core\Analytics\Models\AnalyticsEvent;
use App\Core\Business\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AnalyticsEvent> */
class AnalyticsEventFactory extends Factory
{
    protected $model = AnalyticsEvent::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'event_type' => AnalyticsEventType::PageView,
            'locale' => 'fa',
            'device_class' => 'desktop',
            'visitor_hash' => hash('sha256', fake()->uuid()),
            'occurred_at' => now(),
        ];
    }
}
