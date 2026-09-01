<?php

namespace App\Core\Audit;

use Illuminate\Support\Str;

class RedactsSensitiveValues
{
    /** @var list<string> */
    private const SENSITIVE_KEYS = [
        'authorization',
        'cookie',
        'password',
        'secret',
        'token',
        'app_key',
    ];

    /** @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    public function handle(array $values): array
    {
        $redacted = [];

        foreach ($values as $key => $value) {
            $normalizedKey = Str::lower((string) $key);

            if (Str::contains($normalizedKey, self::SENSITIVE_KEYS)) {
                $redacted[$key] = '[REDACTED]';

                continue;
            }

            $redacted[$key] = is_array($value) ? $this->handle($value) : $value;
        }

        return $redacted;
    }
}
