<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatusEnum;
use App\Mail\PendingPaymentReminderMail;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendPendingPaymentReminders extends Command
{
    protected $signature = 'orders:payment-reminders
                            {--first-hours=12 : Horas desde la creación para el primer aviso}
                            {--second-hours=24 : Horas desde la creación para el segundo aviso}
                            {--max-age-hours=72 : Antigüedad máxima de la orden para enviar avisos}
                            {--limit=100 : Máximo de órdenes a procesar por aviso}';

    protected $description = 'Avisa al comprador cuando su pedido sigue pendiente de pago (aviso 1 a las 12 hs, aviso 2 a las 24 hs)';

    public function handle(): int
    {
        $firstHours = (int) $this->option('first-hours');
        $secondHours = (int) $this->option('second-hours');
        $maxAgeHours = (int) $this->option('max-age-hours');
        $limit = (int) $this->option('limit');

        $minAge = now()->subHours($maxAgeHours);

        $first = $this->process(
            reminder: 1,
            stampColumn: 'payment_reminder_1_sent_at',
            createdBefore: now()->subHours($firstHours),
            minAge: $minAge,
            limit: $limit,
        );

        $second = $this->process(
            reminder: 2,
            stampColumn: 'payment_reminder_2_sent_at',
            createdBefore: now()->subHours($secondHours),
            minAge: $minAge,
            limit: $limit,
        );

        $this->info("Avisos enviados — aviso 1: {$first}, aviso 2: {$second}.");

        return self::SUCCESS;
    }

    private function process(int $reminder, string $stampColumn, \DateTimeInterface $createdBefore, \DateTimeInterface $minAge, int $limit): int
    {
        $orders = Order::query()
            ->where('payment_status', PaymentStatusEnum::PENDING)
            ->whereNull($stampColumn)
            ->where('created_at', '<=', $createdBefore)
            ->where('created_at', '>=', $minAge)
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        $sent = 0;

        foreach ($orders as $order) {
            if (! filled($order->email)) {
                $order->forceFill([$stampColumn => now()])->saveQuietly();
                continue;
            }

            Mail::to($order->email)->queue(new PendingPaymentReminderMail($order, $reminder));
            $order->forceFill([$stampColumn => now()])->saveQuietly();
            $sent++;
        }

        return $sent;
    }
}
