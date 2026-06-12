<?php

namespace Tests\Feature;

use App\Enums\PaymentMethodTypeEnum;
use App\Models\PaymentMethod;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutPaymentMethodsTest extends TestCase
{
    use RefreshDatabase;

    private const EMAIL = 'comprador@example.com';

    public function test_mercado_pago_is_offered_when_configured(): void
    {
        config(['services.mercadopago.access_token' => 'TEST-token']);
        $product = Product::factory()->create();
        $this->paymentMethod(PaymentMethodTypeEnum::BANK_TRANSFER);

        $this->withSession($this->checkoutSession($product))
            ->get('/checkout/pago')
            ->assertInertia(fn ($page) => $page
                ->component('Checkout/Payment')
                ->where('paymentMethods', fn ($methods) => collect($methods)
                    ->contains(fn ($m) => $m['type'] === PaymentMethodTypeEnum::CREDIT_CARD->value)));
    }

    public function test_mercado_pago_is_not_offered_when_not_configured(): void
    {
        config(['services.mercadopago.access_token' => null]);
        // Aunque exista un registro credit_card activo, no debe ofrecerse si MP no está configurado.
        $this->paymentMethod(PaymentMethodTypeEnum::CREDIT_CARD);
        $product = Product::factory()->create();
        $this->paymentMethod(PaymentMethodTypeEnum::BANK_TRANSFER);

        $this->withSession($this->checkoutSession($product))
            ->get('/checkout/pago')
            ->assertInertia(fn ($page) => $page
                ->component('Checkout/Payment')
                ->where('paymentMethods', fn ($methods) => collect($methods)
                    ->doesntContain(fn ($m) => $m['type'] === PaymentMethodTypeEnum::CREDIT_CARD->value)));
    }

    public function test_mercado_pago_is_offered_even_if_record_is_inactive(): void
    {
        config(['services.mercadopago.access_token' => 'TEST-token']);
        $product = Product::factory()->create();
        PaymentMethod::create([
            'type' => PaymentMethodTypeEnum::CREDIT_CARD,
            'title' => 'Tarjeta',
            'is_active' => false,
            'sort_order' => 1,
        ]);

        $this->withSession($this->checkoutSession($product))
            ->get('/checkout/pago')
            ->assertInertia(fn ($page) => $page
                ->component('Checkout/Payment')
                ->where('paymentMethods', fn ($methods) => collect($methods)
                    ->contains(fn ($m) => $m['type'] === PaymentMethodTypeEnum::CREDIT_CARD->value)));
    }

    private function paymentMethod(PaymentMethodTypeEnum $type): PaymentMethod
    {
        return PaymentMethod::create([
            'type' => $type,
            'title' => $type->getLabel(),
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function checkoutSession(Product $product): array
    {
        return [
            'cart' => [
                'item-1' => [
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'quantity' => 2,
                ],
            ],
            'checkout_contact' => [
                'email' => self::EMAIL,
                'delivery_type' => 'shipping',
            ],
            'checkout_delivery' => [
                'postal_code' => '7607',
                'shipping_cost' => 0,
                'shipping_method' => 'correo_argentino',
                'shipping_method_id' => null,
            ],
            'checkout_recipient' => [
                'firstname' => 'Juan',
                'lastname' => 'Pérez',
                'phone' => '2231234567',
                'address' => 'Calle 1 123',
                'department' => null,
                'city' => 'Miramar',
                'state' => 'Buenos Aires',
                'document_number' => '30123456',
                'document_type' => 'DNI',
                'wants_factura_a' => false,
            ],
        ];
    }
}
