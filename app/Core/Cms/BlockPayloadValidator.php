<?php

namespace App\Core\Cms;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BlockPayloadValidator
{
    public function __construct(private readonly BlockSchemaRegistry $schemas) {}

    /** @param array<string, mixed> $payload */
    public function validateStructure(string $type, array $payload): void
    {
        $this->validate($payload, $this->schemas->get($type)['structure'], 'structure');
    }

    /** @param array<string, mixed> $payload */
    public function validateContent(string $type, array $payload, bool $ready): void
    {
        $schema = $this->schemas->get($type)['content'];

        if (! $ready) {
            $schema = array_map(static fn (string $rule): string => str_ends_with($rule, '?') ? $rule : $rule.'?', $schema);
        }

        $this->validate($payload, $schema, 'content');
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, string>  $schema
     */
    private function validate(array $payload, array $schema, string $prefix): void
    {
        $unknown = array_diff(array_keys($payload), array_keys($schema));

        if ($unknown !== []) {
            throw ValidationException::withMessages([
                $prefix => ['Unexpected fields: '.implode(', ', $unknown).'.'],
            ]);
        }

        $rules = [];
        foreach ($schema as $field => $type) {
            $optional = str_ends_with($type, '?');
            $base = rtrim($type, '?');
            $rules[$field] = [$optional ? 'nullable' : 'required', match ($base) {
                'integer' => 'integer',
                'numeric' => 'numeric',
                'url' => 'url',
                'integer[]', 'url[]' => 'array',
                default => 'string',
            }];

            if ($base === 'integer[]') {
                $rules[$field.'.*'] = ['integer'];
            } elseif ($base === 'url[]') {
                $rules[$field.'.*'] = ['url'];
            }
        }

        Validator::make($payload, $rules)->validate();
    }
}
