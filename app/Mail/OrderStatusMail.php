<?php

namespace App\Mail;

use App\Enums\OrderMailStepEnum;
use App\Models\Order;
use App\Services\SettingsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public OrderMailStepEnum $step,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->step->subject($this->order),
        );
    }

    public function content(): Content
    {
        $contact = [];

        try {
            $contact = app(SettingsService::class)->group('contact');
        } catch (\Throwable $e) {
            // No bloquear el render si la tabla de settings no existe.
        }

        return new Content(
            view: 'emails.order-status',
            with: [
                'order' => $this->order,
                'step' => $this->step,
                'contactPhone' => $contact['whatsapp'] ?? '+54 9 223 312-3981',
            ],
        );
    }
}
