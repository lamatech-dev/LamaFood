<?php

namespace App\Http\Requests\Api\Admin\V1\Menu;

use Illuminate\Foundation\Http\FormRequest;

class ChangeProductStateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['publication_state' => ['required', 'in:draft,published,inactive,archived']];
    }
}
