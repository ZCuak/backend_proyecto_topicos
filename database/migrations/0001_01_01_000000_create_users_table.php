<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usertypes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Tipo de usuario o función, ej. Conductor, Ayudante, Supervisor');
            $table->boolean('is_system')->default(false)->comment('Indica si es un tipo predefinido del sistema que no puede eliminarse');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('dni', 8)->unique()->comment('Documento nacional de identidad');
            $table->string('name')->comment('Nombres completos');
            $table->string('lastname')->comment('Apellidos completos');
            $table->string('email')->unique()->comment('Correo electrónico institucional');
            $table->string('license')->nullable()->comment('Número de licencia de conducir si aplica');
            $table->string('phone', 15)->nullable()->comment('Teléfono de contacto');
            $table->string('address')->nullable()->comment('Dirección del empleado');
            $table->string('photo_path')->nullable()->comment('Ruta de la foto del personal');
            $table->foreignId('usertype_id')->constrained('usertypes')->cascadeOnDelete();
            $table->enum('status', ['ACTIVO', 'INACTIVO'])->default('ACTIVO')->comment('Estado actual del personal');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usertypes');
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
