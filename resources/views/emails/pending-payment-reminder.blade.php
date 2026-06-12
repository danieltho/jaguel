@php
    /** @var \App\Models\Order $order */
    /** @var int $reminder */

    $name = trim(($order->recipient_firstname ?? '').' '.($order->recipient_lastname ?? ''));
    $name = $name !== '' ? $name : 'Hola';

    $money = fn ($value) => '$'.number_format((int) $value, 0, ',', '.');

    $isFinal = $reminder >= 2;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu pedido #{{ $order->order_number }} sigue pendiente de pago</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f6f6f6; margin: 0; padding: 24px;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden;">
        <tr>
            <td style="padding: 24px 32px 8px;">
                <span style="display: inline-block; padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; background: #A1A389; color: #ffffff;">
                    Pago pendiente
                </span>
            </td>
        </tr>

        <tr>
            <td style="padding: 8px 32px 32px;">
                <p style="margin: 0 0 16px; font-size: 16px; color: #1a1a1a;">Hola {{ $name }},</p>

                @if ($isFinal)
                    <p style="margin: 0 0 16px; font-size: 15px; color: #555; line-height: 1.5;">
                        Te escribimos por tu pedido <strong>#{{ $order->order_number }}</strong>, que todavía figura pendiente de pago. ¡no te preocupes!
                    </p>
                    <p style="margin: 0 0 24px; font-size: 15px; color: #555; line-height: 1.5;">
                        Si querés completarlo o tenés cualquier duda, estamos para ayudarte. Escribinos y lo resolvemos juntos en un minuto.
                    </p>
                @else
                    <p style="margin: 0 0 16px; font-size: 15px; color: #555; line-height: 1.5;">
                        Notamos que tu pedido <strong>#{{ $order->order_number }}</strong> quedó pendiente de pago. Quizás se te cortó el proceso o quedó para más tarde, ¡puede pasar!
                    </p>
                    <p style="margin: 0 0 24px; font-size: 15px; color: #555; line-height: 1.5;">
                        Estamos para ayudarte a terminarlo. Si tenés alguna duda o necesitás una mano, escribinos y lo completamos juntos.
                    </p>
                @endif

                {{-- Resumen breve del pedido --}}
                <h2 style="margin: 24px 0 8px; font-size: 15px; color: #1a1a1a;">Tu pedido</h2>
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="font-size: 14px; color: #555;">
                    <tr>
                        <td style="padding: 4px 0;">Pedido</td>
                        <td align="right" style="padding: 4px 0;">#{{ $order->order_number }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0 0; font-weight: 700; color: #1a1a1a; border-top: 1px solid #f1f1ee;">Total</td>
                        <td align="right" style="padding: 8px 0 0; font-weight: 700; color: #1a1a1a; border-top: 1px solid #f1f1ee;">{{ $money($order->total) }}</td>
                    </tr>
                </table>

                <p style="margin: 24px 0 0; font-size: 14px; color: #555; line-height: 1.5;">
                    ¿Necesitás ayuda? Escribinos por WhatsApp o respondé a este mail y te acompañamos para terminar el proceso. Encontrás nuestros datos de contacto al pie de este mensaje.
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
