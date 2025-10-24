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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->integer('compaction_capacity')->nullable()->comment('Capacidad de compactación en metros cúbicos');
            $table->integer('fuel_capacity')->nullable()->comment('Capacidad de combustible en litros');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['compaction_capacity', 'fuel_capacity']);
        });
    }
};
