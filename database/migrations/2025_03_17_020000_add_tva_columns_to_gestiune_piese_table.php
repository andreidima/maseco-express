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
        Schema::table('service_gestiune_piese', function (Blueprint $table) {
            $table->decimal('tva_cota', 5, 2)->nullable()->after('pret');
            $table->decimal('valoare_tva', 12, 2)->nullable()->after('tva_cota');
            $table->decimal('pret_brut', 12, 2)->nullable()->after('valoare_tva');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_gestiune_piese', function (Blueprint $table) {
            $table->dropColumn(['tva_cota', 'valoare_tva', 'pret_brut']);
        });
    }
};
