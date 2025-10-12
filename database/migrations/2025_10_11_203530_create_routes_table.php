<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();

            // Nombre o identificador de la ruta
            $table->string('name')->comment('Nombre o código de la ruta');

            // Coordenadas geográficas de inicio y fin
            $table->decimal('latitude_start', 10, 7)->comment('Latitud del punto inicial');
            $table->decimal('longitude_start', 10, 7)->comment('Longitud del punto inicial');
            $table->decimal('latitude_end', 10, 7)->comment('Latitud del punto final');
            $table->decimal('longitude_end', 10, 7)->comment('Longitud del punto final');

            // Estado de la ruta
            $table->enum('status', ['ACTIVA', 'INACTIVA'])->default('ACTIVA')->comment('Estado actual de la ruta');

            // Auditoría
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
