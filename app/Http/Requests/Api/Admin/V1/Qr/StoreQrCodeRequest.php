<?php

namespace App\Http\Requests\Api\Admin\V1\Qr;

use App\Core\Qr\QrCodeType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQrCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer'],
            'type' => ['required', Rule::enum(QrCodeType::class)],
            'label' => ['required', 'string', 'max:255'],
            'table_key' => ['nullable', 'string', 'max:80', 'alpha_dash:ascii'],
        ];
    }
}
