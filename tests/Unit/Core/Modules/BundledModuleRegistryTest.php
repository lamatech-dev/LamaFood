<?php

namespace Tests\Unit\Core\Modules;

use App\Core\Modules\BundledModuleRegistry;
use Tests\TestCase;

class BundledModuleRegistryTest extends TestCase
{
    public function test_discovers_only_configured_bundled_modules(): void
    {
        $modules = app(BundledModuleRegistry::class)->all();

        $this->assertSame(['foundation'], array_keys($modules));
        $this->assertSame('1.0.0', $modules['foundation']->version);
        $this->assertSame(1, $modules['foundation']->schemaVersion);
        $this->assertSame([], $modules['foundation']->dependencies);
    }
}
