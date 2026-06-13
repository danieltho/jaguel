<?php

namespace App\Mail;

use App\Enums\PaymentMethodTypeEnum;
use App\Models\Order;
use App\Services\MercadoPagoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class PendingPaymentReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public int $reminder,
    ) {}

    public function envelope(): Envelope
    {
        $number = '#'.$this->order->order_number;

        $subject = $this->reminder >= 2
            ? "Tu pedido {$number} sigue pendiente de pago — estamos para ayudarte"
            : "Tu pedido {$number} quedó pendiente de pago";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        // El pago online solo aplica a tarjeta (Mercado Pago) y requiere que MP
        // esté configurado. Para esos casos generamos un link firmado que retoma
        // el pago; el resto de los medios siguen con el copy de contacto.
        $canPay = $this->order->paymentMethod?->type === PaymentMethodTypeEnum::CREDIT_CARD
            && app(MercadoPagoService::class)->isConfigured();

        $payUrl = $canPay
            ? URL::signedRoute('checkout.pay', ['order' => $this->order->id])
            : null;

        return new Content(
            view: 'emails.pending-payment-reminder',
            with: [
                'order' => $this->order,
                'reminder' => $this->reminder,
                'canPay' => $canPay,
                'payUrl' => $payUrl,
            ],
        );
    }
}
