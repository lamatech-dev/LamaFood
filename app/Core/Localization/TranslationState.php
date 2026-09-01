<?php

namespace App\Core\Localization;

enum TranslationState: string
{
    case Draft = 'draft';
    case Ready = 'ready';
}
