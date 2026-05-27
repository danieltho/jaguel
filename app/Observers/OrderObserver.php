<?php

namespace App\Observers;

use App\Enums\OrderMailStepEnum;
use App\Enums\PaymentStatusEnum;
use App\Mail\OrderStatusMail;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

class OrderObserver
{
    /**
     * Notifica al comprador cuando cambia el estado del pedido o se aprueba el pago.
     *
     * Solo cubre cambios hechos con eventos Eloquent (p. ej. el panel de Filament).
     * El flujo de MercadoPago usa saveQuietly() y dispara el mail de "aprobado" por
     * su cuenta en MercadoPagoService::syncOrderStatus().
     */
    public function updated(Order $order): void
    {
        // Pago aprobado (cualquier método): mail de confirmación de compra.
        if ($order->wasChanged('payment_status')
            && $order->payment_status === PaymentStatusEnum::PAID) {
            $this->send($order, OrderMailStepEnum::APPROVED);
        }

        // Avance del estado del pedido: en preparación / envío / entregado.
        if ($order->wasChanged('status')) {
            $step = OrderMailStepEnum::fromOrderStatus($order->status);
            if ($step) {
                $this->send($order, $step);
            }
        }
    }

    private function send(Order $order, OrderMailStepEnum $step): void
    {
        if (! filled($order->email)) {
            return;
        }

        Mail::to($order->email)->queue(new OrderStatusMail($order, $step));
    }
}
