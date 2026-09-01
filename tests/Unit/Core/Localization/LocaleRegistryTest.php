<?php

namespace Tests\Unit\Core\Localization;

use App\Core\Localization\LocaleRegistry;
use LogicException;
use Tests\TestCase;

class LocaleRegistryTest extends TestCase
{
    public function test_denardi_v1_exposes_three_data_driven_locales_with_direction_metadata(): void
    {
        $registry = app(LocaleRegistry::class);

        $this->assertSame(['fa', 'en', 'ar'], $registry->codes());
        $this->assertSame('fa', $registry->default());
        $this->assertSame('rtl', $registry->get('fa')['direction']);
        $this->assertSame('ltr', $registry->get('en')['direction']);
        $this->assertSame('rtl', $registry->get('ar')['direction']);
        $this->assertSame('fa|en|ar', $registry->routePattern());
        $this->assertNull(config('localization.public_fallback'));
    }

    public function test_rejects_locale_with_invalid_direction(): void
    {
        config()->set('localization.locales.fa.direction', 'auto');

        $this->expectException(LogicException::class);

        app(LocaleRegistry::class)->all();
    }
}
