<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Código interno único del vehículo');
            $table->string('plate', 10)->unique()->comment('Placa del vehículo');
            $table->integer('year')->comment('Año del vehículo');
            $table->string('serial_number', 100)->nullable()->comment('Número de serie o chasis');
            $table->string('image_path')->nullable()->comment('Imagen de perfil del vehículo');
            $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->foreignId('brandmodel_id')->constrained('brandmodels')->cascadeOnDelete();
            $table->foreignId('vehicletype_id')->constrained('vehicletypes')->cascadeOnDelete();
            $table->foreignId('vehiclecolor_id')->constrained('vehiclecolors')->cascadeOnDelete();
            $table->enum('status', ['DISPONIBLE', 'OCUPADO', 'EN_MANTENIMIENTO'])->default('DISPONIBLE')->comment('Estado actual del vehículo');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
