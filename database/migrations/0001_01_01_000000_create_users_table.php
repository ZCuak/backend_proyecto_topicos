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
            $table->string('username')->unique()->comment('Nombre de usuario para login');
            $table->string('dni', 8)->unique()->comment('Documento nacional de identidad');
            $table->string('firstname')->comment('Nombres del usuario');
            $table->string('lastname')->comment('Apellidos del usuario');
            $table->date('birthdate')->nullable()->comment('Fecha de nacimiento');
            $table->string('license')->nullable()->comment('Licencia de conducir si aplica');
            $table->string('address')->nullable()->comment('Dirección del usuario');
            $table->string('email')->unique()->comment('Correo electrónico institucional');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->comment('Contraseña cifrada');
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->rememberToken();
            $table->foreignId('current_team_id')->nullable()->comment('Integración con Jetstream o equipo actual');
            $table->string('profile_photo_path')->nullable()->comment('Ruta de la foto del usuario');
            $table->foreignId('usertype_id')->constrained('usertypes')->cascadeOnDelete();
            // $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->enum('status', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
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
