<?php

namespace App\Core\Business\Actions;

use App\Core\Business\Models\Business;
use App\Core\Localization\LocaleRegistry;
use Illuminate\Support\Facades\DB;

class CreateBusiness
{
    public function __construct(private readonly LocaleRegistry $locales) {}

    public function execute(
        string $name,
        string $slug,
        string $branchName,
        string $branchSlug,
        string $timezone = 'Asia/Tehran',
    ): Business {
        return DB::transaction(function () use ($name, $slug, $branchName, $branchSlug, $timezone): Business {
            $business = Business::query()->create([
                'name' => $name,
                'slug' => $slug,
                'default_locale' => $this->locales->default(),
                'timezone' => $timezone,
                'is_active' => true,
            ]);

            $business->branches()->create([
                'name' => $branchName,
                'slug' => $branchSlug,
                'timezone' => $timezone,
                'is_default' => true,
                'is_active' => true,
            ]);

            foreach ($this->locales->all() as $locale => $metadata) {
                $business->locales()->create([
                    'locale' => $locale,
                    'direction' => $metadata['direction'],
                    'name' => $metadata['name'],
                    'native_name' => $metadata['native_name'],
                    'is_default' => $locale === $this->locales->default(),
                    'is_enabled' => true,
                    'is_required_for_publication' => $metadata['required_for_publication'],
                ]);
            }

            return $business->load(['branches', 'locales']);
        });
    }
}
