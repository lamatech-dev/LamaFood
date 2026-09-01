<?php

namespace App\Core\Media;

enum MediaStatus: string
{
    case Processing = 'processing';
    case Ready = 'ready';
    case Archived = 'archived';
}
