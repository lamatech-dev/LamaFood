<?php

namespace App\Core\Seo;

use App\Core\Business\Models\Business;
use App\Core\Cms\Models\Page;
use App\Core\Cms\PageStatus;
use App\Core\Localization\LocaleRegistry;
use Illuminate\Support\Arr;

class BusinessStructuredData
{
    public function __construct(private readonly LocaleRegistry $locales) {}

    /** @return array<string, mixed> */
    public function forBusiness(Business $business, string $locale): array
    {
        $content = $this->publishedContactContent($business, $locale);
        $configuredType = (string) config('denardi.schema.type', 'CafeOrCoffeeShop');
        $type = in_array($configuredType, ['CafeOrCoffeeShop', 'LocalBusiness'], true)
            ? $configuredType
            : 'LocalBusiness';

        $data = [
            '@context' => 'https://schema.org',
            '@type' => $type,
            '@id' => url('/#business'),
            'name' => $business->name,
            'url' => url($this->locales->publicPath($locale)),
            'menu' => url($this->locales->publicPath($locale, 'menu')),
            'inLanguage' => $locale,
        ];

        $address = $content['address'] ?? config('denardi.schema.address');
        $telephone = $content['phone'] ?? config('denardi.phone');
        $mapUrl = $content['map_url'] ?? config('denardi.map_url');
        $instagramUrl = $content['instagram_url'] ?? config('denardi.instagram_url');
        $latitude = $content['latitude'] ?? config('denardi.schema.latitude');
        $longitude = $content['longitude'] ?? config('denardi.schema.longitude');

        $this->putWhenFilled($data, 'telephone', $telephone);
        $this->putWhenFilled($data, 'hasMap', $mapUrl);
        $this->putWhenFilled($data, 'logo', config('denardi.schema.logo_url'));
        $this->putWhenFilled($data, 'image', config('denardi.schema.image_url'));
        $this->putWhenFilled($data, 'priceRange', config('denardi.schema.price_range'));

        if (filled($address)) {
            $data['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $address,
            ];
        }

        if (is_numeric($latitude) && is_numeric($longitude)) {
            $data['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $latitude,
                'longitude' => (float) $longitude,
            ];
        }

        $openingHours = config('denardi.schema.opening_hours', []);
        if (is_array($openingHours) && $openingHours !== []) {
            $data['openingHours'] = array_values(array_filter($openingHours, fn (mixed $hours): bool => filled($hours)));
        }

        if (filled($instagramUrl)) {
            $data['sameAs'] = [(string) $instagramUrl];
        }

        return $data;
    }

    /** @return array{address?: mixed, phone?: mixed, map_url?: mixed, instagram_url?: mixed, latitude?: mixed, longitude?: mixed} */
    private function publishedContactContent(Business $business, string $locale): array
    {
        $page = Page::query()
            ->where('business_id', $business->id)
            ->where('slug', 'contact')
            ->where('status', PageStatus::Published)
            ->with('publishedRevision')
            ->first();
        $snapshot = $page?->publishedRevision?->snapshot_json;
        if (! is_array($snapshot)) {
            return [];
        }

        $blocks = collect(is_array($snapshot['blocks'] ?? null) ? $snapshot['blocks'] : []);
        $location = $blocks->first(fn (mixed $block): bool => is_array($block) && ($block['type'] ?? null) === 'location');
        $contact = $blocks->first(fn (mixed $block): bool => is_array($block) && ($block['type'] ?? null) === 'contact');

        return array_filter([
            'address' => is_array($location) ? Arr::get($location, "translations.{$locale}.content_json.address") : null,
            'map_url' => is_array($location) ? Arr::get($location, 'structure.mapUrl') : null,
            'latitude' => is_array($location) ? Arr::get($location, 'structure.lat') : null,
            'longitude' => is_array($location) ? Arr::get($location, 'structure.lng') : null,
            'phone' => is_array($contact) ? Arr::get($contact, 'structure.phone') : null,
            'instagram_url' => is_array($contact) ? Arr::get($contact, 'structure.instagramUrl') : null,
        ], fn (mixed $value): bool => filled($value));
    }

    /** @param array<string, mixed> $data */
    private function putWhenFilled(array &$data, string $key, mixed $value): void
    {
        if (filled($value)) {
            $data[$key] = $value;
        }
    }
}
