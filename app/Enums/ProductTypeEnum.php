<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ProductTypeEnum: string implements HasLabel
{
    case FISICO = 'Fisico';
    case SERVICE = 'Digital o servicio';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::FISICO => 'Físico',
            self::SERVICE => 'Digital o servicio'
        };
    }

}
