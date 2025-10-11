<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('zonecoords', function (Blueprint $table) {
            $table->id();
            $table->decimal('latitude', 10, 7)->comment('Latitud del punto del perímetro');
            $table->decimal('longitude', 10, 7)->comment('Longitud del punto del perímetro');
            $table->foreignId('zone_id')->constrained('zones')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zonecoords');
    }
};
