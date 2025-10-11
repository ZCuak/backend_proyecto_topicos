<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['NOMBRADO', 'CONTRATO_PERMANENTE', 'CONTRATO_EVENTUAL'])->comment('Tipo de contrato del personal');
            $table->date('start_date')->comment('Fecha de inicio de contrato');
            $table->date('end_date')->nullable()->comment('Fecha de fin de contrato (solo aplica a contratos)');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
