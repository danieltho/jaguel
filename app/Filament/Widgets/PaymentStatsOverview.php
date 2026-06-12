<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaymentStatsOverview extends BaseWidget
{
    protected ?string $heading = 'Pagos';

    protected function getStats(): array
    {
        $approvedToday = Order::query()
            ->where('payment_status', PaymentStatusEnum::PAID)
            ->whereDate('updated_at', today());

        $pendingCount = Order::query()
            ->where('payment_status', PaymentStatusEnum::PENDING)
            ->count();

        $failedLast24h = Order::query()
            ->where('payment_status', PaymentStatusEnum::FAILED)
            ->where('updated_at', '>=', now()->subDay())
            ->count();

        $monthRevenue = (int) Order::query()
            ->where('payment_status', PaymentStatusEnum::PAID)
            ->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total');

        return [
            Stat::make('Aprobados hoy', $approvedToday->count())
                ->description($this->formatMoney((int) (clone $approvedToday)->sum('total')))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Pendientes', $pendingCount)
                ->description('Esperando confirmación')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Rechazados 24h', $failedLast24h)
                ->description('Últimas 24 horas')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('Ingresos del mes', $this->formatMoney($monthRevenue))
                ->description(now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }

    private function formatMoney(int $amount): string
    {
        return '$ '.number_format($amount, 2, ',', '.');
    }
}
