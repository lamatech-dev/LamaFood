<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublicAnalyticsViewRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(['category_view', 'product_view'])],
            'subject_public_id' => ['required', 'string', 'size:26'],
            'locale' => ['required', 'string', Rule::in(array_keys(config('localization.locales', [])))],
            'branch' => ['required', 'string', 'max:255'],
        ];
    }
}
