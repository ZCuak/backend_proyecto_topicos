<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicleimages', function (Blueprint $table) {
            $table->id();
            $table->string('path')->comment('Ruta de la imagen del vehículo');
            $table->boolean('is_profile')->default(false)->comment('Indica si es la imagen principal del vehículo');
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicleimages');
    }
};
