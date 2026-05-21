<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('days_label')->nullable();
            $table->string('delivery_type'); // pickup | shipping
            $table->unsignedInteger('price')->default(0); // ARS sin decimales
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('shipping_method_id')
                ->nullable()
                ->after('shipping_method')
                ->constrained('shipping_methods')
                ->nullOnDelete();
        });

        // Sembrar los métodos que estaban hardcodeados.
        $now = now();
        \DB::table('shipping_methods')->insert([
            [
                'code' => 'punto_retiro',
                'name' => 'Punto de Retiro',
                'description' => 'Calle 37 N° 1242, Miramar, Buenos Aires',
                'days_label' => 'Listo entre 3-5 días hábiles',
                'delivery_type' => 'pickup',
                'price' => 0,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'correo_argentino',
                'name' => 'Correo Argentino',
                'description' => 'Envío a domicilio',
                'days_label' => '5-7 días hábiles',
                'delivery_type' => 'shipping',
                'price' => 640000,
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // Backfill: vincular pedidos existentes a su método según el código.
        \DB::table('orders')
            ->whereNotNull('shipping_method')
            ->orderBy('id')
            ->chunkById(500, function ($orders) {
                foreach ($orders as $order) {
                    $methodId = \DB::table('shipping_methods')
                        ->where('code', $order->shipping_method)
                        ->value('id');

                    if ($methodId) {
                        \DB::table('orders')
                            ->where('id', $order->id)
                            ->update(['shipping_method_id' => $methodId]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shipping_method_id');
        });

        Schema::dropIfExists('shipping_methods');
    }
};
