<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->boolean('is_customized')->default(false)->after('unit_price');
            $table->integer('customization_price')->default(0)->after('is_customized');
            $table->string('customization_label')->nullable()->after('customization_price');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['is_customized', 'customization_price', 'customization_label']);
        });
    }
};
