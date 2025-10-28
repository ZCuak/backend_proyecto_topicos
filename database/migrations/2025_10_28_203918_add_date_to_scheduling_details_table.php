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
        Schema::table('scheduling_details', function (Blueprint $table) {
            // Agregar campo de fecha específica para este detalle
            $table->date('date')->comment('Fecha específica de este detalle de programación');
            
            // Índice para optimizar consultas por fecha
            $table->index(['scheduling_id', 'date']);
            $table->index(['user_id', 'date']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scheduling_details', function (Blueprint $table) {
            $table->dropIndex(['scheduling_id', 'date']);
            $table->dropIndex(['user_id', 'date']);
            $table->dropIndex(['date']);
            $table->dropColumn('date');
        });
    }
};