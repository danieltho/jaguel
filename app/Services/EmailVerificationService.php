<?php

namespace App\Services;

use App\Mail\EmailVerificationMail;
use App\Models\Customer;
use App\Models\EmailVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class EmailVerificationService
{
    private const CODE_TTL_MINUTES = 60;

    private const RESEND_COOLDOWN_SECONDS = 60;

    private const MAX_SENDS_PER_HOUR = 3;

    private const MAX_ATTEMPTS = 5;

    public function isEmailVerified(string $email): bool
    {
        $email = $this->normalize($email);

        return Customer::query()
            ->where('email', $email)
            ->whereNotNull('email_verified_at')
            ->exists();
    }

    public function sendCode(string $email, ?Request $request = null): EmailVerification
    {
        $email = $this->normalize($email);

        $recent = EmailVerification::query()
            ->where('email', $email)
            ->where('created_at', '>=', now()->subSeconds(self::RESEND_COOLDOWN_SECONDS))
            ->latest('id')
            ->first();

        if ($recent) {
            $remaining = self::RESEND_COOLDOWN_SECONDS - now()->diffInSeconds($recent->created_at, true);
            throw new RuntimeException("Esperá {$remaining} segundos antes de pedir otro código.");
        }

        $sentLastHour = EmailVerification::query()
            ->where('email', $email)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($sentLastHour >= self::MAX_SENDS_PER_HOUR) {
            throw new RuntimeException('Alcanzaste el límite de códigos por hora. Probá más tarde.');
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $verification = EmailVerification::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
            'ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);

        Mail::to($email)->send(new EmailVerificationMail($code));

        return $verification;
    }

    public function verify(string $email, string $code): bool
    {
        $email = $this->normalize($email);
        $code = trim($code);

        $verification = EmailVerification::query()
            ->where('email', $email)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if (! $verification) {
            throw new RuntimeException('No hay un código pendiente para este email. Solicitá uno nuevo.');
        }

        if ($verification->isExpired()) {
            throw new RuntimeException('El código expiró. Solicitá uno nuevo.');
        }

        if ($verification->attempts >= self::MAX_ATTEMPTS) {
            throw new RuntimeException('Demasiados intentos. Solicitá un código nuevo.');
        }

        $verification->increment('attempts');

        if (! hash_equals($verification->code, $code)) {
            throw new RuntimeException('Código incorrecto.');
        }

        $verification->update(['verified_at' => now()]);

        $this->markCustomerVerified($email);

        return true;
    }

    private function markCustomerVerified(string $email): void
    {
        Customer::query()
            ->where('email', $email)
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    private function normalize(string $email): string
    {
        return strtolower(trim($email));
    }
}
