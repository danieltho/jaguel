<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatusEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informacion del Pedido')
                    ->columns(2)
                    ->schema([
                        TextInput::make('order_number')
                            ->label('Numero de Pedido')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit'),

                        TextInput::make('email')
                            ->label('Email de Contacto')
                            ->email()
                            ->required(),

                        TextInput::make('postal_code')
                            ->label('Codigo Postal')
                            ->maxLength(20),

                        Select::make('customer_id')
                            ->label('Cliente')
                            ->relationship('customer', 'email')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Select::make('status')
                            ->label('Estado')
                            ->options(OrderStatusEnum::class)
                            ->default(OrderStatusEnum::PENDING)
                            ->required(),
                    ]),

                Section::make('Totales')
                    ->columns(2)
                    ->visibleOn('edit')
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->prefix('ARS')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('shipping_cost')
                            ->label('Gastos de Envio')
                            ->numeric()
                            ->prefix('ARS')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('total')
                            ->label('Total')
                            ->numeric()
                            ->prefix('ARS')
                            ->disabled()
                            ->dehydrated(false),

                        Select::make('coupon_id')
                            ->label('Cupon')
                            ->relationship('coupon', 'code')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),
            ]);
    }
}
