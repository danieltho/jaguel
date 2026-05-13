<?php

namespace Tests\Feature;

use App\Enums\PaymentStatusEnum;
use App\Jobs\ProcessMpWebhook;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\MercadoPagoService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class MercadoPagoWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test-webhook-secret-xyz';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.mercadopago.webhook_secret' => self::SECRET]);
        // Force production-like behavior for signature checks (local skips when no secret).
        $this->app['env'] = 'testing';
    }

    public function test_webhook_with_valid_signature_dispatches_job(): void
    {
        Queue::fake();

        $dataId = '123456789';
        $requestId = 'req-abc';
        $ts = (string) time();
        $manifest = "id:{$dataId};request-id:{$requestId};ts:{$ts};";
        $signature = hash_hmac('sha256', $manifest, self::SECRET);

        $response = $this->withHeaders([
            'x-signature' => "ts={$ts},v1={$signature}",
            'x-request-id' => $requestId,
        ])->postJson('/webhook/mercadopago?data.id='.$dataId, [
            'type' => 'payment',
            'data' => ['id' => $dataId],
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);
        Queue::assertPushed(ProcessMpWebhook::class, function ($job) use ($dataId) {
            return $job->type === 'payment' && $job->dataId === $dataId;
        });
    }

    public function test_webhook_with_invalid_signature_returns_401(): void
    {
        Queue::fake();

        $response = $this->withHeaders([
            'x-signature' => 'ts=123,v1=wrong-hash',
            'x-request-id' => 'req-xxx',
        ])->postJson('/webhook/mercadopago?data.id=999', [
            'type' => 'payment',
            'data' => ['id' => '999'],
        ]);

        $response->assertStatus(401);
        Queue::assertNothingPushed();
    }

    public function test_webhook_without_signature_headers_returns_401(): void
    {
        Queue::fake();

        $response = $this->postJson('/webhook/mercadopago', [
            'type' => 'payment',
            'data' => ['id' => '999'],
        ]);

        $response->assertStatus(401);
        Queue::assertNothingPushed();
    }

    public function test_handle_webhook_payload_is_idempotent(): void
    {
        $order = $this->makeOrder();

        $payload = $this->paymentPayload($order, status: 'approved', paymentId: 555);

        $service = $this->makeService();
        $service->handleWebhookPayload($order, $payload);
        $service->handleWebhookPayload($order, $payload);

        $this->assertSame(1, PaymentTransaction::where('mp_payment_id', '555')->count());
        $this->assertSame(PaymentStatusEnum::PAID, $order->fresh()->payment_status);
    }

    public function test_handle_webhook_payload_updates_existing_on_status_change(): void
    {
        $order = $this->makeOrder();
        $service = $this->makeService();

        $service->handleWebhookPayload($order, $this->paymentPayload($order, 'pending', 777));
        $this->assertSame(PaymentStatusEnum::PENDING, $order->fresh()->payment_status);

        $service->handleWebhookPayload($order, $this->paymentPayload($order, 'approved', 777));
        $this->assertSame(PaymentStatusEnum::PAID, $order->fresh()->payment_status);

        $this->assertSame(1, PaymentTransaction::where('mp_payment_id', '777')->count());
    }

    private function makeOrder(): Order
    {
        return Order::create([
            'email' => 'test@example.com',
            'postal_code' => '7607',
            'subtotal' => 10000,
            'total' => 10000,
            'shipping_cost' => 0,
            'discount_amount' => 0,
            'payment_status' => PaymentStatusEnum::PENDING,
        ]);
    }

    private function paymentPayload(Order $order, string $status, int $paymentId): array
    {
        return [
            'id' => $paymentId,
            'status' => $status,
            'status_detail' => 'accredited',
            'external_reference' => (string) $order->id,
            'transaction_amount' => 100.00,
            'currency_id' => 'ARS',
            'payment_type_id' => 'credit_card',
            'payment_method_id' => 'visa',
            'installments' => 1,
            'payer' => ['email' => 'buyer@example.com'],
        ];
    }

    private function makeService(): MercadoPagoService
    {
        return new MercadoPagoService(app(SettingsService::class));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
