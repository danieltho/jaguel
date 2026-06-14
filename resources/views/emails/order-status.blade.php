@php
    use App\Enums\OrderMailStepEnum;

    /** @var \App\Models\Order $order */
    /** @var \App\Enums\OrderMailStepEnum $step */

    $name = trim(($order->recipient_firstname ?? '').' '.($order->recipient_lastname ?? ''));
    $name = $name !== '' ? $name : 'Hola';

    $money = fn ($value) => '$'.number_format((int) $value, 0, ',', '.');

    $isPickup = $order->delivery_type === 'pickup';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $step->subject($order) }}</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f6f6f6; margin: 0; padding: 24px;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden;">
        {{-- Barra de progreso (5 estados) --}}
        <tr>
            <td style="padding: 24px 24px 8px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                    <tr>
                        @foreach (OrderMailStepEnum::steps() as $s)
                            @php
                                $active = $s === $step->progressStep();
                                $isPickupStep = $isPickup || $step === OrderMailStepEnum::READY_PICKUP;
                                $stepLabel = ($s === OrderMailStepEnum::SHIPPING && $isPickupStep) ? 'Retiro' : $s->label();
                            @endphp
                            <td align="center" style="padding: 6px 4px;">
                                <span style="display: inline-block; padding: 6px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; {{ $active ? 'background: #A1A389; color: #ffffff;' : 'background: #f1f1ee; color: #b5b5ad;' }}">
                                    {{ $stepLabel }}
                                </span>
                            </td>
                        @endforeach
                    </tr>
                </table>
            </td>
        </tr>

        {{-- Cuerpo --}}
        <tr>
            <td style="padding: 16px 32px 32px;">
                <p style="margin: 0 0 16px; font-size: 16px; color: #1a1a1a;">Hola {{ $name }},</p>

                @switch($step)
                    @case(OrderMailStepEnum::PENDING)
                        <p style="margin: 0 0 24px; font-size: 15px; color: #555; line-height: 1.5;">
                            Hemos recibido tu pedido y estamos a la espera que se acredite del pago.
                        </p>
                        @break

                    @case(OrderMailStepEnum::APPROVED)
                        <p style="margin: 0 0 8px; font-size: 15px; color: #555; line-height: 1.5;">¡Gracias por tu compra!</p>
                        <p style="margin: 0 0 24px; font-size: 15px; color: #555; line-height: 1.5;">
                            Una vez que tu pedido esté listo te enviaremos un mail notificando.
                        </p>
                        @break

                    @case(OrderMailStepEnum::IN_PREPARATION)
                        <p style="margin: 0 0 24px; font-size: 15px; color: #555; line-height: 1.5;">
                            Tu pedido está en proceso de preparación. Pronto te enviaremos otro mail cuando esté listo para {{ $isPickup ? 'retirar' : 'enviar' }}.
                        </p>
                        @break

                    @case(OrderMailStepEnum::SHIPPING)
                        <p style="margin: 0 0 24px; font-size: 15px; color: #555; line-height: 1.5;">
                            ¡Tu pedido está listo! Pronto llegará a tu domicilio.
                        </p>
                        @break

                    @case(OrderMailStepEnum::READY_PICKUP)
                        <p style="margin: 0 0 24px; font-size: 15px; color: #555; line-height: 1.5;">
                            Ya puedes dirigirte al punto de retiro seleccionado para recoger el pedido.
                        </p>
                        @break

                    @case(OrderMailStepEnum::DELIVERED)
                        <p style="margin: 0 0 24px; font-size: 15px; color: #555; line-height: 1.5;">
                            @if ($isPickup)
                                ¡Ya retiraste tu pedido! Gracias por confiar en nosotros, esperamos volver a verte pronto.
                            @else
                                ¡Tu pedido fue entregado! Gracias por confiar en nosotros, esperamos volver a verte pronto.
                            @endif
                        </p>
                        @break
                @endswitch

                {{-- Detalle completo: solo en los mails de pendiente y aprobado --}}
                @if (in_array($step, [OrderMailStepEnum::PENDING, OrderMailStepEnum::APPROVED], true))
                    {{-- Modo de envío --}}
                    <h2 style="margin: 24px 0 8px; font-size: 15px; color: #1a1a1a;">Modo de envío</h2>
                    <p style="margin: 0 0 4px; font-size: 14px; color: #555; line-height: 1.5;">
                        {{ $isPickup ? 'Retiro en local' : 'Envío a domicilio' }}
                    </p>
                    @unless ($isPickup)
                        @if ($order->recipient_address)
                            <p style="margin: 0; font-size: 14px; color: #555; line-height: 1.5;">
                                {{ $order->recipient_address }}@if ($order->recipient_department), {{ $order->recipient_department }}@endif<br>
                                {{ trim(collect([$order->recipient_city, $order->recipient_state, $order->postal_code])->filter()->implode(', ')) }}
                            </p>
                        @endif
                    @endunless

                    {{-- Listado del producto --}}
                    <h2 style="margin: 24px 0 8px; font-size: 15px; color: #1a1a1a;">Listado del producto</h2>
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="font-size: 14px; color: #555;">
                        @foreach ($order->items as $item)
                            @php
                                $variant = $item->productVariant;
                                $attrs = collect([$variant?->color?->name, $variant?->size?->name])->filter()->implode(' / ');
                            @endphp
                            <tr>
                                <td style="padding: 6px 0; border-bottom: 1px solid #f1f1ee;">
                                    {{ $item->product?->name ?? 'Producto' }}@if ($attrs) <span style="color: #999;">({{ $attrs }})</span>@endif
                                    <br><span style="color: #999;">x{{ $item->quantity }}</span>
                                </td>
                                <td align="right" style="padding: 6px 0; border-bottom: 1px solid #f1f1ee; white-space: nowrap;">
                                    {{ $money($item->unit_price * $item->quantity) }}
                                </td>
                            </tr>
                        @endforeach
                    </table>

                    {{-- Resumen del pedido --}}
                    <h2 style="margin: 24px 0 8px; font-size: 15px; color: #1a1a1a;">Resumen del pedido</h2>
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="font-size: 14px; color: #555;">
                        <tr>
                            <td style="padding: 4px 0;">Subtotal</td>
                            <td align="right" style="padding: 4px 0;">{{ $money($order->subtotal) }}</td>
                        </tr>
                        @if ($order->discount_amount > 0)
                            <tr>
                                <td style="padding: 4px 0;">Descuento</td>
                                <td align="right" style="padding: 4px 0;">- {{ $money($order->discount_amount) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td style="padding: 4px 0;">Envío</td>
                            <td align="right" style="padding: 4px 0;">{{ $order->shipping_cost > 0 ? $money($order->shipping_cost) : 'Gratis' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0 0; font-weight: 700; color: #1a1a1a; border-top: 1px solid #f1f1ee;">Total</td>
                            <td align="right" style="padding: 8px 0 0; font-weight: 700; color: #1a1a1a; border-top: 1px solid #f1f1ee;">{{ $money($order->total) }}</td>
                        </tr>
                    </table>
                @endif

                <p style="margin: 24px 0 0; font-size: 12px; color: #999;">
                    Este mail es un envío automático, por favor no lo respondas.
                </p>
            </td>
        </tr>

        {{-- Footer de marca --}}
        <tr>
            <td>
                @include('emails.partials.footer')
            </td>
        </tr>
    </table>
</body>
</html>
