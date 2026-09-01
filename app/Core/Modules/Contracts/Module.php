<?php

namespace App\Core\Modules\Contracts;

interface Module
{
    public function key(): string;

    public function name(): string;

    public function version(): string;

    public function schemaVersion(): int;

    /** @return list<string> */
    public function dependencies(): array;

    /** @return list<string> */
    public function permissions(): array;

    /** @return list<string> */
    public function healthChecks(): array;
}
