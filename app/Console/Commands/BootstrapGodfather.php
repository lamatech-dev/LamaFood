<?php

namespace App\Console\Commands;

use App\Core\Audit\AuditRecorder;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class BootstrapGodfather extends Command
{
    protected $signature = 'lamatech:bootstrap-godfather';

    protected $description = 'Create or rotate the instance-level Lamatech Godfather account from environment configuration';

    public function handle(AuditRecorder $audit): int
    {
        $username = (string) config('lamatech.godfather.username');
        $password = (string) config('lamatech.godfather.password');
        $email = (string) config('lamatech.godfather.email');

        if ($username === '' || $password === '' || $password === '<local-secret>') {
            throw new RuntimeException('Godfather username and a non-placeholder password must be supplied through environment variables.');
        }

        $godfather = User::query()->firstOrNew(['is_godfather' => true]);
        $created = ! $godfather->exists;

        $godfather->forceFill([
            'business_id' => null,
            'name' => 'Godfather',
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'is_godfather' => true,
        ])->save();

        $audit->record(
            action: $created ? 'lamatech.godfather.bootstrapped' : 'lamatech.godfather.credentials_rotated',
            actor: $godfather,
            subject: $godfather,
            after: ['username' => $username, 'is_godfather' => true],
        );

        $this->components->info($created ? 'Godfather account created.' : 'Godfather credentials rotated.');

        return self::SUCCESS;
    }
}
