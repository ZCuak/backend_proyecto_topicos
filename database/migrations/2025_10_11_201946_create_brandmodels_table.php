<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('brandmodels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nombre del modelo, ej. Hilux');
            $table->string('code')->comment('A001');
            $table->text('description');
            $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brandmodels');
    }
};
