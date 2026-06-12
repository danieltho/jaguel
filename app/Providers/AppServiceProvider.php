<?php

namespace App\Providers;

use App\Listeners\RecordSentEmail;
use App\Services\MailConfigurator;
use App\Services\MercadoPagoService;
use App\Services\SettingsService;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Event;
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
        $this->registerMailLogging();
    }

    private function registerMailLogging(): void
    {
        Event::listen(MessageSending::class, [RecordSentEmail::class, 'sending']);
        Event::listen(MessageSent::class, [RecordSentEmail::class, 'sent']);
        Event::listen(JobFailed::class, [RecordSentEmail::class, 'jobFailed']);
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
