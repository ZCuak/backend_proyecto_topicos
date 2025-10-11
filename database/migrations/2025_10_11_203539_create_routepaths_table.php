<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('routepaths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('routes')->cascadeOnDelete();
            $table->decimal('latitude', 10, 7)->comment('Latitud del punto del trayecto');
            $table->decimal('longitude', 10, 7)->comment('Longitud del punto del trayecto');
            $table->integer('order')->default(0)->comment('Orden secuencial del punto en la ruta');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routepaths');
    }
};
