<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vacations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('year')->comment('Año de las vacaciones');
            $table->integer('days_programmed')->default(0)->comment('Días programados');
            $table->integer('days_pending')->default(0)->comment('Días pendientes');
            $table->integer('max_days')->default(30)->comment('Máximo permitido por año');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacations');
    }
};
