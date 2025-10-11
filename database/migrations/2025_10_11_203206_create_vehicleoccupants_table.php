<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicleoccupants', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['ACTIVO', 'INACTIVO'])->default('ACTIVO')->comment('Estado de asignación');
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('usertype_id')->constrained('usertypes')->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('vehicleoccupants');
    }
};
