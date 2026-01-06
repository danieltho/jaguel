<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DiscountTypeEnum: string implements HasLabel
{
    case PERCENTAGE = 'percentage';
    case FIXED_AMOUNT = 'fixed_amount';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PERCENTAGE => 'Porcentaje',
            self::FIXED_AMOUNT => 'Monto fijo',
        };
    }
}
