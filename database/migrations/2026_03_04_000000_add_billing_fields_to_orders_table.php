<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('document_number')->nullable()->after('postal_code');
            $table->string('document_type')->nullable()->default('DNI')->after('document_number');
            $table->boolean('wants_factura_a')->default(false)->after('document_type');
            $table->string('recipient_firstname')->nullable()->after('wants_factura_a');
            $table->string('recipient_lastname')->nullable()->after('recipient_firstname');
            $table->string('recipient_phone')->nullable()->after('recipient_lastname');
            $table->string('recipient_address')->nullable()->after('recipient_phone');
            $table->string('recipient_department')->nullable()->after('recipient_address');
            $table->string('recipient_city')->nullable()->after('recipient_department');
            $table->string('recipient_state')->nullable()->after('recipient_city');
            $table->string('delivery_type')->nullable()->after('recipient_state');
            $table->string('shipping_method')->nullable()->after('delivery_type');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'document_number', 'document_type', 'wants_factura_a',
                'recipient_firstname', 'recipient_lastname', 'recipient_phone',
                'recipient_address', 'recipient_department', 'recipient_city', 'recipient_state',
                'delivery_type', 'shipping_method',
            ]);
        });
    }
};
