<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nombre o código del horario');
            $table->time('time_start')->comment('Hora de inicio del turno');
            $table->time('time_end')->comment('Hora de fin del turno');
            $table->text('description')->nullable()->comment('Descripción del turno');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};