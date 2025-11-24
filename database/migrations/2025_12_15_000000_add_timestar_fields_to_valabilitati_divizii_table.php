<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('valabilitati_divizii', function (Blueprint $table): void {
            $table->decimal('pret_km_bord', 10, 3)->nullable()->after('pret_km_cu_taxa');
            $table->decimal('pret_nr_zile_lucrate', 10, 3)->nullable()->after('pret_km_bord');
        });
    }

    public function down(): void
    {
        Schema::table('valabilitati_divizii', function (Blueprint $table): void {
            $table->dropColumn(['pret_km_bord', 'pret_nr_zile_lucrate']);
        });
    }
};
