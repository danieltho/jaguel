<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethodTypeEnum: string implements HasLabel
{
    case CREDIT_CARD = 'credit_card';
    case BANK_TRANSFER = 'bank_transfer';
    case CASH_SHOWROOM = 'cash_showroom';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CREDIT_CARD => 'Tarjeta de Crédito/Débito',
            self::BANK_TRANSFER => 'Transferencia',
            self::CASH_SHOWROOM => 'Efectivo Showroom',
        };
    }
}
