<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CouponTypeEnum: string implements HasLabel
{
    case COUPON = 'coupon';
    case AUTOMATIC_DISCOUNT = 'automatic_discount';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::COUPON => 'Cupón con código',
            self::AUTOMATIC_DISCOUNT => 'Descuento automático',
        };
    }
}
