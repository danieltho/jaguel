<?php

use App\Http\Controllers\Auth\CustomerLoginController;
use App\Http\Controllers\Auth\CustomerPasswordController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductDetailController;
use App\Http\Controllers\ProductListingController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class);

Route::get('/producto/{slug}/{variantSku?}', [ProductDetailController::class, 'show'])->name('products.show');

Route::prefix('productos')->name('products.')->group(function () {
    Route::get('/', [ProductListingController::class, 'index'])->name('index');
    Route::get('/{groupSlug}', [ProductListingController::class, 'byGroup'])->name('by-group');
});

Route::prefix('carrito')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/agregar', [CartController::class, 'add'])->name('add');
    Route::patch('/actualizar', [CartController::class, 'update'])->name('update');
    Route::delete('/eliminar', [CartController::class, 'remove'])->name('remove');
    Route::post('/cupon', [CartController::class, 'applyCoupon'])->name('coupon.apply');
    Route::delete('/cupon', [CartController::class, 'removeCoupon'])->name('coupon.remove');
});

Route::prefix('cuenta')->name('customer.')->group(function () {
    Route::middleware('guest:customer')->group(function () {
        Route::get('/login', [CustomerLoginController::class, 'showLogin'])->name('login');
        Route::post('/login', [CustomerLoginController::class, 'login']);
        Route::get('/registro', [CustomerLoginController::class, 'showRegister'])->name('register');
        Route::post('/registro', [CustomerLoginController::class, 'register']);
        Route::get('/recuperar', [CustomerPasswordController::class, 'showForgot'])->name('password.request');
        Route::post('/recuperar', [CustomerPasswordController::class, 'sendResetLink'])->name('password.email');
        Route::get('/restablecer/{token}', [CustomerPasswordController::class, 'showReset'])->name('password.reset');
        Route::post('/restablecer', [CustomerPasswordController::class, 'reset'])->name('password.update');
    });
    Route::post('/logout', [CustomerLoginController::class, 'logout'])->name('logout')->middleware('auth:customer');
});

Route::get('/buscar', [SearchController::class, 'index'])->name('search');

Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/contacto', [CheckoutController::class, 'showContact'])->name('contact');
    Route::post('/contacto', [CheckoutController::class, 'saveContact']);
    Route::get('/entrega', [CheckoutController::class, 'showDelivery'])->name('delivery');
    Route::post('/entrega', [CheckoutController::class, 'saveDelivery']);
    Route::get('/destinatario', [CheckoutController::class, 'showRecipient'])->name('recipient');
    Route::post('/destinatario', [CheckoutController::class, 'saveRecipient']);
    Route::get('/pago', [CheckoutController::class, 'showPayment'])->name('payment');
    Route::post('/pago', [CheckoutController::class, 'placeOrder']);
    Route::get('/resultado', [CheckoutController::class, 'success'])->name('result');
});

Route::post('/webhook/mercadopago', [CheckoutController::class, 'webhook'])
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
