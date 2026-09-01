<?php

namespace App\Http\Requests\Api\Admin\V1\Menu;

class StoreProductRequest extends LocalizedMenuRequest
{
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'string'],
            'slug' => ['required', 'string', 'max:160', 'alpha_dash:ascii'],
            'position' => ['sometimes', 'integer', 'min:0'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_new' => ['sometimes', 'boolean'],
            'is_best_seller' => ['sometimes', 'boolean'],
            'translations' => ['required', 'array'],
            'translations.*' => ['array'],
            'translations.*.name' => ['nullable', 'string', 'max:255'],
            'translations.*.description' => ['nullable', 'string', 'max:3000'],
            'translations.*.ingredients' => ['nullable', 'string', 'max:2000'],
            'translations.*.allergen_notice' => ['nullable', 'string', 'max:2000'],
            'translations.*.translation_state' => ['sometimes', 'in:draft,ready'],
        ];
    }
}
