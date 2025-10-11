<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date')->comment('Fecha de marcación de asistencia');
            $table->time('check_in')->nullable()->comment('Hora de entrada');
            $table->time('check_out')->nullable()->comment('Hora de salida');
            $table->string('dni_key')->nullable()->comment('DNI o clave usada para registrar la asistencia');
            $table->enum('status', ['PRESENTE', 'AUSENTE', 'TARDANZA'])->default('PRESENTE')->comment('Estado de asistencia');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'date'], 'unique_attendance_per_day');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
