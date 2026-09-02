<?php

namespace App\Core\Backup\Contracts;

interface DatabaseRestorer
{
    public function restore(string $source): void;
}
