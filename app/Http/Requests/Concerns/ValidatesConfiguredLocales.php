<?php

namespace App\Http\Requests\Concerns;

use App\Core\Localization\LocaleRegistry;
use Illuminate\Validation\Validator;

trait ValidatesConfiguredLocales
{
    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $unsupported = array_diff(array_keys($this->array('translations')), app(LocaleRegistry::class)->codes());
            if ($unsupported !== []) {
                $validator->errors()->add('translations', 'Unsupported locales: '.implode(', ', $unsupported).'.');
            }
        });
    }
}
