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
        Schema::table('schedulings', function (Blueprint $table) {
            // Agregar campos de rango de fechas
            $table->date('start_date')->nullable()->comment('Fecha de inicio de la programación');
            $table->date('end_date')->nullable()->comment('Fecha de fin de la programación');
            
            // Mantener el campo date para compatibilidad (será la fecha principal)
            // $table->date('date')->comment('Fecha principal de la programación');
            
            // Índices para optimizar consultas por rango de fechas
            $table->index(['start_date', 'end_date']);
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedulings', function (Blueprint $table) {
            $table->dropIndex(['start_date', 'end_date']);
            $table->dropIndex(['start_date']);
            $table->dropIndex(['end_date']);
            $table->dropColumn(['start_date', 'end_date']);
        });
    }
};