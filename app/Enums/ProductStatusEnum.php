<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ProductStatusEnum: string implements HasLabel, HasColor
{
    case IN_STOCK = 'In Stock';
    case OUT_STOCK = 'Out Stock';
    case COMING_SOON = 'Coming Soon';

    public function getLabel(): string|Htmlable|null
    {
        return $this->value;
    }
    public function getColor(): string
    {
        return match ($this) {
            self::IN_STOCK => 'success',
            self::OUT_STOCK => 'danger',
            self::COMING_SOON => 'warning',
        };
    }

}
