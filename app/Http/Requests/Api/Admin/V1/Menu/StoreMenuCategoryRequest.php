<?php

namespace App\Http\Requests\Api\Admin\V1\Menu;

class StoreMenuCategoryRequest extends LocalizedMenuRequest
{
    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:160', 'alpha_dash:ascii'],
            'position' => ['sometimes', 'integer', 'min:0'],
            'translations' => ['required', 'array'],
            'translations.*' => ['array'],
            'translations.*.name' => ['nullable', 'string', 'max:255'],
            'translations.*.description' => ['nullable', 'string', 'max:2000'],
            'translations.*.seo_title' => ['nullable', 'string', 'max:255'],
            'translations.*.seo_description' => ['nullable', 'string', 'max:500'],
            'translations.*.translation_state' => ['sometimes', 'in:draft,ready'],
        ];
    }
}
