<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', Customer::count())
                ->description('Usuarios registrados')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            Stat::make('Total Pedidos', Order::count())
                ->description('Pedidos registrados')
                ->color('success'),
        ];
    }
}
