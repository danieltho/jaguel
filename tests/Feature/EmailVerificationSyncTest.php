<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\EmailVerification;
use App\Services\EmailVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmailVerificationSyncTest extends TestCase
{
    use RefreshDatabase;

    private function makeCustomer(string $email): Customer
    {
        return Customer::create([
            'firstname' => 'Juan',
            'lastname' => 'Pérez',
            'email' => $email,
            'password' => Hash::make('secret'),
        ]);
    }

    private function service(): EmailVerificationService
    {
        return app(EmailVerificationService::class);
    }

    public function test_was_code_verified_reflects_verification_records(): void
    {
        $email = 'invitado@example.com';

        $this->assertFalse($this->service()->wasCodeVerified($email));

        // Código emitido pero no verificado todavía.
        EmailVerification::create([
            'email' => $email,
            'code' => '123456',
            'expires_at' => now()->addMinutes(60),
        ]);
        $this->assertFalse($this->service()->wasCodeVerified($email));

        // Código verificado.
        EmailVerification::where('email', $email)->update(['verified_at' => now()]);
        $this->assertTrue($this->service()->wasCodeVerified($email));
    }

    public function test_sync_marks_customer_verified_when_code_was_verified(): void
    {
        $email = 'invitado@example.com';

        EmailVerification::create([
            'email' => $email,
            'code' => '123456',
            'expires_at' => now()->addMinutes(60),
            'verified_at' => now(),
        ]);

        // Customer creado DESPUÉS de la verificación (caso invitado en checkout).
        $customer = $this->makeCustomer($email);
        $this->assertNull($customer->email_verified_at);

        $this->service()->syncCustomerVerification($customer);

        $this->assertNotNull($customer->fresh()->email_verified_at);
    }

    public function test_sync_does_nothing_without_verified_code(): void
    {
        $customer = $this->makeCustomer('sinverificar@example.com');

        $this->service()->syncCustomerVerification($customer);

        $this->assertNull($customer->fresh()->email_verified_at);
    }
}
