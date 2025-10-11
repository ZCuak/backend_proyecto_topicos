<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicleroutes', function (Blueprint $table) {
            $table->id();

            // Datos principales del recorrido
            $table->date('date_route')->comment('Fecha programada del recorrido');
            $table->time('time_route')->comment('Hora programada del recorrido');
            $table->text('description')->nullable()->comment('Descripción u observaciones del recorrido');

            // Relaciones
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('route_id')->constrained('routes')->cascadeOnDelete();
            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();

            // Auditoría
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicleroutes');
    }
};
