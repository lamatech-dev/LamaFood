<?php

namespace App\Core\Analytics;

enum AnalyticsEventType: string
{
    case Scan = 'scan';
    case MenuView = 'menu_view';
    case CategoryView = 'category_view';
    case ProductView = 'product_view';
    case PageView = 'page_view';
}
