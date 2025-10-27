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
        if (Schema::hasTable('contracts') && Schema::hasColumn('contracts', 'description')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('contracts') && !Schema::hasColumn('contracts', 'description')) {
            Schema::table('contracts', function (Blueprint $table) {
                // Re-add description after date_end if possible
                if (Schema::hasColumn('contracts', 'date_end')) {
                    $table->text('description')->nullable()->after('date_end');
                } else {
                    $table->text('description')->nullable();
                }
            });
        }
    }
};
