<?php

namespace Tests\Feature;

use App\Enums\SentEmailStatusEnum;
use App\Models\SentEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SentEmailLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_sending_an_email_records_it_as_sent(): void
    {
        Mail::raw('Contenido de prueba', function ($message) {
            $message->to('comprador@example.com')->subject('Asunto de prueba');
        });

        $this->assertDatabaseCount('sent_emails', 1);

        $record = SentEmail::first();

        $this->assertSame('comprador@example.com', $record->to_address);
        $this->assertSame('Asunto de prueba', $record->subject);
        $this->assertSame(SentEmailStatusEnum::SENT, $record->status);
        $this->assertNotNull($record->sent_at);
    }
}
