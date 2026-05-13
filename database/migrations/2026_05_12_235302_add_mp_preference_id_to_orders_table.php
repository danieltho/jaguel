<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('mp_preference_id')->nullable()->after('payment_status')->index();
            $table->string('mp_payment_id')->nullable()->after('mp_preference_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['mp_preference_id']);
            $table->dropIndex(['mp_payment_id']);
            $table->dropColumn(['mp_preference_id', 'mp_payment_id']);
        });
    }
};
