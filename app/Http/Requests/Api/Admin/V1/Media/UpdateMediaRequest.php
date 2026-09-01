<?php

namespace App\Http\Requests\Api\Admin\V1\Media;

use App\Http\Requests\Concerns\ValidatesConfiguredLocales;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMediaRequest extends FormRequest
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
            'status' => ['sometimes', 'in:ready,archived'],
            'translations' => ['sometimes', 'array'],
            'translations.*.alt' => ['nullable', 'string', 'max:255'],
            'translations.*.title' => ['nullable', 'string', 'max:255'],
            'translations.*.caption' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
