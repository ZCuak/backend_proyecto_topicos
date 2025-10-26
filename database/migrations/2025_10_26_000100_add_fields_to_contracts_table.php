<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Agregar columnas básicas
        Schema::table('contracts', function (Blueprint $table) {
            // colocar salary después de date_end para evitar dependencia de una columna 'description' eliminada
            $table->decimal('salary', 10, 2)->after('date_end');
            $table->unsignedBigInteger('position_id')->after('vacation_days_per_year');
            $table->unsignedBigInteger('department_id')->after('position_id');
            $table->integer('probation_period_months')->after('department_id');
            $table->text('termination_reason')->nullable()->after('probation_period_months');
        });

        // Agregar FK a departments (debe existir)
        if (Schema::hasTable('departments')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->foreign('department_id')->references('id')->on('departments');
            });
        }

        // Agregar FK a positions solo si existe la tabla usertypes
        if (Schema::hasTable('usertypes')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->foreign('position_id')->references('id')->on('usertypes');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar claves foráneas si existen y luego las columnas
        if (Schema::hasTable('contracts')) {
            // department_id
            if (Schema::hasColumn('contracts', 'department_id')) {
                Schema::table('contracts', function (Blueprint $table) {
                    // Intentar eliminar la FK si existe
                    if (Schema::hasTable('departments')) {
                        $table->dropForeign(['department_id']);
                    }
                    $table->dropColumn('department_id');
                });
            }

            // position_id
            if (Schema::hasColumn('contracts', 'position_id')) {
                Schema::table('contracts', function (Blueprint $table) {
                    if (Schema::hasTable('usertypes')) {
                        $table->dropForeign(['position_id']);
                    }
                    $table->dropColumn('position_id');
                });
            }

            // Otras columnas
            Schema::table('contracts', function (Blueprint $table) {
                if (Schema::hasColumn('contracts', 'salary')) {
                    $table->dropColumn('salary');
                }
                if (Schema::hasColumn('contracts', 'probation_period_months')) {
                    $table->dropColumn('probation_period_months');
                }
                if (Schema::hasColumn('contracts', 'termination_reason')) {
                    $table->dropColumn('termination_reason');
                }
            });
        }
    }
};
