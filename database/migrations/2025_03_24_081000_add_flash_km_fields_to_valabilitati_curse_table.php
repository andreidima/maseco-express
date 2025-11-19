<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('valabilitati_curse', function (Blueprint $table): void {
            $table->unsignedInteger('km_maps_gol')->nullable()->after('km_maps');
            $table->unsignedInteger('km_maps_plin')->nullable()->after('km_maps_gol');
            $table->unsignedInteger('km_cu_taxa')->nullable()->after('km_maps_plin');
            $table->unsignedInteger('km_flash_gol')->nullable()->after('km_cu_taxa');
            $table->unsignedInteger('km_flash_plin')->nullable()->after('km_flash_gol');
        });
    }

    public function down(): void
    {
        Schema::table('valabilitati_curse', function (Blueprint $table): void {
            $table->dropColumn([
                'km_maps_gol',
                'km_maps_plin',
                'km_cu_taxa',
                'km_flash_gol',
                'km_flash_plin',
            ]);
        });
    }
};
