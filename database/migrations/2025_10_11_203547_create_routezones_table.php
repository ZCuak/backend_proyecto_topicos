<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('routezones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('routes')->cascadeOnDelete();
            $table->foreignId('zone_id')->constrained('zones')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['route_id', 'zone_id'], 'unique_route_zone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routezones');
    }
};
