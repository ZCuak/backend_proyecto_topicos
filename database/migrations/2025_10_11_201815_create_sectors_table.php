<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sectors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nombre del sector dentro de una zona');
            $table->decimal('area', 10, 2)->nullable()->comment('Área total en km2');
            $table->text('description')->nullable()->comment('Descripción o detalles del sector');
            $table->foreignId('district_id')->constrained('districts')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sectors');
    }
};
