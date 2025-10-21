<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar la relación con zone_id (si existe la FK)
            if (Schema::hasColumn('users', 'zone_id')) {
       
                $table->dropColumn('zone_id');
            }

            // Agregar el nuevo campo phone
            $table->string('phone', 20)->nullable()->after('email')->comment('Número de teléfono del usuario');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar el campo phone
            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }

            // Restaurar el campo zone_id (en caso de rollback)
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
        });
    }
};
