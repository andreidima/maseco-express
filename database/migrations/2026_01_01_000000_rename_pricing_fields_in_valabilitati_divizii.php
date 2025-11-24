<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('valabilitati_divizii', function (Blueprint $table) {
            $table->decimal('flash_pret_km_gol', 12, 3)->nullable()->after('nume');
            $table->decimal('flash_pret_km_plin', 12, 3)->nullable()->after('flash_pret_km_gol');
            $table->decimal('flash_pret_km_cu_taxa', 12, 3)->nullable()->after('flash_pret_km_plin');
            $table->decimal('flash_contributie_zilnica', 12, 3)->nullable()->after('flash_pret_km_cu_taxa');
            $table->decimal('timestar_pret_km_bord', 12, 3)->nullable()->after('flash_contributie_zilnica');
            $table->decimal('timestar_pret_nr_zile_lucrate', 12, 3)->nullable()->after('timestar_pret_km_bord');
        });

        DB::table('valabilitati_divizii')->select([
            'id',
            'pret_km_gol',
            'pret_km_plin',
            'pret_km_cu_taxa',
            'pret_km_bord',
            'pret_nr_zile_lucrate',
            'contributie_zilnica',
        ])->orderBy('id')->chunkById(100, function ($divizii): void {
            foreach ($divizii as $divizie) {
                DB::table('valabilitati_divizii')
                    ->where('id', $divizie->id)
                    ->update([
                        'flash_pret_km_gol' => $divizie->pret_km_gol,
                        'flash_pret_km_plin' => $divizie->pret_km_plin,
                        'flash_pret_km_cu_taxa' => $divizie->pret_km_cu_taxa,
                        'flash_contributie_zilnica' => $divizie->contributie_zilnica,
                        'timestar_pret_km_bord' => $divizie->pret_km_bord,
                        'timestar_pret_nr_zile_lucrate' => $divizie->pret_nr_zile_lucrate,
                    ]);
            }
        });

        Schema::table('valabilitati_divizii', function (Blueprint $table) {
            $table->dropColumn([
                'pret_km_gol',
                'pret_km_plin',
                'pret_km_cu_taxa',
                'pret_km_bord',
                'pret_nr_zile_lucrate',
                'contributie_zilnica',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('valabilitati_divizii', function (Blueprint $table) {
            $table->decimal('pret_km_gol', 12, 3)->nullable()->after('nume');
            $table->decimal('pret_km_plin', 12, 3)->nullable()->after('pret_km_gol');
            $table->decimal('pret_km_cu_taxa', 12, 3)->nullable()->after('pret_km_plin');
            $table->decimal('contributie_zilnica', 12, 3)->nullable()->after('pret_km_cu_taxa');
            $table->decimal('pret_km_bord', 12, 3)->nullable()->after('contributie_zilnica');
            $table->decimal('pret_nr_zile_lucrate', 12, 3)->nullable()->after('pret_km_bord');
        });

        DB::table('valabilitati_divizii')->select([
            'id',
            'flash_pret_km_gol',
            'flash_pret_km_plin',
            'flash_pret_km_cu_taxa',
            'flash_contributie_zilnica',
            'timestar_pret_km_bord',
            'timestar_pret_nr_zile_lucrate',
        ])->orderBy('id')->chunkById(100, function ($divizii): void {
            foreach ($divizii as $divizie) {
                DB::table('valabilitati_divizii')
                    ->where('id', $divizie->id)
                    ->update([
                        'pret_km_gol' => $divizie->flash_pret_km_gol,
                        'pret_km_plin' => $divizie->flash_pret_km_plin,
                        'pret_km_cu_taxa' => $divizie->flash_pret_km_cu_taxa,
                        'contributie_zilnica' => $divizie->flash_contributie_zilnica,
                        'pret_km_bord' => $divizie->timestar_pret_km_bord,
                        'pret_nr_zile_lucrate' => $divizie->timestar_pret_nr_zile_lucrate,
                    ]);
            }
        });

        Schema::table('valabilitati_divizii', function (Blueprint $table) {
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
