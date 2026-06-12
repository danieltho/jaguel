<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('payment_reminder_1_sent_at')->nullable()->after('payment_status');
            $table->timestamp('payment_reminder_2_sent_at')->nullable()->after('payment_reminder_1_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_reminder_1_sent_at', 'payment_reminder_2_sent_at']);
        });
    }
};
