<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case PENDING = 'Pendiente';
    case IN_PREPARATION = 'En preparacion';
    case PREPARATED = 'Preparado';
    case PREPARATED_PENDING_SHIPPING = 'Preparado sin despachar';
    case SHIPPING = 'Despachada a transporte';
    case READY_PICKUP = 'Lista para retiro';
    case DELIVERED = 'Entregado';
    case CANCELLED = 'Cancelado';

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::IN_PREPARATION => 'warning',
            self::PREPARATED => 'info',
            self::PREPARATED_PENDING_SHIPPING => 'info',
            self::SHIPPING => 'primary',
            self::READY_PICKUP => 'success',
            self::DELIVERED => 'success',
            self::CANCELLED => 'danger',
        };
    }
}
