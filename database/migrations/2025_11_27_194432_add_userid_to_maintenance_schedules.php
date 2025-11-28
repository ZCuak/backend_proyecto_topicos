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
        Schema::table('maintenance_schedules', function (Blueprint $table) {
            $table->foreignId('user_id')
                  ->nullable() // Recomendado poner nullable si ya tienes datos en la tabla para no romperlos
                  ->constrained('users') // Crea la relación con la tabla users
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_schedules', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            // Luego borramos la columna
            $table->dropColumn('user_id');
        });
    }
};
