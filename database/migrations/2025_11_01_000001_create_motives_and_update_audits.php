<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear catálogo de motivos
        Schema::create('motives', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Poblar motivos base
        DB::table('motives')->insert([
            ['name' => 'Corrección de datos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Por salud', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fallecimiento', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fuerza mayor', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Accidente', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Agregar motivo_id a auditoría (opcional)
        Schema::table('audits', function (Blueprint $table) {
            $table->foreignId('motive_id')
                ->nullable()
                ->after('user_name')
                ->constrained('motives')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            if (Schema::hasColumn('audits', 'motive_id')) {
                $table->dropForeign(['motive_id']);
                $table->dropColumn('motive_id');
            }
        });

        Schema::dropIfExists('motives');
    }
};
