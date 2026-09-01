<?php

namespace App\Http\Requests\Api\Admin\V1\Cms;

class UpdateBlockRequest extends StoreBlockRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['position']);

        return [...$rules, 'is_enabled' => ['required', 'boolean']];
    }
}
