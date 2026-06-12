<?php

namespace App\Services;

use App\Enums\MpPaymentStatusEnum;
use App\Enums\OrderMailStepEnum;
use App\Enums\PaymentStatusEnum;
use App\Jobs\ProcessMpWebhook;
use App\Mail\OrderStatusMail;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Payment\PaymentRefundClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\Net\MPSearchRequest;

class MercadoPagoService
{
    public function __construct(private SettingsService $settings) {}

    public function isConfigured(): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        return $this->resolveAccessToken() !== null;
    }

    /**
     * Devuelve el access token de Mercado Pago solo si parece válido. Ignora
     * valores vacíos y el placeholder por defecto (`your_token` del .env), y
     * exige el prefijo oficial (`TEST-` o `APP_USR-`). Así evitamos ofrecer el
     * pago con tarjeta con credenciales que Mercado Pago va a rechazar.
     */
    public function resolveAccessToken(): ?string
    {
        $token = $this->settings->get('mercadopago', 'access_token')
            ?? config('services.mercadopago.access_token');

        if (! is_string($token)) {
            return null;
        }

        $token = trim($token);

        if ($token === '') {
            return null;
        }

        if (! str_starts_with($token, 'TEST-') && ! str_starts_with($token, 'APP_USR-')) {
            return null;
        }

        return $token;
    }

    public function isEnabled(): bool
    {
        $value = $this->settings->get('mercadopago', 'enabled');

        if ($value === null) {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function createPreference(Order $order): array
    {
        $client = new PreferenceClient;

        $items = $order->items->map(function ($item) {
            $name = $item->product?->name ?? $item->productVariant?->product?->name ?? 'Producto';

            return [
                'title' => $name,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'currency_id' => 'ARS',
            ];
        })->toArray();

        if ($order->shipping_cost > 0) {
            $items[] = [
                'title' => 'Envio - '.($order->shipping_method ?? 'Standard'),
                'quantity' => 1,
                'unit_price' => (float) $order->shipping_cost,
                'currency_id' => 'ARS',
            ];
        }

        if ($order->discount_amount > 0) {
            $items[] = [
                'title' => 'Descuento',
                'quantity' => 1,
                'unit_price' => -((float) $order->discount_amount),
                'currency_id' => 'ARS',
            ];
        }

        $backUrls = $this->buildBackUrls();

        $preferenceData = [
            'items' => $items,
            'external_reference' => (string) $order->id,
            'metadata' => ['order_id' => $order->id],
            'payer' => $this->buildPayer($order),
            'back_urls' => $backUrls,
        ];

        // auto_return y notification_url solo dependen de que las URLs sean
        // HTTPS públicas (lo que MP exige). No las atamos al entorno de Laravel:
        // así funcionan en cualquier server con dominio HTTPS, incluso si quedó
        // APP_ENV=local. En localhost (http) no se envían y MP no las rechaza.
        $hasPublicBackUrls = str_starts_with($backUrls['success'], 'https://');

        if ($hasPublicBackUrls) {
            $preferenceData['auto_return'] = 'all';
        }

        $webhookUrl = $this->buildWebhookUrl();
        if (str_starts_with($webhookUrl, 'https://')) {
            $preferenceData['notification_url'] = $webhookUrl;
        }

        Log::info('MercadoPago preference request', ['order_id' => $order->id]);

        $preference = $client->create($preferenceData);

        $order->forceFill(['mp_preference_id' => $preference->id])->saveQuietly();

        // init_point funciona con credenciales TEST y de producción; usamos
        // sandbox_init_point solo como respaldo (en la API actual suele venir
        // vacío). Si ambos están vacíos, abortamos con un error explícito en
        // lugar de devolver una URL vacía que no redirige a ningún lado.
        $redirectUrl = $preference->init_point ?: $preference->sandbox_init_point;

        if (empty($redirectUrl)) {
            throw new \RuntimeException(
                'Mercado Pago no devolvió una URL de pago (init_point) para la orden '.$order->id
            );
        }

        return [
            'id' => $preference->id,
            'init_point' => $redirectUrl,
        ];
    }

    public function handleWebhook(Request $request): bool
    {
        if (! $this->verifySignature($request)) {
            Log::warning('MercadoPago webhook: invalid signature', [
                'ip' => $request->ip(),
            ]);

            return false;
        }

        $type = (string) ($request->input('type') ?? $request->query('type') ?? '');
        $dataId = $request->input('data.id') ?? $request->query('data.id');

        ProcessMpWebhook::dispatch($type, $dataId ? (string) $dataId : null);

        return true;
    }

    public function getPayment(string $paymentId): ?array
    {
        return $this->fetchPayment($paymentId);
    }

    public function syncOrderFromMp(Order $order): bool
    {
        try {
            $client = new PaymentClient;
            $searchRequest = new MPSearchRequest(5, 0, [
                'external_reference' => (string) $order->id,
                'sort' => 'date_created',
                'criteria' => 'desc',
            ]);
            $results = $client->search($searchRequest);

            $payments = $results->results ?? [];
            if (empty($payments)) {
                return false;
            }

            $latest = $payments[0];
            $payment = json_decode(json_encode($latest), true);

            $this->handleWebhookPayload($order, $payment);

            return true;
        } catch (MPApiException $e) {
            Log::error('MercadoPago syncOrderFromMp failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
                'response' => $e->getApiResponse()?->getContent(),
            ]);

            return false;
        }
    }

    public function handleWebhookPayload(Order $order, array $payment): void
    {
        DB::transaction(function () use ($order, $payment) {
            $locked = Order::lockForUpdate()->find($order->id);
            if (! $locked) {
                return;
            }

            $this->recordTransaction($locked, $payment);
            $this->syncOrderStatus($locked, $payment);
        });
    }

    public function refundPayment(string $paymentId, ?int $amount = null): array
    {
        $client = new PaymentRefundClient;
        $id = (int) $paymentId;

        try {
            $refund = $amount
                ? $client->refund($id, (float) $amount)
                : $client->refundTotal($id);

            return [
                'id' => $refund->id,
                'amount' => $refund->amount,
                'status' => $refund->status,
            ];
        } catch (MPApiException $e) {
            Log::error('MercadoPago refund failed', [
                'payment_id' => $paymentId,
                'message' => $e->getMessage(),
                'response' => $e->getApiResponse()?->getContent(),
            ]);

            throw $e;
        }
    }

    private function fetchPayment(string $paymentId): ?array
    {
        try {
            $payment = (new PaymentClient)->get($paymentId);

            return json_decode(json_encode($payment), true);
        } catch (MPApiException $e) {
            Log::error('MercadoPago getPayment failed', [
                'payment_id' => $paymentId,
                'message' => $e->getMessage(),
                'response' => $e->getApiResponse()?->getContent(),
            ]);

            return null;
        }
    }

    private function recordTransaction(Order $order, array $payment): void
    {
        $mpStatus = MpPaymentStatusEnum::tryFrom($payment['status'] ?? '');
        if (! $mpStatus) {
            return;
        }

        $existing = PaymentTransaction::where('mp_payment_id', $payment['id'])->first();

        if ($existing && $existing->mp_status === $mpStatus) {
            return;
        }

        $attributes = [
            'mp_preference_id' => $order->mp_preference_id,
            'mp_status' => $mpStatus->value,
            'mp_status_detail' => $payment['status_detail'] ?? null,
            'mp_payment_type' => $payment['payment_type_id'] ?? null,
            'mp_payment_method' => $payment['payment_method_id'] ?? null,
            'installments' => $payment['installments'] ?? null,
            'transaction_amount' => (int) round($payment['transaction_amount'] ?? 0),
            'currency' => $payment['currency_id'] ?? 'ARS',
            'payer_email' => $payment['payer']['email'] ?? null,
            'raw_response' => $payment,
            'processed_at' => now(),
        ];

        if ($existing) {
            $existing->update($attributes);

            return;
        }

        PaymentTransaction::create($attributes + [
            'order_id' => $order->id,
            'provider' => 'mercadopago',
            'mp_payment_id' => $payment['id'],
        ]);
    }

    private function syncOrderStatus(Order $order, array $payment): void
    {
        $mpStatus = MpPaymentStatusEnum::tryFrom($payment['status'] ?? '');
        if (! $mpStatus) {
            return;
        }

        $newPaymentStatus = $mpStatus->toPaymentStatus();
        $wasPaid = $order->payment_status === PaymentStatusEnum::PAID;

        $order->forceFill([
            'mp_payment_id' => (string) $payment['id'],
            'payment_status' => $newPaymentStatus,
        ])->saveQuietly();

        // saveQuietly() no dispara el OrderObserver: notificamos la compra aquí,
        // solo en la transición a PAGADO para no reenviar en webhooks repetidos.
        if (! $wasPaid && $newPaymentStatus === PaymentStatusEnum::PAID && filled($order->email)) {
            Mail::to($order->email)->queue(new OrderStatusMail($order, OrderMailStepEnum::APPROVED));
        }
    }

    private function buildBackUrls(): array
    {
        $base = rtrim((string) $this->settings->get('mercadopago', 'back_url_base'), '/');

        if ($base === '') {
            return [
                'success' => route('checkout.result', ['status' => 'approved']),
                'failure' => route('checkout.result', ['status' => 'rejected']),
                'pending' => route('checkout.result', ['status' => 'pending']),
            ];
        }

        $path = route('checkout.result', [], false);

        return [
            'success' => $base.$path.'?status=approved',
            'failure' => $base.$path.'?status=rejected',
            'pending' => $base.$path.'?status=pending',
        ];
    }

    private function buildWebhookUrl(): string
    {
        $base = rtrim((string) $this->settings->get('mercadopago', 'back_url_base'), '/');

        if ($base !== '') {
            return $base.'/webhook/mercadopago';
        }

        return url('/webhook/mercadopago');
    }

    private function buildPayer(Order $order): array
    {
        $payer = ['email' => $order->email];

        if ($order->recipient_firstname) {
            $payer['name'] = $order->recipient_firstname;
        }
        if ($order->recipient_lastname) {
            $payer['surname'] = $order->recipient_lastname;
        }
        if ($order->recipient_phone) {
            $payer['phone'] = ['number' => $order->recipient_phone];
        }
        if ($order->document_number) {
            $payer['identification'] = [
                'type' => $order->document_type ?? 'DNI',
                'number' => $order->document_number,
            ];
        }

        return $payer;
    }

    private function verifySignature(Request $request): bool
    {
        $secret = $this->settings->get('mercadopago', 'webhook_secret')
            ?? config('services.mercadopago.webhook_secret');

        // No secret configured: skip verification (dev) but warn
        if (! $secret) {
            if (! app()->environment('local')) {
                Log::warning('MercadoPago webhook_secret not configured in production');
            }

            return true;
        }

        $signatureHeader = $request->header('x-signature');
        $requestId = $request->header('x-request-id');
        $dataId = $request->query('data.id') ?? ($request->input('data.id'));

        if (! $signatureHeader || ! $requestId || ! $dataId) {
            return false;
        }

        $parts = collect(explode(',', $signatureHeader))
            ->mapWithKeys(function ($part) {
                [$k, $v] = array_pad(explode('=', trim($part), 2), 2, null);

                return [trim($k) => trim((string) $v)];
            });

        $ts = $parts->get('ts');
        $hash = $parts->get('v1');

        if (! $ts || ! $hash) {
            return false;
        }

        $manifest = "id:{$dataId};request-id:{$requestId};ts:{$ts};";
        $expected = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($expected, $hash);
    }
}
