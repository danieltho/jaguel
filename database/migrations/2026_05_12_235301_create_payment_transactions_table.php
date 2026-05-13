<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('provider')->default('mercadopago')->index();
            $table->string('mp_payment_id')->nullable()->unique();
            $table->string('mp_preference_id')->nullable()->index();
            $table->string('mp_status')->index();
            $table->string('mp_status_detail')->nullable();
            $table->string('mp_payment_type')->nullable();
            $table->string('mp_payment_method')->nullable();
            $table->unsignedInteger('installments')->nullable();
            $table->unsignedBigInteger('transaction_amount');
            $table->string('currency', 3)->default('ARS');
            $table->string('payer_email')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'mp_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
