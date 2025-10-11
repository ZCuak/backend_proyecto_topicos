<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehiclecolors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Nombre del color, ej. Rojo');
            $table->string('rgb_code', 10)->comment('Código RGB o HEX del color');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiclecolors');
    }
};
