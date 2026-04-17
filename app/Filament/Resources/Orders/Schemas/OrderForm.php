<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\PaymentMethod;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                            ->label('Estado Pedido')
                            ->options(OrderStatusEnum::class)
                            ->default(OrderStatusEnum::PENDING)
                            ->required(),
                    ]),

                Section::make('Tipo de Entrega')
                    ->columns(2)
                    ->visibleOn('edit')
                    ->schema([
                        Select::make('delivery_type')
                            ->label('Tipo de Entrega')
                            ->options([
                                'pickup' => 'Retiro en local',
                                'shipping' => 'Envío a domicilio',
                            ])
                            ->disabled()
                            ->dehydrated(false),

                        Select::make('shipping_method')
                            ->label('Método de Envío')
                            ->options([
                                'punto_retiro' => 'Punto de retiro',
                                'correo_argentino' => 'Correo Argentino',
                            ])
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn ($record) => $record?->delivery_type === 'shipping'),
                    ]),

                Section::make('Datos del Destinatario')
                    ->columns(2)
                    ->visibleOn('edit')
                    ->schema([
                        TextInput::make('recipient_firstname')
                            ->label('Nombre')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('recipient_lastname')
                            ->label('Apellido')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('recipient_phone')
                            ->label('Teléfono')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('recipient_address')
                            ->label('Dirección')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('recipient_department')
                            ->label('Departamento')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('recipient_city')
                            ->label('Ciudad')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('recipient_state')
                            ->label('Provincia')
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                Section::make('Facturación')
                    ->columns(2)
                    ->visibleOn('edit')
                    ->schema([
                        TextInput::make('document_number')
                            ->label('Número de Documento')
                            ->disabled()
                            ->dehydrated(false),

                        Select::make('document_type')
                            ->label('Tipo de Documento')
                            ->options([
                                'DNI' => 'DNI',
                                'CUIT' => 'CUIT',
                            ])
                            ->disabled()
                            ->dehydrated(false),

                        Toggle::make('wants_factura_a')
                            ->label('Solicita Factura A')
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                Section::make('Informacion de Pago')
                    ->columns(2)
                    ->schema([
                        Select::make('payment_status')
                            ->label('Estado de Pago')
                            ->options(PaymentStatusEnum::class)
                            ->default(PaymentStatusEnum::PENDING)
                            ->required(),

                        Select::make('payment_method_id')
                            ->label('Metodo de Pago')
                            ->relationship('paymentMethod', 'title')
                            ->searchable()
                            ->preload()
                            ->nullable(),
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

                        Select::make('coupon_id')
                            ->label('Cupon')
                            ->relationship('coupon', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        TextInput::make('discount_amount')
                            ->label('Descuento')
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
                    ]),
            ]);
    }
}
