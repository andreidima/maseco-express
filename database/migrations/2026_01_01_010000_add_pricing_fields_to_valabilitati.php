<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('valabilitati', function (Blueprint $table) {
            $table->decimal('flash_pret_km_gol', 12, 3)->nullable()->after('divizie_id');
            $table->decimal('flash_pret_km_plin', 12, 3)->nullable()->after('flash_pret_km_gol');
            $table->decimal('flash_pret_km_cu_taxa', 12, 3)->nullable()->after('flash_pret_km_plin');
            $table->decimal('flash_contributie_zilnica', 12, 3)->nullable()->after('flash_pret_km_cu_taxa');
            $table->decimal('timestar_pret_km_bord', 12, 3)->nullable()->after('flash_contributie_zilnica');
            $table->decimal('timestar_pret_nr_zile_lucrate', 12, 3)->nullable()->after('timestar_pret_km_bord');
        });

        DB::table('valabilitati as v')
            ->join('valabilitati_divizii as d', 'v.divizie_id', '=', 'd.id')
            ->update([
                'flash_pret_km_gol' => DB::raw('d.flash_pret_km_gol'),
                'flash_pret_km_plin' => DB::raw('d.flash_pret_km_plin'),
                'flash_pret_km_cu_taxa' => DB::raw('d.flash_pret_km_cu_taxa'),
                'flash_contributie_zilnica' => DB::raw('d.flash_contributie_zilnica'),
                'timestar_pret_km_bord' => DB::raw('d.timestar_pret_km_bord'),
                'timestar_pret_nr_zile_lucrate' => DB::raw('d.timestar_pret_nr_zile_lucrate'),
            ]);
    }

    public function down(): void
    {
        Schema::table('valabilitati', function (Blueprint $table) {
            $table->dropColumn([
                'flash_pret_km_gol',
                'flash_pret_km_plin',
                'flash_pret_km_cu_taxa',
                'flash_contributie_zilnica',
                'timestar_pret_km_bord',
                'timestar_pret_nr_zile_lucrate',
            ]);
        });
    }
};
