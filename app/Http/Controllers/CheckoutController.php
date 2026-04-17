<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethodTypeEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentMethod;
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

    // Step 1: Contact (email + delivery type)
    public function showContact(Request $request): Response
    {
        $items = $this->cartService->getItems();

        if (empty($items)) {
            return redirect()->route('cart.index');
        }

        $customer = $request->user('customer');

        return Inertia::render('Checkout/Contact', [
            'customer' => $customer
                ? $customer->only('firstname', 'lastname', 'email', 'phone')
                : ['firstname' => '', 'lastname' => '', 'email' => '', 'phone' => ''],
            'summary' => $this->getSummary(),
        ]);
    }

    public function saveContact(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'delivery_type' => 'required|string|in:pickup,shipping',
        ]);

        session(['checkout_contact' => $validated]);

        // Clear downstream session if delivery type changed
        session()->forget(['checkout_delivery', 'checkout_recipient']);

        // Skip delivery step for pickup orders
        if ($validated['delivery_type'] === 'pickup') {
            session(['checkout_delivery' => [
                'postal_code' => null,
                'shipping_method' => 'punto_retiro',
                'shipping_cost' => 0,
            ]]);

            return redirect()->route('checkout.recipient');
        }

        return redirect()->route('checkout.delivery');
    }

    // Step 2: Delivery (postal code + shipping method) — skipped for pickup
    public function showDelivery(Request $request): Response
    {
        if (! session('checkout_contact')) {
            return redirect()->route('checkout.contact');
        }

        $deliveryType = session('checkout_contact.delivery_type');

        if ($deliveryType === 'pickup') {
            return redirect()->route('checkout.recipient');
        }

        $shippingOptions = [
            [
                'id' => 'punto_retiro',
                'name' => 'Punto de Retiro',
                'price' => 0,
                'description' => 'Calle 37 N° 1242, Miramar, Buenos Aires',
                'days' => 'Listo entre 3-5 días hábiles',
            ],
        ];

        if ($deliveryType === 'shipping') {
            $shippingOptions[] = [
                'id' => 'correo_argentino',
                'name' => 'Correo Argentino',
                'price' => 640000,
                'description' => 'Envío a domicilio',
                'days' => '5-7 días hábiles',
            ];
        }

        return Inertia::render('Checkout/Delivery', [
            'shippingOptions' => $shippingOptions,
            'deliveryType' => $deliveryType,
            'summary' => $this->getSummary(),
        ]);
    }

    public function saveDelivery(Request $request)
    {
        $request->validate([
            'postal_code' => 'required|string|max:10',
            'shipping_method' => 'required|string|in:punto_retiro,correo_argentino',
        ]);

        $shippingCosts = [
            'punto_retiro' => 0,
            'correo_argentino' => 640000,
        ];

        $method = $request->input('shipping_method');

        session(['checkout_delivery' => [
            'postal_code' => $request->input('postal_code'),
            'shipping_method' => $method,
            'shipping_cost' => $shippingCosts[$method] ?? 0,
        ]]);

        // Clear downstream session
        session()->forget('checkout_recipient');

        return redirect()->route('checkout.recipient');
    }

    // Step 3: Recipient (name, phone, address, billing)
    public function showRecipient(Request $request): Response
    {
        if (! session('checkout_contact') || ! session('checkout_delivery')) {
            return redirect()->route('checkout.contact');
        }

        $customer = $request->user('customer');

        return Inertia::render('Checkout/Recipient', [
            'customer' => $customer
                ? $customer->only('firstname', 'lastname', 'phone', 'document', 'address', 'address_number', 'department', 'city', 'state')
                : ['firstname' => '', 'lastname' => '', 'phone' => '', 'document' => '', 'address' => '', 'address_number' => '', 'department' => '', 'city' => '', 'state' => ''],
            'deliveryType' => session('checkout_contact.delivery_type'),
            'summary' => $this->getSummary(),
        ]);
    }

    public function saveRecipient(Request $request)
    {
        $validated = $request->validate([
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'address' => 'required|string|max:255',
            'department' => 'nullable|string|max:50',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'document_number' => 'required|string|max:20',
            'document_type' => 'nullable|string|in:DNI,CUIT',
            'wants_factura_a' => 'boolean',
        ]);

        $validated['document_type'] = $validated['document_type'] ?? 'DNI';
        $validated['wants_factura_a'] = $validated['wants_factura_a'] ?? false;

        session(['checkout_recipient' => $validated]);

        return redirect()->route('checkout.payment');
    }

    // Step 4: Payment (review + place order)
    public function showPayment(Request $request): Response
    {
        $contact = session('checkout_contact');
        $delivery = session('checkout_delivery');
        $recipient = session('checkout_recipient');

        if (! $contact || ! $delivery || ! $recipient) {
            return redirect()->route('checkout.contact');
        }

        $paymentMethods = PaymentMethod::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'type', 'title', 'subtitle', 'description', 'max_installments']);

        return Inertia::render('Checkout/Payment', [
            'contact' => $contact,
            'delivery' => $delivery,
            'recipient' => $recipient,
            'paymentMethods' => $paymentMethods,
            'summary' => $this->getSummary(),
        ]);
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        $customer = $request->user('customer');
        $items = $this->cartService->getItems();
        $contact = session('checkout_contact');
        $delivery = session('checkout_delivery');
        $recipient = session('checkout_recipient');

        if (empty($items) || ! $contact || ! $delivery || ! $recipient) {
            return redirect()->route('cart.index');
        }

        $paymentMethod = PaymentMethod::findOrFail($request->input('payment_method_id'));

        // Resolve or create customer from email
        $customer = $this->resolveCustomer($customer, $contact, $recipient);

        // Cancel any previous pending MP order from this session to avoid duplicates
        $previousOrderId = session('checkout_pending_order_id');
        if ($previousOrderId) {
            $previousOrder = Order::find($previousOrderId);
            if ($previousOrder && $previousOrder->payment_status === PaymentStatusEnum::PENDING) {
                $previousOrder->update(['payment_status' => PaymentStatusEnum::FAILED]);
            }
            session()->forget('checkout_pending_order_id');
        }

        $subtotal = $this->cartService->getSubtotal();
        $coupon = $this->cartService->getCoupon();
        $discountAmount = $coupon['discount'] ?? 0;
        $shippingCost = $delivery['shipping_cost'] ?? 0;
        $total = max(0, $subtotal - $discountAmount + $shippingCost);

        $order = Order::create([
            'customer_id' => $customer?->id,
            'email' => $contact['email'],
            'postal_code' => $delivery['postal_code'],
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'shipping_cost' => $shippingCost,
            'total' => $total,
            'coupon_id' => $coupon['id'] ?? null,
            'payment_method_id' => $paymentMethod->id,
            'delivery_type' => $contact['delivery_type'],
            'shipping_method' => $delivery['shipping_method'],
            'recipient_firstname' => $recipient['firstname'],
            'recipient_lastname' => $recipient['lastname'],
            'recipient_phone' => $recipient['phone'],
            'recipient_address' => $recipient['address'],
            'recipient_department' => $recipient['department'] ?? null,
            'recipient_city' => $recipient['city'],
            'recipient_state' => $recipient['state'],
            'document_number' => $recipient['document_number'],
            'document_type' => $recipient['document_type'] ?? 'DNI',
            'wants_factura_a' => $recipient['wants_factura_a'] ?? false,
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
        if ($coupon && $customer) {
            $couponModel = \App\Models\Coupon::find($coupon['id']);
            if ($couponModel) {
                app(CouponService::class)->applyCoupon($couponModel, $order, $customer);
            }
        }

        // For credit card: keep cart alive until MP confirms — store order ID in session
        if ($paymentMethod->type === PaymentMethodTypeEnum::CREDIT_CARD) {
            session(['checkout_pending_order_id' => $order->id]);

            return $this->redirectToMercadoPago($order);
        }

        // For other payment methods: clear immediately
        $this->clearCheckoutSession();

        return redirect()->route('checkout.result', [
            'status' => 'pending',
            'order' => $order->id,
        ]);
    }

    private function redirectToMercadoPago(Order $order)
    {
        try {
            $preference = $this->mercadoPagoService->createPreference($order);

            return Inertia::location($preference['init_point']);
        } catch (\Exception $e) {
            \Log::error('MercadoPago preference creation failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $order->id,
                'response' => method_exists($e, 'getApiResponse') ? $e->getApiResponse()?->getContent() : null,
            ]);

            return redirect()->route('checkout.result', [
                'status' => 'pending',
                'order' => $order->id,
            ]);
        }
    }

    public function success(Request $request): Response
    {
        $status = $request->query('status', 'pending');
        $orderId = $request->query('order')
            ?? $request->query('external_reference')
            ?? session('checkout_pending_order_id');

        $paymentMethodData = null;

        if ($orderId) {
            $order = Order::with('paymentMethod')->find($orderId);
            if ($order?->paymentMethod) {
                $paymentMethodData = [
                    'type' => $order->paymentMethod->type->value,
                    'title' => $order->paymentMethod->title,
                    'description' => $order->paymentMethod->description,
                ];
            }
        }

        // Clear cart and checkout session when payment went through (approved/pending)
        if (in_array($status, ['approved', 'pending'])) {
            $this->clearCheckoutSession();
        }

        return Inertia::render('Checkout/Result', [
            'status' => $status,
            'orderId' => $orderId,
            'paymentMethod' => $paymentMethodData,
        ]);
    }

    public function webhook(Request $request)
    {
        $this->mercadoPagoService->handleWebhook($request->all());

        return response()->json(['ok' => true]);
    }

    private function resolveCustomer(?Customer $authenticatedCustomer, array $contact, array $recipient): ?Customer
    {
        if ($authenticatedCustomer) {
            return $authenticatedCustomer;
        }

        $email = $contact['email'];

        return Customer::firstOrCreate(
            ['email' => $email],
            [
                'firstname' => $recipient['firstname'],
                'lastname' => $recipient['lastname'],
                'phone' => $recipient['phone'],
                'address' => $recipient['address'],
                'department' => $recipient['department'] ?? null,
                'city' => $recipient['city'],
                'state' => $recipient['state'],
            ]
        );
    }

    private function clearCheckoutSession(): void
    {
        $this->cartService->clear();
        session()->forget([
            'checkout_contact',
            'checkout_delivery',
            'checkout_recipient',
            'checkout_pending_order_id',
        ]);
    }

    private function getSummary(): array
    {
        $items = $this->cartService->getItems();
        $subtotal = $this->cartService->getSubtotal();
        $coupon = $this->cartService->getCoupon();
        $discount = $coupon['discount'] ?? 0;
        $shipping = session('checkout_delivery.shipping_cost', 0);

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'couponCode' => $coupon['code'] ?? null,
            'shipping' => $shipping,
            'total' => max(0, $subtotal - $discount + $shipping),
        ];
    }
}
