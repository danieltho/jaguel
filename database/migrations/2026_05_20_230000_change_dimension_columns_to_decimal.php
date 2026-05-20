<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('dimension_weight', 8, 2)->nullable()->change();
            $table->decimal('dimension_height', 8, 2)->nullable()->change();
            $table->decimal('dimension_width', 8, 2)->nullable()->change();
            $table->decimal('dimension_length', 8, 2)->nullable()->change();
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('dimension_weight', 8, 2)->nullable()->change();
            $table->decimal('dimension_height', 8, 2)->nullable()->change();
            $table->decimal('dimension_width', 8, 2)->nullable()->change();
            $table->decimal('dimension_length', 8, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('dimension_weight')->nullable()->change();
            $table->integer('dimension_height')->nullable()->change();
            $table->integer('dimension_width')->nullable()->change();
            $table->integer('dimension_length')->nullable()->change();
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->integer('dimension_weight')->nullable()->change();
            $table->integer('dimension_height')->nullable()->change();
            $table->integer('dimension_width')->nullable()->change();
            $table->integer('dimension_length')->nullable()->change();
        });
    }
};
