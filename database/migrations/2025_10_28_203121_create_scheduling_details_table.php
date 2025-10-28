<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scheduling_details', function (Blueprint $table) {
            $table->id();
            
            // Relación con la programación principal
            $table->foreignId('scheduling_id')->constrained('schedulings')->onDelete('cascade');
            
            // Usuario específico en esta programación
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Rol del usuario en esta programación
            $table->enum('role', ['conductor', 'ayudante'])->comment('Rol del usuario en la programación');
            
            // Orden de posición (1=conductor, 2=ayudante1, 3=ayudante2)
            $table->integer('position_order')->comment('Orden de posición: 1=conductor, 2=ayudante1, 3=ayudante2');
            
            // Estado de asistencia del usuario
            $table->enum('attendance_status', ['pendiente', 'presente', 'ausente', 'justificado'])->default('pendiente')->comment('Estado de asistencia');
            
            // Notas específicas del usuario para esta programación
            $table->text('notes')->nullable()->comment('Notas específicas del usuario');
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para optimizar consultas
            $table->index(['scheduling_id', 'user_id']);
            $table->index(['scheduling_id', 'role']);
            $table->index(['user_id', 'attendance_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduling_details');
    }
};