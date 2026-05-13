<?php

namespace App\Filament\Resources\PaymentTransactions;

use App\Enums\MpPaymentStatusEnum;
use App\Filament\Resources\PaymentTransactions\Pages\ListPaymentTransactions;
use App\Filament\Resources\PaymentTransactions\Pages\ViewPaymentTransaction;
use App\Models\PaymentTransaction;
use App\Services\MercadoPagoService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PaymentTransactionResource extends Resource
{
    protected static ?string $model = PaymentTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Transacciones MP';

    protected static ?string $modelLabel = 'Transacción';

    protected static ?string $pluralModelLabel = 'Transacciones';

    protected static string|null|\UnitEnum $navigationGroup = 'Ventas';

    protected static ?int $navigationSort = 30;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pago')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('mp_payment_id')
                            ->label('Payment ID')
                            ->copyable(),
                        \Filament\Infolists\Components\TextEntry::make('mp_preference_id')
                            ->label('Preference ID')
                            ->copyable(),
                        \Filament\Infolists\Components\TextEntry::make('mp_status')
                            ->label('Estado')
                            ->badge(),
                        \Filament\Infolists\Components\TextEntry::make('mp_status_detail')
                            ->label('Detalle'),
                        \Filament\Infolists\Components\TextEntry::make('mp_payment_method')
                            ->label('Método'),
                        \Filament\Infolists\Components\TextEntry::make('mp_payment_type')
                            ->label('Tipo'),
                        \Filament\Infolists\Components\TextEntry::make('installments')
                            ->label('Cuotas')
                            ->placeholder('-'),
                        \Filament\Infolists\Components\TextEntry::make('transaction_amount')
                            ->label('Monto')
                            ->money('ARS'),
                        \Filament\Infolists\Components\TextEntry::make('payer_email')
                            ->label('Email pagador'),
                        \Filament\Infolists\Components\TextEntry::make('processed_at')
                            ->label('Procesado')
                            ->dateTime(),
                    ])
                    ->columns(2),

                Section::make('Orden asociada')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('order.order_number')
                            ->label('N° Orden'),
                        \Filament\Infolists\Components\TextEntry::make('order.email')
                            ->label('Email'),
                        \Filament\Infolists\Components\TextEntry::make('order.total')
                            ->label('Total orden')
                            ->money('ARS'),
                    ])
                    ->columns(3),

                Section::make('Respuesta cruda de Mercado Pago')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('raw_response')
                            ->label('')
                            ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('order.order_number')
                    ->label('Orden')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('mp_payment_id')
                    ->label('Payment ID')
                    ->copyable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('mp_status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                TextColumn::make('mp_payment_method')
                    ->label('Método')
                    ->placeholder('-')
                    ->formatStateUsing(function ($state, PaymentTransaction $record) {
                        if (! $state) {
                            return null;
                        }

                        return $record->installments
                            ? "{$state} × {$record->installments}"
                            : $state;
                    }),

                TextColumn::make('transaction_amount')
                    ->label('Monto')
                    ->money('ARS')
                    ->sortable(),

                TextColumn::make('payer_email')
                    ->label('Email pagador')
                    ->searchable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('mp_status')
                    ->label('Estado')
                    ->options(MpPaymentStatusEnum::class),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('sync')
                    ->label('Reconsultar')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->requiresConfirmation()
                    ->modalHeading('Reconsultar estado en Mercado Pago')
                    ->modalDescription('Trae el estado actual desde la API y actualiza este registro.')
                    ->action(function (PaymentTransaction $record) {
                        $service = app(MercadoPagoService::class);
                        $payment = $service->getPayment($record->mp_payment_id);

                        if (! $payment) {
                            Notification::make()
                                ->title('No se pudo obtener el pago')
                                ->danger()
                                ->send();

                            return;
                        }

                        $service->handleWebhookPayload($record->order, $payment);

                        Notification::make()
                            ->title('Estado actualizado')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentTransactions::route('/'),
            'view' => ViewPaymentTransaction::route('/{record}'),
        ];
    }
}
