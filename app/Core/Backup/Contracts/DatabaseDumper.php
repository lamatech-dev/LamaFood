<?php

namespace App\Core\Backup\Contracts;

interface DatabaseDumper
{
    public function dump(string $destination): void;
}
