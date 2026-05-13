<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MpPaymentStatusEnum: string implements HasColor, HasLabel
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case AUTHORIZED = 'authorized';
    case IN_PROCESS = 'in_process';
    case IN_MEDIATION = 'in_mediation';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case CHARGED_BACK = 'charged_back';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'Pendiente',
            self::APPROVED => 'Aprobado',
            self::AUTHORIZED => 'Autorizado',
            self::IN_PROCESS => 'En proceso',
            self::IN_MEDIATION => 'En mediación',
            self::REJECTED => 'Rechazado',
            self::CANCELLED => 'Cancelado',
            self::REFUNDED => 'Reembolsado',
            self::CHARGED_BACK => 'Contracargo',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::APPROVED, self::AUTHORIZED => 'success',
            self::PENDING, self::IN_PROCESS => 'warning',
            self::IN_MEDIATION => 'info',
            self::REJECTED, self::CANCELLED, self::CHARGED_BACK => 'danger',
            self::REFUNDED => 'gray',
        };
    }

    public function toPaymentStatus(): PaymentStatusEnum
    {
        return match ($this) {
            self::APPROVED, self::AUTHORIZED => PaymentStatusEnum::PAID,
            self::PENDING, self::IN_PROCESS, self::IN_MEDIATION => PaymentStatusEnum::PENDING,
            self::REJECTED, self::CANCELLED, self::CHARGED_BACK => PaymentStatusEnum::FAILED,
            self::REFUNDED => PaymentStatusEnum::REFUNDED,
        };
    }
}
