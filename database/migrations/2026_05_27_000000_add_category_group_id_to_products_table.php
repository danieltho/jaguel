<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_group_id')
                ->nullable()
                ->after('category_id')
                ->constrained()
                ->nullOnDelete();
        });

        // Backfill: derivar el grupo desde la subcategoría ya asignada.
        DB::statement(
            'UPDATE products SET category_group_id = ('
            .'SELECT category_group_id FROM categories WHERE categories.id = products.category_id'
            .') WHERE category_id IS NOT NULL'
        );
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_group_id');
        });
    }
};
