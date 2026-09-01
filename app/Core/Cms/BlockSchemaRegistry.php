<?php

namespace App\Core\Cms;

use InvalidArgumentException;

class BlockSchemaRegistry
{
    /** @return array{structure: array<string, string>, content: array<string, string>} */
    public function get(string $type): array
    {
        $schema = config("cms.blocks.{$type}");

        if (! is_array($schema) || ! isset($schema['structure'], $schema['content'])) {
            throw new InvalidArgumentException("Unsupported CMS block type [{$type}].");
        }

        /** @var array{structure: array<string, string>, content: array<string, string>} $schema */
        return $schema;
    }

    /** @return list<string> */
    public function types(): array
    {
        return array_keys(config('cms.blocks', []));
    }
}
