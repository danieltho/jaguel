<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatusEnum;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingPaymentsTable extends BaseWidget
{
    protected static ?string $heading = 'Pagos pendientes con más de 1 hora';

    protected int|string|array $columnSpan = '50%';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->where('payment_status', PaymentStatusEnum::PENDING)
                    ->where('created_at', '<=', now()->subHour())
                    ->latest('created_at')
            )
            ->paginated([10])
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i'),
                TextColumn::make('order_number')
                    ->label('N° Pedido'),
                TextColumn::make('email')
                    ->label('Email'),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('ARS'),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Abrir')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Order $record) => OrderResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
