<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nombre o código de la ruta');
            $table->text('description')->nullable()->comment('Descripción general de la ruta');
            $table->foreignId('zone_id')->constrained('zones')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete()->comment('Vehículo asignado a la ruta');
            $table->foreignId('conductor_id')->nullable()->constrained('users')->nullOnDelete()->comment('Conductor principal');
            $table->foreignId('assistant_id')->nullable()->constrained('users')->nullOnDelete()->comment('Ayudante asignado');
            $table->string('start_point')->comment('Punto de inicio');
            $table->string('end_point')->comment('Punto de fin');
            $table->enum('status', ['PLANIFICADA', 'EN_PROCESO', 'FINALIZADA'])->default('PLANIFICADA');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
