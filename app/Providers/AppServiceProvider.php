<?php

namespace App\Providers;

use App\Services\MailConfigurator;
use App\Services\SettingsService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
    }

    public function boot(): void
    {
        $this->configureMercadoPago();
        $this->configureMail();
    }

    private function configureMail(): void
    {
        try {
            $this->app->make(MailConfigurator::class)->apply();
        } catch (\Throwable $e) {
            // No bloquear el arranque si la tabla de settings no existe (instalación inicial).
        }
    }

    private function configureMercadoPago(): void
    {
        try {
            $settings = $this->app->make(SettingsService::class);
            $token = $settings->get('mercadopago', 'access_token')
                ?? config('services.mercadopago.access_token');
        } catch (\Throwable $e) {
            $token = config('services.mercadopago.access_token');
        }

        if ($token) {
            \MercadoPago\MercadoPagoConfig::setAccessToken($token);
        }
    }
}
