<?php

namespace App\Core\Menu;

enum PublicationState: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Inactive = 'inactive';
    case Archived = 'archived';
}
