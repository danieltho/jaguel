<?php

namespace App\Enums;

enum ProductStatusEnum: string
{
    case IN_STOCK = 'In Stock';
    case OUT_STOCK = 'Out Stock';
    case COMING_SOON = 'Coming Soon';

}
