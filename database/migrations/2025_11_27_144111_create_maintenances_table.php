<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id(); // ID único
            $table->string('name', 255); // Nombre del mantenimiento
            $table->date('start_date'); // Fecha de inicio
            $table->date('end_date'); // Fecha de fin
            $table->timestamps(); // created_at y updated_at
            $table->softDeletes(); // deleted_at para eliminación lógica
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};