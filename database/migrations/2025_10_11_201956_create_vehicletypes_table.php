<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicletypes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Tipo de vehículo, ej. Camión, Motocar, etc.');
            $table->text('description')->nullable()->comment('Descripción opcional del tipo');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicletypes');
    }
};
