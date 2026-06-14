<?php

namespace App\Http\Controllers;

use App\Enums\OrderMailStepEnum;
use App\Enums\PaymentMethodTypeEnum;
use App\Enums\PaymentStatusEnum;
use App\Mail\NewOrderAdminMail;
use App\Mail\OrderStatusMail;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\EmailVerificationService;
use App\Services\MercadoPagoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private MercadoPagoService $mercadoPagoService,
        private EmailVerificationService $emailVerificationService,
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
            'contact' => session('checkout_contact'),
            'summary' => $this->getSummary(),
        ]);
    }

    public function saveContact(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'delivery_type' => 'required|string|in:pickup,shipping',
            'verification_code' => 'nullable|string|size:6',
        ]);

        $email = strtolower(trim($validated['email']));
        $loggedInCustomer = $request->user('customer');
        $isLoggedInWithSameEmail = $loggedInCustomer
            && strtolower($loggedInCustomer->email) === $email;

        if (! $isLoggedInWithSameEmail && ! $this->emailVerificationService->isEmailVerified($email)) {
            if (empty($validated['verification_code'])) {
                $codeMessage = 'Te enviamos un código a tu email. Ingresalo para continuar.';
                try {
                    $this->emailVerificationService->sendCode($email, $request);
                } catch (\Throwable $e) {
                    // El error del catch va al campo del CÓDIGO (no del email),
                    // así el input de verificación se sigue mostrando aunque el
                    // envío del mail falle (cooldown, rate-limit, SMTP, etc.).
                    $codeMessage = $this->emailVerificationService->hasPendingCode($email)
                        ? 'Ya te enviamos un código. Ingresalo o pedí uno nuevo cuando termine el cooldown.'
                        : $e->getMessage();
                }

                return back()
                    ->withErrors(['verification_code' => $codeMessage])
                    ->withInput()
                    ->with('verification_required', true);
            }

            try {
                $this->emailVerificationService->verify($email, $validated['verification_code']);
            } catch (\Throwable $e) {
                return back()
                    ->withErrors(['verification_code' => $e->getMessage()])
                    ->withInput()
                    ->with('verification_required', true);
            }
        }

        unset($validated['verification_code']);
        $validated['email'] = $email;

        $previousDeliveryType = session('checkout_contact.delivery_type');

        session(['checkout_contact' => $validated]);

        // Solo limpiar la info de envío (postal/método) si cambió el tipo de entrega,
        // porque depende de pickup/shipping. Los datos del destinatario (nombre,
        // dirección, etc.) se conservan: son válidos para cualquier tipo de entrega.
        if ($previousDeliveryType !== $validated['delivery_type']) {
            session()->forget('checkout_delivery');
        }

        // Skip delivery step for pickup orders
        if ($validated['delivery_type'] === 'pickup') {
            $pickup = ShippingMethod::where('is_active', true)
                ->where('delivery_type', 'pickup')
                ->orderBy('sort_order')
                ->first();

            session(['checkout_delivery' => [
                'postal_code' => null,
                'shipping_method' => $pickup?->code,
                'shipping_method_id' => $pickup?->id,
                'shipping_cost' => $pickup?->price ?? 0,
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

        // El paso "Entrega" muestra todos los métodos activos (retiro + envío),
        // para que el cliente pueda cambiar de opinión y elegir retirar.
        $methods = ShippingMethod::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $shippingOptions = $methods->map(fn (ShippingMethod $m) => [
            'id' => $m->code,
            'name' => $m->name,
            'price' => $m->price,
            'description' => $m->description,
            'days' => $m->days_label,
        ])->values()->all();

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
            'shipping_method' => 'required|string|exists:shipping_methods,code',
        ]);

        $method = ShippingMethod::where('code', $request->input('shipping_method'))
            ->where('is_active', true)
            ->firstOrFail();

        session(['checkout_delivery' => [
            'postal_code' => $request->input('postal_code'),
            'shipping_method' => $method->code,
            'shipping_method_id' => $method->id,
            'shipping_cost' => $method->price,
        ]]);

        return redirect()->route('checkout.recipient');
    }

    // Step 3: Recipient (name, phone, address, billing)
    public function showRecipient(Request $request): Response
    {
        if (! session('checkout_contact') || ! session('checkout_delivery')) {
            return redirect()->route('checkout.contact');
        }

        $customer = $request->user('customer');
        $saved = session('checkout_recipient');

        // Prellenar con lo que el cliente ya ingresó (si volvió a editar pasos previos),
        // y si no, con los datos del cliente autenticado.
        if ($saved) {
            $prefill = [
                'firstname' => $saved['firstname'] ?? '',
                'lastname' => $saved['lastname'] ?? '',
                'phone' => $saved['phone'] ?? '',
                'document' => $saved['document_number'] ?? '',
                'address' => $saved['address'] ?? '',
                'department' => $saved['department'] ?? '',
                'city' => $saved['city'] ?? '',
                'state' => $saved['state'] ?? '',
                'document_type' => $saved['document_type'] ?? 'DNI',
                'wants_factura_a' => $saved['wants_factura_a'] ?? false,
            ];
        } elseif ($customer) {
            $prefill = $customer->only('firstname', 'lastname', 'phone', 'document', 'address', 'department', 'city', 'state')
                + ['document_type' => 'DNI', 'wants_factura_a' => false];
        } else {
            $prefill = [
                'firstname' => '', 'lastname' => '', 'phone' => '', 'document' => '',
                'address' => '', 'department' => '', 'city' => '', 'state' => '',
                'document_type' => 'DNI', 'wants_factura_a' => false,
            ];
        }

        return Inertia::render('Checkout/Recipient', [
            'customer' => $prefill,
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

        // Métodos administrados manualmente (transferencia, efectivo). El medio
        // de Mercado Pago (credit_card) NO se gestiona acá: depende solo de la
        // configuración de Mercado Pago (toggle habilitado + access token).
        $paymentMethods = PaymentMethod::where('is_active', true)
            ->where('type', '!=', PaymentMethodTypeEnum::CREDIT_CARD)
            ->orderBy('sort_order')
            ->get();

        if ($this->mercadoPagoService->isConfigured()) {
            $paymentMethods->push($this->resolveMercadoPagoMethod());
        }

        $paymentMethods = $paymentMethods
            ->sortBy('sort_order')
            ->map(fn (PaymentMethod $m) => $m->only(
                'id', 'type', 'title', 'subtitle', 'description', 'max_installments'
            ))
            ->values();

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
            'payment_receipt' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,heic,heif', 'max:5120'],
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

        if ($paymentMethod->type === PaymentMethodTypeEnum::CREDIT_CARD
            && ! $this->mercadoPagoService->isConfigured()) {
            return back()->withErrors([
                'payment_method_id' => 'El pago con tarjeta no está disponible en este momento.',
            ]);
        }

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
            'shipping_method_id' => $delivery['shipping_method_id'] ?? null,
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
                'is_customized' => $item['customized'] ?? false,
                'customization_price' => $item['customization_price'] ?? 0,
                'customization_label' => $item['customization_label'] ?? null,
            ]);
        }

        // Comprobante de transferencia (opcional)
        if ($paymentMethod->type === PaymentMethodTypeEnum::BANK_TRANSFER
            && $request->hasFile('payment_receipt')) {
            $order->addMediaFromRequest('payment_receipt')->toMediaCollection('payment_receipt');
        }

        // Apply coupon usage
        if ($coupon && $customer) {
            $couponModel = Coupon::find($coupon['id']);
            if ($couponModel) {
                app(CouponService::class)->applyCoupon($couponModel, $order, $customer);
            }
        }

        // Aviso a los administradores suscritos: el pedido se concreta al hacer
        // click en "Realizar pedido", sin importar el medio de pago elegido.
        $this->notifyAdmins($order);

        // For credit card: keep cart alive until MP confirms — store order ID in session
        if ($paymentMethod->type === PaymentMethodTypeEnum::CREDIT_CARD) {
            session(['checkout_pending_order_id' => $order->id]);

            return $this->redirectToMercadoPago($order);
        }

        // For other payment methods: clear immediately
        $this->clearCheckoutSession();

        // Transferencia: el pago se acredita luego, avisamos que recibimos el pedido.
        if ($paymentMethod->type === PaymentMethodTypeEnum::BANK_TRANSFER && filled($order->email)) {
            Mail::to($order->email)->queue(new OrderStatusMail($order, OrderMailStepEnum::PENDING));
        }

        return redirect()->route('checkout.result', [
            'status' => 'pending',
            'order' => $order->id,
        ]);
    }

    // Retomar el pago de una orden pendiente desde el email de recordatorio.
    // La ruta está firmada (middleware 'signed'), por lo que el link no puede
    // ser manipulado para apuntar a otra orden.
    public function pay(Order $order)
    {
        // Si la orden ya no está pendiente, mostramos su estado real en lugar de
        // reiniciar un pago. Reutilizamos el mapeo de estados de success().
        if ($order->payment_status !== PaymentStatusEnum::PENDING) {
            $status = match ($order->payment_status) {
                PaymentStatusEnum::PAID => 'approved',
                PaymentStatusEnum::FAILED => 'rejected',
                default => 'pending',
            };

            return redirect()->route('checkout.result', [
                'status' => $status,
                'order' => $order->id,
            ]);
        }

        // El pago online solo aplica a tarjeta (Mercado Pago). Transferencia y
        // efectivo se acreditan manualmente, así que no hay nada que retomar.
        $isCreditCard = $order->paymentMethod?->type === PaymentMethodTypeEnum::CREDIT_CARD;

        if (! $isCreditCard || ! $this->mercadoPagoService->isConfigured()) {
            return redirect()->route('checkout.result', [
                'status' => 'pending',
                'order' => $order->id,
            ]);
        }

        try {
            $preference = $this->mercadoPagoService->createPreference($order);

            return redirect()->away($preference['init_point']);
        } catch (\Throwable $e) {
            Log::error('MercadoPago resume payment failed', [
                'message' => $e->getMessage(),
                'order_id' => $order->id,
                'response' => method_exists($e, 'getApiResponse') ? $e->getApiResponse()?->getContent() : null,
            ]);

            return redirect()->route('checkout.result', [
                'status' => 'pending',
                'order' => $order->id,
            ]);
        }
    }

    private function redirectToMercadoPago(Order $order)
    {
        try {
            $preference = $this->mercadoPagoService->createPreference($order);

            return Inertia::location($preference['init_point']);
        } catch (\Throwable $e) {
            Log::error('MercadoPago preference creation failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $order->id,
                'response' => method_exists($e, 'getApiResponse') ? $e->getApiResponse()?->getContent() : null,
            ]);

            // No dejamos la orden "pendiente" fantasma ni simulamos un pedido
            // recibido: marcamos el intento como fallido y volvemos al paso de
            // pago con un error visible para que el cliente pueda reintentar.
            $order->update(['payment_status' => PaymentStatusEnum::FAILED]);
            session()->forget('checkout_pending_order_id');

            return back()->withErrors([
                'payment_method_id' => 'No pudimos iniciar el pago con tarjeta. Probá nuevamente o elegí otro medio de pago.',
            ]);
        }
    }

    public function success(Request $request): Response|RedirectResponse
    {
        $status = $request->query('status', 'pending');
        $orderId = $request->query('order')
            ?? $request->query('external_reference')
            ?? session('checkout_pending_order_id');

        $paymentMethodData = null;

        if ($orderId) {
            $order = Order::with('paymentMethod')->find($orderId);

            // Solo consultamos MP si la orden efectivamente pasó por MP
            // (tiene preference id). Para transferencia/efectivo no hay nada
            // que sincronizar, y antes generaba un 401 inocuo en el log.
            if ($order && $order->payment_status === PaymentStatusEnum::PENDING && filled($order->mp_preference_id)) {
                $this->mercadoPagoService->syncOrderFromMp($order);
                $order->refresh();
            }

            // Cancelacion en Mercado Pago: la orden paso por MP pero el cliente
            // volvio sin pagar, asi que sigue PENDING y nunca se registro un pago
            // (mp_payment_id vacio). El carrito sigue intacto (el flujo de tarjeta
            // no lo limpia), asi que lo devolvemos al carrito para reintentar.
            if ($order
                && filled($order->mp_preference_id)
                && $order->payment_status === PaymentStatusEnum::PENDING
                && blank($order->mp_payment_id)
            ) {
                return redirect()->route('cart.index')
                    ->with('error', 'Cancelaste el pago. Tu carrito sigue disponible para reintentar.');
            }

            if ($order?->paymentMethod) {
                $paymentMethodData = [
                    'type' => $order->paymentMethod->type->value,
                    'title' => $order->paymentMethod->title,
                    'description' => $order->paymentMethod->description,
                ];
            }

            if ($order && $status === 'pending') {
                $status = match ($order->payment_status) {
                    PaymentStatusEnum::PAID => 'approved',
                    PaymentStatusEnum::FAILED => 'rejected',
                    default => 'pending',
                };
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
        $ok = $this->mercadoPagoService->handleWebhook($request);

        return response()->json(['ok' => $ok], $ok ? 200 : 401);
    }

    public function sendEmailCode(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        try {
            $this->emailVerificationService->sendCode($validated['email'], $request);
        } catch (\Throwable $e) {
            return back()
                ->withErrors(['verification_code' => $e->getMessage()])
                ->with('verification_required', true);
        }

        return back()->with('verification_resent', true);
    }

    private function resolveCustomer(?Customer $authenticatedCustomer, array $contact, array $recipient): ?Customer
    {
        if ($authenticatedCustomer) {
            return $authenticatedCustomer;
        }

        $email = $contact['email'];

        $customer = Customer::firstOrCreate(
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

        // El invitado pudo verificar su email antes de que existiera su Customer,
        // así que sincronizamos la marca de verificación recién ahora.
        $this->emailVerificationService->syncCustomerVerification($customer);

        return $customer;
    }

    /**
     * Devuelve el registro canónico del medio de pago Mercado Pago (credit_card),
     * creándolo con valores por defecto si no existe. Así la opción queda
     * disponible siempre que Mercado Pago esté configurado, sin depender de que
     * el registro haya sido sembrado o activado manualmente en el admin.
     */
    private function resolveMercadoPagoMethod(): PaymentMethod
    {
        return PaymentMethod::firstOrCreate(
            ['type' => PaymentMethodTypeEnum::CREDIT_CARD],
            [
                'title' => 'Tarjeta de Credito / Debito',
                'subtitle' => 'Hasta 6 cuotas sin interes',
                'description' => 'Pago seguro a traves de Mercado Pago.',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );
    }

    /**
     * Envía el aviso de pedido nuevo a los administradores (tabla users) que
     * tienen activado "Recibir notificaciones de pedidos".
     */
    private function notifyAdmins(Order $order): void
    {
        $recipients = User::where('receives_order_notifications', true)
            ->pluck('email')
            ->filter()
            ->all();

        if (empty($recipients)) {
            return;
        }

        Mail::to($recipients)->queue(new NewOrderAdminMail($order));
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
