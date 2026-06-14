<?php

namespace Tests\Feature;

use App\Enums\OrderMailStepEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Mail\OrderStatusMail;
use App\Models\Order;
use App\Services\MercadoPagoService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OrderStatusMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_mail_is_queued_when_mercadopago_marks_order_paid(): void
    {
        Mail::fake();
        $order = $this->makeOrder();

        $this->makeService()->handleWebhookPayload(
            $order,
            $this->paymentPayload($order, 'approved'),
        );

        Mail::assertQueued(OrderStatusMail::class, function (OrderStatusMail $mail) use ($order) {
            return $mail->step === OrderMailStepEnum::APPROVED
                && $mail->hasTo($order->email);
        });
    }

    public function test_approved_mail_is_not_resent_on_repeated_paid_webhook(): void
    {
        Mail::fake();
        $order = $this->makeOrder();
        $service = $this->makeService();
        $payload = $this->paymentPayload($order, 'approved');

        $service->handleWebhookPayload($order, $payload);
        $service->handleWebhookPayload($order, $payload);

        Mail::assertQueuedCount(1);
    }

    public function test_pending_webhook_does_not_queue_mail(): void
    {
        Mail::fake();
        $order = $this->makeOrder();

        $this->makeService()->handleWebhookPayload(
            $order,
            $this->paymentPayload($order, 'pending'),
        );

        Mail::assertNothingQueued();
    }

    public function test_approved_mail_is_queued_when_admin_sets_payment_paid(): void
    {
        Mail::fake();
        $order = $this->makeOrder();

        $order->update(['payment_status' => PaymentStatusEnum::PAID]);

        Mail::assertQueued(OrderStatusMail::class, fn (OrderStatusMail $mail) => $mail->step === OrderMailStepEnum::APPROVED);
    }

    /**
     * @return array<string, array{0: OrderStatusEnum, 1: OrderMailStepEnum}>
     */
    public static function notifyingStatuses(): array
    {
        return [
            'en preparación' => [OrderStatusEnum::IN_PREPARATION, OrderMailStepEnum::IN_PREPARATION],
            'enviado' => [OrderStatusEnum::SHIPPING, OrderMailStepEnum::SHIPPING],
            'listo para retiro' => [OrderStatusEnum::READY_PICKUP, OrderMailStepEnum::READY_PICKUP],
            'entregado' => [OrderStatusEnum::DELIVERED, OrderMailStepEnum::DELIVERED],
        ];
    }

    #[DataProvider('notifyingStatuses')]
    public function test_status_change_queues_matching_step_mail(OrderStatusEnum $status, OrderMailStepEnum $expected): void
    {
        Mail::fake();
        $order = $this->makeOrder();

        $order->update(['status' => $status]);

        Mail::assertQueued(OrderStatusMail::class, fn (OrderStatusMail $mail) => $mail->step === $expected);
    }

    public function test_ready_pickup_and_shipping_steps_have_distinct_wording(): void
    {
        // El bug: aunque el pedido sea de envío a domicilio, el paso de retiro
        // debe decir "listo para retirar" (el texto lo decide el paso, no delivery_type).
        $order = $this->makeOrder(['delivery_type' => 'shipping']);

        $pickupHtml = (new OrderStatusMail($order, OrderMailStepEnum::READY_PICKUP))->render();
        $shippingHtml = (new OrderStatusMail($order, OrderMailStepEnum::SHIPPING))->render();

        // Cuerpo.
        $this->assertStringContainsString('punto de retiro', $pickupHtml);
        $this->assertStringNotContainsString('domicilio', $pickupHtml);
        $this->assertStringContainsString('domicilio', $shippingHtml);

        // Barra de progreso: etiqueta "Retiro" vs "Envío".
        $this->assertStringContainsString('Retiro', $pickupHtml);
        $this->assertStringNotContainsString('Retiro', $shippingHtml);
        $this->assertStringContainsString('Envío', $shippingHtml);

        // Asunto.
        $this->assertStringContainsString('listo para retirar', OrderMailStepEnum::READY_PICKUP->subject($order));
        $this->assertStringContainsString('en camino', OrderMailStepEnum::SHIPPING->subject($order));

        $this->assertNotSame($pickupHtml, $shippingHtml);
    }

    public function test_non_notifying_status_change_does_not_queue_mail(): void
    {
        Mail::fake();
        $order = $this->makeOrder();

        $order->update(['status' => OrderStatusEnum::PREPARATED]);

        Mail::assertNothingQueued();
    }

    public function test_no_mail_when_order_has_no_email(): void
    {
        Mail::fake();
        $order = $this->makeOrder(['email' => '']);

        $order->update(['status' => OrderStatusEnum::DELIVERED]);

        Mail::assertNothingQueued();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeOrder(array $overrides = []): Order
    {
        return Order::create([
            'email' => 'test@example.com',
            'postal_code' => '7607',
            'delivery_type' => 'shipping',
            'subtotal' => 10000,
            'total' => 10000,
            'shipping_cost' => 0,
            'discount_amount' => 0,
            'status' => OrderStatusEnum::PENDING,
            'payment_status' => PaymentStatusEnum::PENDING,
            ...$overrides,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function paymentPayload(Order $order, string $status): array
    {
        return [
            'id' => 555,
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
}
