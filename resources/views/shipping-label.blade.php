<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>
        @if ($orders->count() === 1)
            Etiqueta de envío - Pedido #{{ $orders->first()->order_number }}
        @else
            Etiquetas de envío ({{ $orders->count() }} pedidos)
        @endif
    </title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 24px;
            color: #111;
            background: #f4f4f4;
        }
        .sheet {
            max-width: 720px;
            margin: 0 auto 24px;
            background: #fff;
            padding: 32px;
            border: 1px solid #ddd;
            page-break-after: always;
        }
        .sheet:last-child {
            page-break-after: auto;
        }
        .toolbar {
            max-width: 720px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }
        .toolbar button {
            background: #111;
            color: #fff;
            border: none;
            padding: 10px 16px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 4px;
        }
        .toolbar button.secondary {
            background: #fff;
            color: #111;
            border: 1px solid #111;
        }
        h1 {
            margin: 0 0 4px;
            font-size: 22px;
        }
        .meta {
            font-size: 12px;
            color: #555;
            margin-bottom: 24px;
        }
        .block {
            border: 1px solid #111;
            padding: 16px;
            margin-bottom: 16px;
        }
        .block h2 {
            margin: 0 0 8px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #555;
        }
        .name {
            font-size: 20px;
            font-weight: bold;
            margin: 0 0 6px;
        }
        .line {
            font-size: 15px;
            margin: 2px 0;
        }
        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: 24px;
            font-size: 12px;
            color: #555;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .sheet { border: none; max-width: 100%; padding: 16px; margin: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" class="secondary" onclick="window.close()">Cerrar</button>
        <button type="button" onclick="window.print()">Imprimir</button>
    </div>

    @foreach ($orders as $order)
        <div class="sheet">
            <h1>Etiqueta de envío</h1>
            <p class="meta">
                Pedido #{{ $order->order_number }}
                &middot;
                {{ $order->created_at->format('d/m/Y H:i') }}
            </p>

            <div class="block">
                <h2>Destinatario</h2>
                <p class="name">
                    {{ trim(($order->recipient_firstname ?? '') . ' ' . ($order->recipient_lastname ?? '')) ?: '—' }}
                </p>
                @if ($order->recipient_address)
                    <p class="line">{{ $order->recipient_address }}{{ $order->recipient_department ? ', ' . $order->recipient_department : '' }}</p>
                @endif
                @if ($order->recipient_city || $order->recipient_state || $order->postal_code)
                    <p class="line">
                        {{ collect([$order->recipient_city, $order->recipient_state, $order->postal_code ? 'CP ' . $order->postal_code : null])
                            ->filter()->implode(', ') }}
                    </p>
                @endif
                @if ($order->recipient_phone)
                    <p class="line">Tel: {{ $order->recipient_phone }}</p>
                @endif
                @if ($order->email)
                    <p class="line">Email: {{ $order->email }}</p>
                @endif
            </div>

            <div class="block">
                <h2>Remitente</h2>
                <p class="name">{{ $sender['name'] ?? '—' }}</p>
                @if (!empty($sender['address']))
                    <p class="line">{{ $sender['address'] }}</p>
                @endif
                @if (!empty($sender['city']) || !empty($sender['state']) || !empty($sender['postal_code']))
                    <p class="line">
                        {{ collect([
                            $sender['city'] ?? null,
                            $sender['state'] ?? null,
                            !empty($sender['postal_code']) ? 'CP ' . $sender['postal_code'] : null,
                        ])->filter()->implode(', ') }}
                    </p>
                @endif
                @if (!empty($sender['phone']))
                    <p class="line">Tel: {{ $sender['phone'] }}</p>
                @endif
            </div>

            <div class="footer">
                <span>Pedido #{{ $order->order_number }}</span>
                <span>{{ $order->items->sum('quantity') }} producto(s)</span>
            </div>
        </div>
    @endforeach

    <script>
        window.addEventListener('load', () => setTimeout(() => window.print(), 300));
    </script>
</body>
</html>
