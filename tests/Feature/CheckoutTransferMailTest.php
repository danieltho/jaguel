<?php

namespace Tests\Feature;

use App\Enums\OrderMailStepEnum;
use App\Enums\PaymentMethodTypeEnum;
use App\Mail\OrderStatusMail;
use App\Models\PaymentMethod;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutTransferMailTest extends TestCase
{
    use RefreshDatabase;

    private const EMAIL = 'comprador@example.com';

    public function test_transfer_checkout_queues_pending_mail(): void
    {
        Mail::fake();
        $product = Product::factory()->create();
        $transfer = $this->paymentMethod(PaymentMethodTypeEnum::BANK_TRANSFER);

        $response = $this->withSession($this->checkoutSession($product))
            ->post('/checkout/pago', ['payment_method_id' => $transfer->id]);

        $response->assertRedirect();
        Mail::assertQueued(OrderStatusMail::class, function (OrderStatusMail $mail) {
            return $mail->step === OrderMailStepEnum::PENDING && $mail->hasTo(self::EMAIL);
        });
    }

    public function test_non_transfer_checkout_does_not_queue_pending_mail(): void
    {
        Mail::fake();
        $product = Product::factory()->create();
        $cash = $this->paymentMethod(PaymentMethodTypeEnum::CASH_SHOWROOM);

        $this->withSession($this->checkoutSession($product))
            ->post('/checkout/pago', ['payment_method_id' => $cash->id]);

        Mail::assertNothingQueued();
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
