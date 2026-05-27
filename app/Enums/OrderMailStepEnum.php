<?php

namespace App\Enums;

use App\Models\Order;

/**
 * Los pasos del seguimiento de un pedido que disparan un mail al comprador.
 * Cada paso se corresponde con un "estado" visible en la barra de progreso del mail.
 */
enum OrderMailStepEnum: string
{
    case PENDING = 'pendiente';
    case APPROVED = 'aprobado';
    case IN_PREPARATION = 'en preparacion';
    case SHIPPING = 'envio';
    case DELIVERED = 'entregado';

    /**
     * Orden de los pasos en la barra de progreso del mail.
     *
     * @return array<int, self>
     */
    public static function steps(): array
    {
        return [self::PENDING, self::APPROVED, self::IN_PREPARATION, self::SHIPPING, self::DELIVERED];
    }

    /** Etiqueta visible en la barra de progreso. */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendiente',
            self::APPROVED => 'Aprobado',
            self::IN_PREPARATION => 'En preparación',
            self::SHIPPING => 'Envío',
            self::DELIVERED => 'Entregado',
        };
    }

    /** Mapea un estado de pedido a su paso de mail, o null si ese estado no notifica. */
    public static function fromOrderStatus(OrderStatusEnum $status): ?self
    {
        return match ($status) {
            OrderStatusEnum::IN_PREPARATION => self::IN_PREPARATION,
            OrderStatusEnum::SHIPPING, OrderStatusEnum::READY_PICKUP => self::SHIPPING,
            OrderStatusEnum::DELIVERED => self::DELIVERED,
            default => null,
        };
    }

    /** Asunto del mail, contextualizado según el pedido (p. ej. retiro vs envío). */
    public function subject(Order $order): string
    {
        $number = '#'.$order->order_number;

        return match ($this) {
            self::PENDING => "Recibimos tu pedido {$number} — pendiente de pago",
            self::APPROVED => "¡Gracias por tu compra! Pedido {$number}",
            self::IN_PREPARATION => "Tu pedido {$number} está en preparación",
            self::SHIPPING => $order->delivery_type === 'pickup'
                ? "Tu pedido {$number} está listo para retirar"
                : "Tu pedido {$number} está en camino",
            self::DELIVERED => "¡Tu pedido {$number} fue entregado!",
        };
    }
}
