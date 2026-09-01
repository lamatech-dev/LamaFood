<?php

namespace App\Core\Localization;

use LogicException;

class LocaleRegistry
{
    /** @return array<string, array{name: string, native_name: string, direction: string, required_for_publication: bool}> */
    public function all(): array
    {
        /** @var array<string, array<string, mixed>> $configured */
        $configured = config('localization.locales', []);
        $locales = [];

        foreach ($configured as $locale => $metadata) {
            if (! isset($metadata['direction']) || ! in_array($metadata['direction'], array_column(TextDirection::cases(), 'value'), true)) {
                throw new LogicException("Locale [{$locale}] has an invalid text direction.");
            }

            if (! isset($metadata['name'], $metadata['native_name']) || ! is_string($metadata['name']) || ! is_string($metadata['native_name'])) {
                throw new LogicException("Locale [{$locale}] is missing required display metadata.");
            }

            $locales[$locale] = [
                'name' => $metadata['name'],
                'native_name' => $metadata['native_name'],
                'direction' => $metadata['direction'],
                'required_for_publication' => ($metadata['required_for_publication'] ?? false) === true,
            ];
        }

        if (! array_key_exists($this->default(), $locales)) {
            throw new LogicException('The default locale must exist in the locale registry.');
        }

        return $locales;
    }

    public function default(): string
    {
        return (string) config('localization.default', 'fa');
    }

    /** @return array{name: string, native_name: string, direction: string, required_for_publication: bool} */
    public function get(string $locale): array
    {
        $locales = $this->all();

        if (! isset($locales[$locale])) {
            throw new LogicException("Locale [{$locale}] is not supported.");
        }

        return $locales[$locale];
    }

    /** @return list<string> */
    public function codes(): array
    {
        return array_keys($this->all());
    }

    public function routePattern(): string
    {
        return implode('|', array_map(static fn (string $locale): string => preg_quote($locale, '/'), $this->codes()));
    }
}
