<?php

namespace App\Core\Menu;

enum AvailabilityState: string
{
    case Available = 'available';
    case SoldOut = 'sold_out';
}
