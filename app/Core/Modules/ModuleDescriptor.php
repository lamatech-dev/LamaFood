<?php

namespace App\Core\Modules;

final readonly class ModuleDescriptor
{
    /**
     * @param  list<string>  $dependencies
     * @param  list<string>  $permissions
     * @param  list<string>  $healthChecks
     */
    public function __construct(
        public string $key,
        public string $name,
        public string $version,
        public int $schemaVersion,
        public array $dependencies,
        public array $permissions,
        public array $healthChecks,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'name' => $this->name,
            'version' => $this->version,
            'schema_version' => $this->schemaVersion,
            'dependencies' => $this->dependencies,
            'permissions' => $this->permissions,
            'health_checks' => $this->healthChecks,
        ];
    }
}
