<?php

namespace App\Providers;

use App\Services\MailConfigurator;
use App\Services\MercadoPagoService;
use App\Services\SettingsService;
use Illuminate\Support\ServiceProvider;
use MercadoPago\MercadoPagoConfig;

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
            $token = $this->app->make(MercadoPagoService::class)->resolveAccessToken();
        } catch (\Throwable $e) {
            $token = null;
        }

        if ($token) {
            MercadoPagoConfig::setAccessToken($token);
        }
    }
}
