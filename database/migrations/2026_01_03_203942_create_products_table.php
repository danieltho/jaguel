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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type', 20)->default(\App\Enums\ProductTypeEnum::FISICO);
            $table->string('link_video')->nullable();
            $table->integer('price_sold')->default(0);
            $table->integer('price_sales')->default(0);
            $table->integer('price_provider')->default(0);
            $table->integer('price_cost')->default(0);
            $table->integer('dimension_weight')->nullable();
            $table->integer('dimension_height')->nullable();
            $table->integer('dimension_width')->nullable();
            $table->integer('dimension_length')->nullable();
            $table->integer('stock')->nullable();
            $table->string('sku')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
