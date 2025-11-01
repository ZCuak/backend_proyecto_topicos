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
        Schema::create('audits', function (Blueprint $table) {
            $table->id();

            $table->morphs('auditable');
            $table->string('campo_modificado', 150)
                  ->comment('Nombre de la columna modificada (Ej: turno, personal, zona)');
            $table->text('valor_anterior')->nullable();
            $table->text('valor_nuevo')->nullable();
            $table->string('user_name', 100)->nullable()
                  ->comment('Nombre o email del usuario al momento del cambio');
            $table->text('nota_adicional')->nullable()
                  ->comment('Nota opcional del contexto');
            

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
