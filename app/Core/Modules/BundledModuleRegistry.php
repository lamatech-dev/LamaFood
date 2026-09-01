<?php

namespace App\Core\Modules;

use App\Core\Modules\Contracts\Module;
use LogicException;

class BundledModuleRegistry
{
    /** @return array<string, ModuleDescriptor> */
    public function all(): array
    {
        /** @var list<class-string> $configured */
        $configured = config('modules.bundled', []);
        $modules = [];

        foreach ($configured as $moduleClass) {
            $module = app($moduleClass);

            if (! $module instanceof Module) {
                throw new LogicException("Bundled module [{$moduleClass}] must implement the Module contract.");
            }

            if (isset($modules[$module->key()])) {
                throw new LogicException("Bundled module key [{$module->key()}] is duplicated.");
            }

            $modules[$module->key()] = new ModuleDescriptor(
                key: $module->key(),
                name: $module->name(),
                version: $module->version(),
                schemaVersion: $module->schemaVersion(),
                dependencies: $module->dependencies(),
                permissions: $module->permissions(),
                healthChecks: $module->healthChecks(),
            );
        }

        return $modules;
    }
}
