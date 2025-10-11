<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();

            // Datos principales
            $table->string('name')->comment('Nombre o referencia del vehículo');
            $table->string('code', 50)->unique()->comment('Código interno único del vehículo');
            $table->string('plate', 10)->unique()->comment('Placa del vehículo');
            $table->integer('year')->comment('Año de fabricación');
            
            // Capacidades
            $table->integer('occupant_capacity')->default(0)->comment('Capacidad máxima de ocupantes');
            $table->integer('load_capacity')->default(0)->comment('Capacidad máxima de carga en kg');
            
            // Descripción y estado
            $table->text('description')->nullable()->comment('Descripción o detalles adicionales del vehículo');
            $table->enum('status', ['DISPONIBLE', 'OCUPADO', 'MANTENIMIENTO', 'INACTIVO'])
                  ->default('DISPONIBLE')
                  ->comment('Estado actual del vehículo');
            
            // Relaciones
            $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->foreignId('model_id')->constrained('brandmodels')->cascadeOnDelete();
            $table->foreignId('type_id')->constrained('vehicletypes')->cascadeOnDelete();
            $table->foreignId('color_id')->constrained('vehiclecolors')->cascadeOnDelete();

            // Auditoría
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
