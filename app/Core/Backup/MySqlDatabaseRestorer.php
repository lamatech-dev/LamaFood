<?php

namespace App\Core\Backup;

use App\Core\Backup\Contracts\DatabaseRestorer;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class MySqlDatabaseRestorer implements DatabaseRestorer
{
    public function restore(string $source): void
    {
        $connection = config('database.connections.mysql');
        if (! is_array($connection)) {
            throw new RuntimeException('The MySQL connection is not configured.');
        }

        $input = fopen($source, 'rb');
        if ($input === false) {
            throw new RuntimeException('The staged database restore file is unavailable.');
        }

        $process = new Process([
            'mysql',
            '--binary-mode',
            '--protocol=TCP',
            '--host='.(string) ($connection['host'] ?? '127.0.0.1'),
            '--port='.(string) ($connection['port'] ?? '3306'),
            '--user='.(string) ($connection['username'] ?? ''),
            (string) ($connection['database'] ?? ''),
        ], env: ['MYSQL_PWD' => (string) ($connection['password'] ?? '')]);
        $process->setInput($input);
        $process->setTimeout(1800);

        DB::disconnect('mysql');

        try {
            $process->mustRun();
        } catch (ProcessFailedException) {
            throw new RuntimeException('The database restore process failed. Review the protected operations log.');
        } finally {
            fclose($input);
            DB::reconnect('mysql');
        }
    }
}
