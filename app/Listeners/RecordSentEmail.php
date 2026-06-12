<?php

namespace App\Listeners;

use App\Enums\SentEmailStatusEnum;
use App\Models\SentEmail;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Queue\Events\JobFailed;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class RecordSentEmail
{
    private const TRACKING_HEADER = 'X-Sent-Email-Id';

    /**
     * Antes de enviar: registra el correo como "en envío" y deja un header
     * para poder correlacionar el resultado en MessageSent.
     */
    public function sending(MessageSending $event): void
    {
        $email = $event->message;

        $record = SentEmail::create([
            'to_address' => $this->addresses($email->getTo()),
            'subject' => $email->getSubject(),
            'mailer' => config('mail.default'),
            'status' => SentEmailStatusEnum::SENDING,
        ]);

        $email->getHeaders()->addTextHeader(self::TRACKING_HEADER, (string) $record->id);
    }

    /**
     * Tras enviar correctamente: marca el registro como enviado.
     */
    public function sent(MessageSent $event): void
    {
        $email = $event->sent->getOriginalMessage();
        $record = $this->resolveRecord($email);

        if (! $record) {
            return;
        }

        $record->update([
            'status' => SentEmailStatusEnum::SENT,
            'message_id' => $event->sent->getMessageId(),
            'sent_at' => now(),
        ]);
    }

    /**
     * Cuando un job encolado falla: marca como fallido el último correo en
     * estado "en envío" (best-effort) y guarda el error.
     */
    public function jobFailed(JobFailed $event): void
    {
        $record = SentEmail::query()
            ->where('status', SentEmailStatusEnum::SENDING)
            ->latest('id')
            ->first();

        if (! $record) {
            return;
        }

        $record->update([
            'status' => SentEmailStatusEnum::FAILED,
            'error' => $event->exception->getMessage(),
        ]);
    }

    private function resolveRecord(Email $email): ?SentEmail
    {
        $header = $email->getHeaders()->get(self::TRACKING_HEADER);

        if (! $header) {
            return null;
        }

        return SentEmail::find((int) $header->getBodyAsString());
    }

    /**
     * @param  array<int, Address>  $addresses
     */
    private function addresses(array $addresses): string
    {
        return collect($addresses)
            ->map(fn ($address) => $address->getAddress())
            ->implode(', ');
    }
}
