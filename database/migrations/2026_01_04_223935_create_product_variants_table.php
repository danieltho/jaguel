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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('price_sold')->default(0);
            $table->integer('price_sales')->default(0);
            $table->integer('price_provider')->default(0);
            $table->integer('price_cost')->default(0);
            $table->integer('dimension_weight')->nullable();
            $table->integer('dimension_height')->nullable();
            $table->integer('dimension_width')->nullable();
            $table->integer('dimension_length')->nullable();
            $table->integer('stock')->nullable();
            $table->integer('sort_order')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
