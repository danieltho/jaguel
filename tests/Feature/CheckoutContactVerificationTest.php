<?php

namespace Tests\Feature;

use App\Mail\EmailVerificationMail;
use App\Models\Customer;
use App\Models\EmailVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutContactVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_email_triggers_verification_and_sends_code(): void
    {
        Mail::fake();

        $response = $this->post('/checkout/contacto', [
            'email' => 'nuevo@example.com',
            'delivery_type' => 'shipping',
        ]);

        $response->assertSessionHas('verification_required', true);
        $response->assertSessionHasErrors('verification_code');
        Mail::assertSent(EmailVerificationMail::class);
    }

    public function test_verification_required_stays_set_when_send_code_fails_due_to_cooldown(): void
    {
        Mail::fake();
        $email = 'nuevo@example.com';

        // Simulamos que ya se envió un código hace segundos (dispara cooldown).
        EmailVerification::create([
            'email' => $email,
            'code' => '123456',
            'expires_at' => now()->addMinutes(60),
        ]);

        $response = $this->post('/checkout/contacto', [
            'email' => $email,
            'delivery_type' => 'shipping',
        ]);

        // El input de código se DEBE seguir mostrando aunque sendCode haya fallado.
        $response->assertSessionHas('verification_required', true);
        $response->assertSessionHasErrors('verification_code');
        Mail::assertNothingSent();
    }

    public function test_verified_customer_email_skips_verification(): void
    {
        Mail::fake();
        $customer = new Customer([
            'firstname' => 'Juan',
            'lastname' => 'Pérez',
            'email' => 'cliente@example.com',
            'password' => Hash::make('secret'),
        ]);
        $customer->email_verified_at = now();
        $customer->save();

        $response = $this->post('/checkout/contacto', [
            'email' => 'cliente@example.com',
            'delivery_type' => 'shipping',
        ]);

        $response->assertRedirect('/checkout/entrega');
        $response->assertSessionMissing('verification_required');
        Mail::assertNothingSent();
    }
}
