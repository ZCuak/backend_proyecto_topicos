<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['nombrado', 'permanente', 'eventual']); // Tipo de contrato
            $table->date('date_start'); // Fecha de inicio del contrato
            $table->date('date_end')->nullable(); // Fecha de término (null para nombrados y permanentes)
            $table->text('description')->nullable(); // Descripción o notas del contrato
            $table->boolean('is_active')->default(true); // Si el contrato está activo
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Usuario/Personal contratado
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
