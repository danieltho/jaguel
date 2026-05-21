<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use App\Services\MercadoPagoService;
use Filament\Actions\ActionGroup;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Illuminate\Database\Eloquent\Collection;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
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
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('order_number')
                    ->label('N Pedido')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('payment_status')
                    ->label('Pago')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Estado Pedido')
                    ->badge()
                    ->color(fn (OrderStatusEnum $state): string => $state->color()),
                TextColumn::make('paymentMethod.title')
                    ->label('Metodo Pago')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('customer.email')
                    ->label('Cliente')
                    ->placeholder('Sin asignar')
                    ->searchable(),
                TextColumn::make('postal_code')
                    ->label('CP')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('coupon.name')
                    ->label('Cupon')
                    ->badge()
                    ->color('success')
                    ->placeholder('-'),
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('ARS')
                    ->sortable(),
                TextColumn::make('shipping_cost')
                    ->label('Envio')
                    ->money('ARS')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('ARS')
                    ->summarize(Sum::make()->money('ARS'))
                    ->sortable(),
                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('payment_status')
                    ->label('Estado de Pago')
                    ->options(PaymentStatusEnum::class),
                SelectFilter::make('status')
                    ->label('Estado Pedido')
                    ->options(OrderStatusEnum::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('shippingLabel')
                        ->label('Etiqueta de envío')
                        ->icon(Heroicon::OutlinedPrinter)
                        ->color('gray')
                        ->url(fn (Order $record) => route('orders.shipping-label', $record))
                        ->openUrlInNewTab(),
                    Action::make('syncMpStatus')
                        ->label('Sincronizar con MP')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->visible(fn (Order $record) => filled($record->mp_preference_id)
                            || filled($record->mp_payment_id))
                        ->action(function (Order $record) {
                            $service = app(MercadoPagoService::class);

                            if (filled($record->mp_payment_id)) {
                                $payment = $service->getPayment($record->mp_payment_id);
                                if ($payment) {
                                    $service->handleWebhookPayload($record, $payment);

                                    Notification::make()
                                        ->title('Estado actualizado: '.$record->fresh()->payment_status->getLabel())
                                        ->success()
                                        ->send();

                                    return;
                                }
                            }

                            $found = $service->syncOrderFromMp($record);

                            if (! $found) {
                                Notification::make()
                                    ->title('No se encontraron pagos en Mercado Pago')
                                    ->body('Es posible que el comprador aún no haya pagado o que el link aún no se haya usado.')
                                    ->warning()
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->title('Estado actualizado: '.$record->fresh()->payment_status->getLabel())
                                ->success()
                                ->send();
                        }),
                    Action::make('paymentLink')
                        ->label('Link de pago MP')
                        ->icon(Heroicon::OutlinedLink)
                        ->color('info')
                        ->visible(fn (Order $record) => $record->paymentMethod?->type === \App\Enums\PaymentMethodTypeEnum::CREDIT_CARD
                            && in_array($record->payment_status, [PaymentStatusEnum::PENDING, PaymentStatusEnum::FAILED], true))
                        ->modalHeading('Link de pago Mercado Pago')
                        ->modalDescription('Copia este link y envialo al comprador. Caduca en 24 horas.')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Cerrar')
                        ->fillForm(function (Order $record) {
                            try {
                                $preference = app(MercadoPagoService::class)->createPreference($record);

                                if ($record->payment_status === PaymentStatusEnum::FAILED) {
                                    $record->update(['payment_status' => PaymentStatusEnum::PENDING]);
                                }

                                return ['init_point' => $preference['init_point']];
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Error generando link')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();

                                return ['init_point' => ''];
                            }
                        })
                        ->schema([
                            TextInput::make('init_point')
                                ->label('Link de pago')
                                ->readOnly()
                                ->suffixAction(
                                    Action::make('copy')
                                        ->icon(Heroicon::OutlinedClipboardDocument)
                                        ->action(function ($state, $livewire) {
                                            $livewire->js('navigator.clipboard.writeText('.json_encode($state).')');
                                        })
                                ),
                        ]),
                    Action::make('refund')
                        ->label('Reembolsar')
                        ->icon(Heroicon::OutlinedReceiptRefund)
                        ->color('danger')
                        ->visible(fn (Order $record) => $record->payment_status === PaymentStatusEnum::PAID
                            && filled($record->mp_payment_id))
                        ->schema([
                            TextInput::make('amount')
                                ->label('Monto a reembolsar (ARS)')
                                ->numeric()
                                ->minValue(1)
                                ->helperText('Dejar vacío para reembolso total.')
                                ->default(fn (Order $record) => $record->total),
                            TextInput::make('confirmation')
                                ->label('Escribí REEMBOLSAR para confirmar')
                                ->required()
                                ->rule('in:REEMBOLSAR'),
                        ])
                        ->action(function (Order $record, array $data) {
                            $service = app(MercadoPagoService::class);

                            try {
                                $amount = ! empty($data['amount'])
                                    ? (int) round((float) $data['amount'])
                                    : null;

                                $service->refundPayment($record->mp_payment_id, $amount);

                                $payment = $service->getPayment($record->mp_payment_id);
                                if ($payment) {
                                    $service->handleWebhookPayload($record, $payment);
                                }

                                Notification::make()
                                    ->title('Reembolso solicitado')
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Error en el reembolso')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('printShippingLabels')
                        ->label('Imprimir etiquetas de envío')
                        ->icon(Heroicon::OutlinedPrinter)
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records, $livewire) {
                            $ids = $records->pluck('id')->implode(',');
                            $url = route('orders.shipping-label.bulk', ['ids' => $ids]);
                            $livewire->js("window.open(" . json_encode($url) . ", '_blank')");
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
