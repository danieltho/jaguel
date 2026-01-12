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
        Schema::table('customers', function (Blueprint $table) {
            // Renombrar columnas
            $table->renameColumn('name', 'firstname');
            $table->renameColumn('dni', 'document');
            $table->renameColumn('province', 'state');
            $table->renameColumn('country', 'country_iso');

            // Agregar nuevas columnas
            $table->string('address_number')->nullable()->after('address');
            $table->string('department')->nullable()->after('address_number');
            $table->string('zone')->nullable()->after('department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Revertir renombres
            $table->renameColumn('firstname', 'name');
            $table->renameColumn('document', 'dni');
            $table->renameColumn('state', 'province');
            $table->renameColumn('country_iso', 'country');

            // Eliminar columnas agregadas
            $table->dropColumn(['address_number', 'department', 'zone']);
        });
    }
};
