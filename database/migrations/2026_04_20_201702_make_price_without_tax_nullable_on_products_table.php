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
            $table->integer('price_without_tax')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        DB::table('products')->whereNull('price_without_tax')->update(['price_without_tax' => 0]);

        Schema::table('products', function (Blueprint $table) {
            $table->integer('price_without_tax')->default(0)->nullable(false)->change();
        });
    }
};
