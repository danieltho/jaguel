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
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price', 'status', 'is_active']);
            $table->dropConstrainedForeignId('category_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_custom')->default(false);
            $table->boolean('is_simple')->default(false);
            $table->boolean('is_featured')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_custom', 'is_simple', 'is_featured']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->integer('price');
            $table->string('status')->default('in_stock');
            $table->boolean('is_active')->default(true);
            $table->foreignId('category_id')->nullable()->constrained();
        });
    }
};
