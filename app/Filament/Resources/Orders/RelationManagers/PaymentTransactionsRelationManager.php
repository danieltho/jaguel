<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Enums\MpPaymentStatusEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'paymentTransactions';

    protected static ?string $title = 'Transacciones Mercado Pago';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('mp_payment_id')
                    ->label('Payment ID')
                    ->copyable(),

                TextColumn::make('mp_status')
                    ->label('Estado')
                    ->badge(),

                TextColumn::make('mp_payment_method')
                    ->label('Método')
                    ->placeholder('-'),

                TextColumn::make('installments')
                    ->label('Cuotas')
                    ->placeholder('-'),

                TextColumn::make('transaction_amount')
                    ->label('Monto')
                    ->money('ARS'),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
