<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

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
        return new Content(
            view: 'emails.pending-payment-reminder',
            with: [
                'order' => $this->order,
                'reminder' => $this->reminder,
            ],
        );
    }
}
