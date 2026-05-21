<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class MailConfigurator
{
    public function __construct(private readonly SettingsService $settings)
    {
    }

    public function apply(): void
    {
        $cfg = $this->settings->group('smtp');

        $enabled = filter_var($cfg['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);

        // Toggle apagado o sin host configurado: forzamos driver "log".
        // Así no se filtran envíos a un SMTP heredado de .env (ej. mailpit).
        if (! $enabled || empty($cfg['host'])) {
            Config::set('mail.default', 'log');
            Mail::purge('log');

            return;
        }

        $encryption = $cfg['encryption'] ?? 'tls';
        if ($encryption === 'none') {
            $encryption = null;
        }

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.transport', 'smtp');
        Config::set('mail.mailers.smtp.host', $cfg['host']);
        Config::set('mail.mailers.smtp.port', (int) ($cfg['port'] ?? 587));
        Config::set('mail.mailers.smtp.username', $cfg['username'] ?? null);
        Config::set('mail.mailers.smtp.password', $cfg['password'] ?? null);
        Config::set('mail.mailers.smtp.encryption', $encryption);

        if (! empty($cfg['from_address'])) {
            Config::set('mail.from.address', $cfg['from_address']);
        }
        if (! empty($cfg['from_name'])) {
            Config::set('mail.from.name', $cfg['from_name']);
        }

        Mail::purge('smtp');
    }
}
