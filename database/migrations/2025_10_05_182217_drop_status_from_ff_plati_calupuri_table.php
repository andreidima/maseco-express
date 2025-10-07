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
        if (!Schema::hasTable('ff_plati_calupuri') || !Schema::hasColumn('ff_plati_calupuri', 'status')) {
            return;
        }

        Schema::table('ff_plati_calupuri', function (Blueprint $table) {
            $table->dropIndex('ff_plati_calupuri_status_data_plata_index');
            $table->dropColumn('status');
        });

        Schema::table('ff_plati_calupuri', function (Blueprint $table) {
            $table->index('data_plata');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('ff_plati_calupuri') || Schema::hasColumn('ff_plati_calupuri', 'status')) {
            return;
        }

        Schema::table('ff_plati_calupuri', function (Blueprint $table) {
            $table->string('status', 20)->default('deschis')->after('observatii');
            $table->index(['status', 'data_plata']);
        });
    }
};
