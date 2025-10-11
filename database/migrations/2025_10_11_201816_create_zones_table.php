<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nombre de la zona');
            $table->decimal('area', 10, 2)->nullable()->comment('Área estimada en km2');
            $table->text('description')->nullable()->comment('Descripción general');
            $table->foreignId('district_id')->constrained('districts')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zones');
    }
};
