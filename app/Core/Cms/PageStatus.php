<?php

namespace App\Core\Cms;

enum PageStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Inactive = 'inactive';
    case Archived = 'archived';
}
