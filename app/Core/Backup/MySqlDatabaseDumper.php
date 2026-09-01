<?php

namespace App\Core\Backup;

use App\Core\Backup\Contracts\DatabaseDumper;
use RuntimeException;
use Symfony\Component\Process\Process;

class MySqlDatabaseDumper implements DatabaseDumper
{
    public function dump(string $destination): void
    {
        $connection = config('database.connections.mysql');
        if (! is_array($connection)) {
            throw new RuntimeException('The MySQL connection is not configured.');
        }

        $process = new Process([
            'mysqldump',
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--host='.(string) ($connection['host'] ?? '127.0.0.1'),
            '--port='.(string) ($connection['port'] ?? '3306'),
            '--user='.(string) ($connection['username'] ?? ''),
            '--result-file='.$destination,
            (string) ($connection['database'] ?? ''),
        ], env: ['MYSQL_PWD' => (string) ($connection['password'] ?? '')]);
        $process->setTimeout(900);
        $process->mustRun();
    }
}
