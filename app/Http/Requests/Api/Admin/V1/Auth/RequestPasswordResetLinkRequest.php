<?php

namespace App\Http\Requests\Api\Admin\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RequestPasswordResetLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return ['email' => ['required', 'string', 'email:rfc', 'max:255']];
    }
}
