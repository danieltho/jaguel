<?php

namespace Tests\Feature;

use App\Enums\PaymentMethodTypeEnum;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CheckoutReceiptUploadTest extends TestCase
{
    use RefreshDatabase;

    private const EMAIL = 'comprador@example.com';

    public function test_transfer_checkout_attaches_uploaded_receipt(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create();
        $transfer = $this->paymentMethod(PaymentMethodTypeEnum::BANK_TRANSFER);

        $this->withSession($this->checkoutSession($product))
            ->post('/checkout/pago', [
                'payment_method_id' => $transfer->id,
                'payment_receipt' => UploadedFile::fake()->create('comprobante.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();

        $order = Order::latest('id')->firstOrFail();
        $this->assertCount(1, $order->getMedia('payment_receipt'));
    }

    public function test_transfer_checkout_works_without_receipt(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create();
        $transfer = $this->paymentMethod(PaymentMethodTypeEnum::BANK_TRANSFER);

        $this->withSession($this->checkoutSession($product))
            ->post('/checkout/pago', ['payment_method_id' => $transfer->id])
            ->assertRedirect();

        $order = Order::latest('id')->firstOrFail();
        $this->assertCount(0, $order->getMedia('payment_receipt'));
    }

    public function test_invalid_receipt_type_is_rejected(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create();
        $transfer = $this->paymentMethod(PaymentMethodTypeEnum::BANK_TRANSFER);

        $this->withSession($this->checkoutSession($product))
            ->post('/checkout/pago', [
                'payment_method_id' => $transfer->id,
                'payment_receipt' => UploadedFile::fake()->create('nota.txt', 10, 'text/plain'),
            ])
            ->assertSessionHasErrors('payment_receipt');

        $this->assertSame(0, Order::count());
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
