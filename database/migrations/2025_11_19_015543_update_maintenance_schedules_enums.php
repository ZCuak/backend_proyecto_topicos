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
        // Actualizar tipo: agregar PREVENTIVO
        DB::statement("ALTER TABLE maintenance_schedules DROP CONSTRAINT IF EXISTS maintenance_schedules_type_check");
        DB::statement("ALTER TABLE maintenance_schedules ADD CONSTRAINT maintenance_schedules_type_check CHECK (type IN ('PREVENTIVO', 'LIMPIEZA', 'REPARACIÓN'))");
        
        // Actualizar día: agregar DOMINGO
        DB::statement("ALTER TABLE maintenance_schedules DROP CONSTRAINT IF EXISTS maintenance_schedules_day_check");
        DB::statement("ALTER TABLE maintenance_schedules ADD CONSTRAINT maintenance_schedules_day_check CHECK (day IN ('LUNES', 'MARTES', 'MIÉRCOLES', 'JUEVES', 'VIERNES', 'SÁBADO', 'DOMINGO'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE maintenance_schedules DROP CONSTRAINT IF EXISTS maintenance_schedules_type_check");
        DB::statement("ALTER TABLE maintenance_schedules ADD CONSTRAINT maintenance_schedules_type_check CHECK (type IN ('LIMPIEZA', 'REPARACIÓN'))");
        
        DB::statement("ALTER TABLE maintenance_schedules DROP CONSTRAINT IF EXISTS maintenance_schedules_day_check");
        DB::statement("ALTER TABLE maintenance_schedules ADD CONSTRAINT maintenance_schedules_day_check CHECK (day IN ('LUNES', 'MARTES', 'MIÉRCOLES', 'JUEVES', 'VIERNES', 'SÁBADO'))");
    }
};
