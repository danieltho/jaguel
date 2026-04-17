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

        if ($order->shipping_cost > 0) {
            $items[] = [
                'title' => 'Envio - '.($order->shipping_method ?? 'Standard'),
                'quantity' => 1,
                'unit_price' => $order->shipping_cost / 100,
                'currency_id' => 'ARS',
            ];
        }

        $preferenceData = [
            'items' => $items,
            'back_urls' => [
                'success' => route('checkout.result', ['status' => 'approved']),
                'failure' => route('checkout.result', ['status' => 'rejected']),
                'pending' => route('checkout.result', ['status' => 'pending']),
            ],
            'external_reference' => (string) $order->id,
        ];

        // auto_return requires HTTPS back_urls — skip in local
        // Use 'all' so MercadoPago auto-redirects on approved, rejected, and pending
        if (! app()->environment('local')) {
            $preferenceData['auto_return'] = 'all';
        }

        // MercadoPago rejects non-HTTPS/localhost notification URLs
        $webhookUrl = url('/webhook/mercadopago');
        if (! app()->environment('local') && str_starts_with($webhookUrl, 'https://')) {
            $preferenceData['notification_url'] = $webhookUrl;
        }

        \Log::info('MercadoPago preference request', ['data' => $preferenceData]);

        $preference = $client->create($preferenceData);

        // Use sandbox_init_point in local, init_point in production
        $redirectUrl = app()->environment('local')
            ? $preference->sandbox_init_point
            : $preference->init_point;

        return [
            'id' => $preference->id,
            'init_point' => $redirectUrl,
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
