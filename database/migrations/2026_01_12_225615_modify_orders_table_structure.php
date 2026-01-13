<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Eliminar foreign keys y columnas obsoletas
            $table->dropForeign(['user_id']);
            $table->dropForeign(['product_id']);
            $table->dropColumn(['user_id', 'product_id', 'is_completed', 'discount_amount']);

            // Renombrar price a total
            $table->renameColumn('price', 'total');
        });

        Schema::table('orders', function (Blueprint $table) {
            // Agregar nuevos campos
            $table->unsignedBigInteger('order_number')->unique()->after('id');
            $table->string('email')->after('order_number');
            $table->string('postal_code')->nullable()->after('email');
            $table->foreignId('customer_id')->nullable()->after('postal_code')->constrained()->nullOnDelete();
            $table->integer('shipping_cost')->default(0)->after('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['order_number', 'email', 'postal_code', 'customer_id', 'shipping_cost']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('total', 'price');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->boolean('is_completed')->default(false);
            $table->integer('discount_amount')->default(0);
        });
    }
};
