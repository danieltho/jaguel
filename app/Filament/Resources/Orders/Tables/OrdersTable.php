<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatusEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('N Pedido')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (OrderStatusEnum $state): string => $state->color()),
                TextColumn::make('customer.email')
                    ->label('Cliente')
                    ->placeholder('Sin asignar')
                    ->searchable(),
                TextColumn::make('postal_code')
                    ->label('CP')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('coupon.code')
                    ->label('Cupon')
                    ->badge()
                    ->color('success')
                    ->placeholder('-'),
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('ARS')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('shipping_cost')
                    ->label('Envio')
                    ->money('ARS')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('ARS')
                    ->summarize(Sum::make()->money('ARS', 100))
                    ->sortable(),
                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items'),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(OrderStatusEnum::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
