<?php

namespace Tests\Feature;

use App\Enums\PaymentMethodTypeEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Services\MercadoPagoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class CheckoutResumePaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_unsigned_url_is_rejected(): void
    {
        $order = $this->makeOrder(PaymentStatusEnum::PENDING, PaymentMethodTypeEnum::CREDIT_CARD);

        $this->get('/checkout/orden/'.$order->id.'/pagar')->assertForbidden();
    }

    public function test_credit_card_pending_order_redirects_to_mercado_pago(): void
    {
        $order = $this->makeOrder(PaymentStatusEnum::PENDING, PaymentMethodTypeEnum::CREDIT_CARD);

        $mock = Mockery::mock(MercadoPagoService::class);
        $mock->shouldReceive('isConfigured')->andReturn(true);
        $mock->shouldReceive('createPreference')
            ->once()
            ->with(Mockery::on(fn (Order $o) => $o->id === $order->id))
            ->andReturn(['id' => 'pref-1', 'init_point' => 'https://mp.example/checkout/init']);
        $this->app->instance(MercadoPagoService::class, $mock);

        $this->get(URL::signedRoute('checkout.pay', ['order' => $order->id]))
            ->assertRedirect('https://mp.example/checkout/init');
    }

    public function test_non_card_order_does_not_call_mercado_pago(): void
    {
        $order = $this->makeOrder(PaymentStatusEnum::PENDING, PaymentMethodTypeEnum::BANK_TRANSFER);

        $mock = Mockery::mock(MercadoPagoService::class);
        $mock->shouldReceive('isConfigured')->andReturn(true);
        $mock->shouldNotReceive('createPreference');
        $this->app->instance(MercadoPagoService::class, $mock);

        $this->get(URL::signedRoute('checkout.pay', ['order' => $order->id]))
            ->assertRedirect(route('checkout.result', ['status' => 'pending', 'order' => $order->id]));
    }

    public function test_already_paid_order_redirects_to_result_as_approved(): void
    {
        $order = $this->makeOrder(PaymentStatusEnum::PAID, PaymentMethodTypeEnum::CREDIT_CARD);

        $mock = Mockery::mock(MercadoPagoService::class);
        $mock->shouldNotReceive('createPreference');
        $this->app->instance(MercadoPagoService::class, $mock);

        $this->get(URL::signedRoute('checkout.pay', ['order' => $order->id]))
            ->assertRedirect(route('checkout.result', ['status' => 'approved', 'order' => $order->id]));
    }

    public function test_card_order_redirects_to_result_when_mp_not_configured(): void
    {
        $order = $this->makeOrder(PaymentStatusEnum::PENDING, PaymentMethodTypeEnum::CREDIT_CARD);

        $mock = Mockery::mock(MercadoPagoService::class);
        $mock->shouldReceive('isConfigured')->andReturn(false);
        $mock->shouldNotReceive('createPreference');
        $this->app->instance(MercadoPagoService::class, $mock);

        $this->get(URL::signedRoute('checkout.pay', ['order' => $order->id]))
            ->assertRedirect(route('checkout.result', ['status' => 'pending', 'order' => $order->id]));
    }

    private function makeOrder(PaymentStatusEnum $status, PaymentMethodTypeEnum $methodType): Order
    {
        $method = PaymentMethod::create([
            'type' => $methodType,
            'title' => $methodType->getLabel(),
            'is_active' => true,
            'sort_order' => 1,
        ]);

        return Order::create([
            'email' => 'test@example.com',
            'postal_code' => '7607',
            'subtotal' => 10000,
            'total' => 10000,
            'shipping_cost' => 0,
            'discount_amount' => 0,
            'payment_status' => $status,
            'payment_method_id' => $method->id,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
