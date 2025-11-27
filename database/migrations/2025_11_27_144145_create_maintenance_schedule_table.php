<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_id')->constrained('maintenances')->onDelete('cascade'); // Relación con mantenimientos
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade'); // Relación con vehículos
            $table->enum('type', ['LIMPIEZA', 'REPARACIÓN']); // Tipo de mantenimiento
            $table->enum('day', ['LUNES', 'MARTES', 'MIÉRCOLES', 'JUEVES', 'VIERNES', 'SÁBADO']); // Día de la semana
            $table->time('start_time'); // Hora de inicio
            $table->time('end_time'); // Hora de fin
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};