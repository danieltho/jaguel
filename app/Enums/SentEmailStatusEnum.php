<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum SentEmailStatusEnum: string implements HasColor, HasIcon, HasLabel
{
    case SENDING = 'sending';
    case SENT = 'sent';
    case FAILED = 'failed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SENDING => 'En envío',
            self::SENT => 'Enviado',
            self::FAILED => 'Fallido',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::SENDING => 'warning',
            self::SENT => 'success',
            self::FAILED => 'danger',
        };
    }

    public function getIcon(): string|\BackedEnum|null
    {
        return match ($this) {
            self::SENDING => Heroicon::OutlinedClock,
            self::SENT => Heroicon::OutlinedCheckCircle,
            self::FAILED => Heroicon::OutlinedXCircle,
        };
    }
}
