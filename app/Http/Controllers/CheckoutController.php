<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private MercadoPagoService $mercadoPagoService,
    ) {}

    public function showAddress(Request $request): Response
    {
        $customer = $request->user('customer');
        $items = $this->cartService->getItems();

        if (empty($items)) {
            return redirect()->route('cart.index');
        }

        return Inertia::render('Checkout/Address', [
            'customer' => $customer->only(
                'firstname', 'lastname', 'email', 'phone',
                'address', 'address_number', 'department', 'city', 'state', 'country_iso'
            ),
            'summary' => $this->getSummary(),
        ]);
    }

    public function saveAddress(Request $request)
    {
        $validated = $request->validate([
            'address' => 'required|string|max:255',
            'address_number' => 'required|string|max:20',
            'department' => 'nullable|string|max:50',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
        ]);

        session(['checkout_address' => $validated]);

        return redirect()->route('checkout.shipping');
    }

    public function showShipping(): Response
    {
        if (! session('checkout_address')) {
            return redirect()->route('checkout.address');
        }

        return Inertia::render('Checkout/Shipping', [
            'shippingOptions' => [
                ['id' => 'free', 'name' => 'Correo Argentino', 'price' => 0, 'days' => '5-7 días hábiles'],
                ['id' => 'standard', 'name' => 'Envío a Domicilio', 'price' => 899000, 'days' => '3-5 días hábiles'],
                ['id' => 'express', 'name' => 'Envío a Domicilio Prioritario', 'price' => 1099000, 'days' => '1-2 días hábiles'],
            ],
            'summary' => $this->getSummary(),
        ]);
    }

    public function saveShipping(Request $request)
    {
        $request->validate([
            'shipping_option' => 'required|string|in:free,standard,express',
        ]);

        $shippingPrices = [
            'free' => 0,
            'standard' => 899000,
            'express' => 1099000,
        ];

        session(['checkout_shipping' => [
            'option' => $request->string('shipping_option'),
            'cost' => $shippingPrices[$request->string('shipping_option')] ?? 0,
        ]]);

        return redirect()->route('checkout.payment');
    }

    public function showPayment(Request $request): Response
    {
        if (! session('checkout_address') || ! session('checkout_shipping')) {
            return redirect()->route('checkout.address');
        }

        return Inertia::render('Checkout/Payment', [
            'summary' => $this->getSummary(),
            'mercadoPagoPublicKey' => config('services.mercadopago.public_key'),
        ]);
    }

    public function placeOrder(Request $request)
    {
        $customer = $request->user('customer');
        $items = $this->cartService->getItems();
        $address = session('checkout_address');
        $shipping = session('checkout_shipping');

        if (empty($items) || ! $address || ! $shipping) {
            return redirect()->route('cart.index');
        }

        $subtotal = $this->cartService->getSubtotal();
        $coupon = $this->cartService->getCoupon();
        $discountAmount = $coupon['discount'] ?? 0;
        $shippingCost = $shipping['cost'] ?? 0;
        $total = max(0, $subtotal - $discountAmount + $shippingCost);

        $order = Order::create([
            'customer_id' => $customer->id,
            'email' => $customer->email,
            'postal_code' => $address['postal_code'] ?? '',
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'shipping_cost' => $shippingCost,
            'total' => $total,
            'coupon_id' => $coupon['id'] ?? null,
        ]);

        foreach ($items as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'product_variant_id' => $item['variant_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ]);
        }

        // Apply coupon usage
        if ($coupon) {
            $couponModel = \App\Models\Coupon::find($coupon['id']);
            if ($couponModel) {
                app(CouponService::class)->applyCoupon($couponModel, $order, $customer);
            }
        }

        // Create MercadoPago preference
        try {
            $preference = $this->mercadoPagoService->createPreference($order);

            // Clear cart and checkout session
            $this->cartService->clear();
            session()->forget(['checkout_address', 'checkout_shipping']);

            return Inertia::location($preference['init_point']);
        } catch (\Exception $e) {
            // Fallback: redirect to result page if MP fails
            $this->cartService->clear();
            session()->forget(['checkout_address', 'checkout_shipping']);

            return redirect()->route('checkout.result', [
                'status' => 'pending',
                'order' => $order->id,
            ]);
        }
    }

    public function success(Request $request): Response
    {
        return Inertia::render('Checkout/Result', [
            'status' => $request->query('status', 'pending'),
            'orderId' => $request->query('order'),
        ]);
    }

    public function webhook(Request $request)
    {
        $this->mercadoPagoService->handleWebhook($request->all());

        return response()->json(['ok' => true]);
    }

    private function getSummary(): array
    {
        $subtotal = $this->cartService->getSubtotal();
        $coupon = $this->cartService->getCoupon();
        $discount = $coupon['discount'] ?? 0;
        $shipping = session('checkout_shipping.cost', 0);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'total' => max(0, $subtotal - $discount + $shipping),
        ];
    }
}
