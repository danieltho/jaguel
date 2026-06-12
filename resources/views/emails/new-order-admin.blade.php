@php
    /** @var \App\Models\Order $order */

    $money = fn ($value) => '$'.number_format((int) $value, 0, ',', '.');

    $isPickup = $order->delivery_type === 'pickup';

    $recipientName = trim(($order->recipient_firstname ?? '').' '.($order->recipient_lastname ?? ''));
    $paymentLabel = $order->paymentMethod?->title ?? 'Sin especificar';
    $paymentStatusLabel = $order->payment_status?->getLabel() ?? '-';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva compra #{{ $order->order_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f6f6f6; margin: 0; padding: 24px;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden;">
        <tr>
            <td style="padding: 24px 32px 8px;">
                <span style="display: inline-block; padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; background: #A1A389; color: #ffffff;">
                    Nueva compra
                </span>
            </td>
        </tr>

        <tr>
            <td style="padding: 8px 32px 32px;">
                <h1 style="margin: 0 0 16px; font-size: 20px; color: #1a1a1a;">Pedido #{{ $order->order_number }}</h1>

                <p style="margin: 0 0 24px; font-size: 15px; color: #555; line-height: 1.5;">
                    Se realizó una nueva compra en la tienda. Estos son los datos del pedido.
                </p>

                {{-- Datos del cliente --}}
                <h2 style="margin: 24px 0 8px; font-size: 15px; color: #1a1a1a;">Cliente</h2>
                <p style="margin: 0; font-size: 14px; color: #555; line-height: 1.5;">
                    @if ($recipientName !== ''){{ $recipientName }}<br>@endif
                    {{ $order->email }}@if ($order->recipient_phone)<br>{{ $order->recipient_phone }}@endif
                </p>

                {{-- Pago --}}
                <h2 style="margin: 24px 0 8px; font-size: 15px; color: #1a1a1a;">Pago</h2>
                <p style="margin: 0; font-size: 14px; color: #555; line-height: 1.5;">
                    Método: {{ $paymentLabel }}<br>
                    Estado: {{ $paymentStatusLabel }}
                </p>

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

                <p style="margin: 24px 0 0; font-size: 12px; color: #999;">
                    Este mail es un envío automático para el equipo de la tienda.
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
