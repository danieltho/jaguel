<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatusEnum: string implements HasLabel, HasColor
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'Pendiente',
            self::PAID => 'Pagado',
            self::FAILED => 'Fallido',
            self::REFUNDED => 'Reembolsado',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PAID => 'success',
            self::FAILED => 'danger',
            self::REFUNDED => 'gray',
        };
    }
}
