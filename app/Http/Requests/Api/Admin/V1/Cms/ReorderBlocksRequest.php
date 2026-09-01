<?php

namespace App\Http\Requests\Api\Admin\V1\Cms;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReorderBlocksRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'blocks' => ['required', 'array'],
            'blocks.*' => ['required', 'string', 'distinct'],
        ];
    }
}
