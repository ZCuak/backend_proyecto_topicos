<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('scheduling_details', function (Blueprint $table) {
            // Agregar la columna usertype_id como nullable primero
            $table->foreignId('usertype_id')->nullable()->after('user_id')->constrained('usertypes')->onDelete('cascade')->comment('ID del tipo de usuario (usertypes.id)');
        });

        // Actualizar los datos existentes basándose en el role
        DB::statement("UPDATE scheduling_details SET usertype_id = CASE WHEN role = 'conductor' THEN 1 ELSE 2 END");

        Schema::table('scheduling_details', function (Blueprint $table) {
            // Hacer la columna NOT NULL después de actualizar los datos
            $table->foreignId('usertype_id')->nullable(false)->change();
            
            // Eliminar la columna role
            $table->dropColumn('role');
            
            // Agregar nuevo índice
            $table->index(['scheduling_id', 'usertype_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scheduling_details', function (Blueprint $table) {
            // Eliminar la columna usertype_id
            $table->dropForeign(['usertype_id']);
            $table->dropColumn('usertype_id');
            
            // Restaurar la columna role
            $table->enum('role', ['conductor', 'ayudante'])->comment('Rol del usuario en la programación');
            
            // Restaurar índices
            $table->dropIndex(['scheduling_id', 'usertype_id']);
            $table->index(['scheduling_id', 'role']);
        });
    }
};