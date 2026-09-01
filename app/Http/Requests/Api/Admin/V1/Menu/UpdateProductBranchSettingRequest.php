<?php

namespace App\Http\Requests\Api\Admin\V1\Menu;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductBranchSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'price_amount' => ['required', 'integer', 'min:0'],
            'availability_state' => ['required', 'in:available,sold_out'],
            'expected_version' => ['required', 'integer', 'min:0'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
