<?php

namespace App\Http\Requests\Api\Admin\V1\Cms;

use App\Http\Requests\Concerns\ValidatesConfiguredLocales;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBlockRequest extends FormRequest
{
    use ValidatesConfiguredLocales;
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
            'type' => ['required', 'string'],
            'position' => ['required', 'integer', 'min:0'],
            'structure' => ['present', 'array'],
            'translations' => ['required', 'array'],
            'translations.*' => ['array'],
            'translations.*.content' => ['present', 'array'],
            'translations.*.translation_state' => ['sometimes', 'in:draft,ready'],
        ];
    }
}
