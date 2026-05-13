<?php

namespace App\Jobs;

use App\Services\MercadoPagoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMpWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $backoff = 30;

    public function __construct(
        public string $type,
        public ?string $dataId,
    ) {}

    public function handle(MercadoPagoService $service): void
    {
        if ($this->type !== 'payment' || ! $this->dataId) {
            return;
        }

        $payment = $service->getPayment($this->dataId);
        if (! $payment) {
            $this->release(60);

            return;
        }

        $orderId = $payment['external_reference'] ?? null;
        if (! $orderId) {
            return;
        }

        $order = \App\Models\Order::find($orderId);
        if (! $order) {
            return;
        }

        $service->handleWebhookPayload($order, $payment);
    }
}
