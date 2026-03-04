<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($token = config('services.mercadopago.access_token')) {
            \MercadoPago\MercadoPagoConfig::setAccessToken($token);
        }
    }
}
