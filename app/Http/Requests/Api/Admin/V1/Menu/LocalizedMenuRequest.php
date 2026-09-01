<?php

namespace App\Http\Requests\Api\Admin\V1\Menu;

use App\Http\Requests\Concerns\ValidatesConfiguredLocales;
use Illuminate\Foundation\Http\FormRequest;

abstract class LocalizedMenuRequest extends FormRequest
{
    use ValidatesConfiguredLocales;

    public function authorize(): bool
    {
        return true;
    }
}
