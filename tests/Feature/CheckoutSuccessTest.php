<?php

namespace Tests\Feature;

use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use App\Services\MercadoPagoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CheckoutSuccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_success_does_not_call_mp_for_non_mp_order(): void
    {
        $order = $this->makeOrder(mpPreferenceId: null);

        $mock = Mockery::mock(MercadoPagoService::class);
        $mock->shouldNotReceive('syncOrderFromMp');
        $this->app->instance(MercadoPagoService::class, $mock);

        $this->get('/checkout/resultado?order='.$order->id)->assertOk();
    }

    public function test_success_calls_mp_when_order_has_mp_preference(): void
    {
        $order = $this->makeOrder(mpPreferenceId: 'mp-pref-123');

        $mock = Mockery::mock(MercadoPagoService::class);
        $mock->shouldReceive('syncOrderFromMp')
            ->once()
            ->with(Mockery::on(fn (Order $o) => $o->id === $order->id))
            ->andReturn(true);
        $this->app->instance(MercadoPagoService::class, $mock);

        $this->get('/checkout/resultado?order='.$order->id)->assertOk();
    }

    private function makeOrder(?string $mpPreferenceId): Order
    {
        return Order::create([
            'email' => 'test@example.com',
            'postal_code' => '7607',
            'subtotal' => 10000,
            'total' => 10000,
            'shipping_cost' => 0,
            'discount_amount' => 0,
            'payment_status' => PaymentStatusEnum::PENDING,
            'mp_preference_id' => $mpPreferenceId,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
