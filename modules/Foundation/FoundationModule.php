<?php

namespace Modules\Foundation;

use App\Core\Modules\Contracts\Module;

class FoundationModule implements Module
{
    public function key(): string
    {
        return 'foundation';
    }

    public function name(): string
    {
        return 'LamaFood Foundation';
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function schemaVersion(): int
    {
        return 1;
    }

    public function dependencies(): array
    {
        return [];
    }

    public function permissions(): array
    {
        return [];
    }

    public function healthChecks(): array
    {
        return ['database'];
    }
}
