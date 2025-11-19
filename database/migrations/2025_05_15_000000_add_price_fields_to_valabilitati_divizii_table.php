<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('valabilitati_divizii', function (Blueprint $table): void {
            $table->decimal('pret_km_gol', 12, 3)->nullable()->after('nume');
            $table->decimal('pret_km_plin', 12, 3)->nullable()->after('pret_km_gol');
            $table->decimal('pret_km_cu_taxa', 12, 3)->nullable()->after('pret_km_plin');
        });
    }

    public function down(): void
    {
        Schema::table('valabilitati_divizii', function (Blueprint $table): void {
            $table->dropColumn([
                'pret_km_gol',
                'pret_km_plin',
                'pret_km_cu_taxa',
            ]);
        });
    }
};
