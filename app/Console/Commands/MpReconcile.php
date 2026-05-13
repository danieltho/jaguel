<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use App\Services\MercadoPagoService;
use Illuminate\Console\Command;

class MpReconcile extends Command
{
    protected $signature = 'mp:reconcile
                            {--minutes=30 : Edad mínima en minutos para considerar una orden pendiente}
                            {--limit=50 : Máximo de órdenes a procesar por ejecución}';

    protected $description = 'Reconsulta en Mercado Pago las órdenes con pago pendiente para cubrir webhooks perdidos';

    public function handle(MercadoPagoService $service): int
    {
        $minutes = (int) $this->option('minutes');
        $limit = (int) $this->option('limit');

        $orders = Order::query()
            ->where('payment_status', PaymentStatusEnum::PENDING)
            ->where('created_at', '<=', now()->subMinutes($minutes))
            ->whereNotNull('mp_preference_id')
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No hay órdenes para reconciliar.');

            return self::SUCCESS;
        }

        $this->info("Reconciliando {$orders->count()} órdenes...");

        foreach ($orders as $order) {
            $paymentId = $order->mp_payment_id;
            if (! $paymentId) {
                $this->line("Order #{$order->id}: sin mp_payment_id, se omite");
                continue;
            }

            $payment = $service->getPayment($paymentId);
            if (! $payment) {
                $this->warn("Order #{$order->id}: no se pudo obtener pago {$paymentId}");
                continue;
            }

            $service->handleWebhookPayload($order, $payment);
            $this->line("Order #{$order->id}: estado MP={$payment['status']}");
        }

        return self::SUCCESS;
    }
}
