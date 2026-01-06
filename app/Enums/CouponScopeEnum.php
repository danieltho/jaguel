<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CouponScopeEnum: string implements HasLabel
{
    case GENERAL = 'general';
    case CATEGORY = 'category';
    case PRODUCT = 'product';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::GENERAL => 'General (cualquier producto)',
            self::CATEGORY => 'Por categoría',
            self::PRODUCT => 'Por producto',
        };
    }
}
