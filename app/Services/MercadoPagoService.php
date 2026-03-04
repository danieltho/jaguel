<?php

namespace App\Services;

use App\Enums\PaymentStatusEnum;
use App\Models\Order;

class MercadoPagoService
{
    public function createPreference(Order $order): array
    {
        $client = new \MercadoPago\Client\Preference\PreferenceClient;

        $items = $order->items->map(function ($item) {
            $name = $item->product?->name ?? $item->productVariant?->product?->name ?? 'Producto';

            return [
                'title' => $name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price / 100,
                'currency_id' => 'ARS',
            ];
        })->toArray();

        $preference = $client->create([
            'items' => $items,
            'back_urls' => [
                'success' => route('checkout.result', ['status' => 'approved']),
                'failure' => route('checkout.result', ['status' => 'rejected']),
                'pending' => route('checkout.result', ['status' => 'pending']),
            ],
            'auto_return' => 'approved',
            'external_reference' => (string) $order->id,
            'notification_url' => url('/webhook/mercadopago'),
        ]);

        return [
            'id' => $preference->id,
            'init_point' => $preference->init_point,
            'sandbox_init_point' => $preference->sandbox_init_point,
        ];
    }

    public function handleWebhook(array $data): void
    {
        if (($data['type'] ?? '') !== 'payment') {
            return;
        }

        $paymentId = $data['data']['id'] ?? null;
        if (! $paymentId) {
            return;
        }

        $client = new \MercadoPago\Client\Payment\PaymentClient;
        $payment = $client->get($paymentId);

        $order = Order::find($payment->external_reference);
        if (! $order) {
            return;
        }

        $order->payment_status = match ($payment->status) {
            'approved' => PaymentStatusEnum::PAID,
            'pending', 'in_process' => PaymentStatusEnum::PENDING,
            'rejected', 'cancelled' => PaymentStatusEnum::FAILED,
            default => $order->payment_status,
        };

        $order->saveQuietly();
    }
}
