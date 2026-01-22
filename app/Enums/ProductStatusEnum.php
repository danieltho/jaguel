<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ProductStatusEnum: string implements HasLabel
{
    case IN_STOCK = 'Limitado';
    case OUT_STOCK = 'Infinito';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::IN_STOCK => 'Limitado',
            self::OUT_STOCK => 'Infinito',
        };
    }

}
