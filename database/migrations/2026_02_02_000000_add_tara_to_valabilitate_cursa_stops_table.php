<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('valabilitate_cursa_stops', function (Blueprint $table) {
            $table->string('tara')->nullable()->after('localitate');
        });

        DB::table('valabilitate_cursa_stops as stops')
            ->join('valabilitati_curse as curse', 'stops.valabilitate_cursa_id', '=', 'curse.id')
            ->leftJoin('tari as tara_incarcare', 'tara_incarcare.id', '=', 'curse.incarcare_tara_id')
            ->leftJoin('tari as tara_descarcare', 'tara_descarcare.id', '=', 'curse.descarcare_tara_id')
            ->select(
                'stops.id as id',
                'stops.type',
                'tara_incarcare.nume as tara_incarcare',
                'tara_descarcare.nume as tara_descarcare'
            )
            ->orderBy('stops.id')
            ->chunkById(500, function ($rows): void {
                foreach ($rows as $row) {
                    $tara = $row->type === 'incarcare' ? $row->tara_incarcare : $row->tara_descarcare;

                    if ($tara === null || $tara === '') {
                        continue;
                    }

                    DB::table('valabilitate_cursa_stops')
                        ->where('id', $row->id)
                        ->update(['tara' => $tara]);
                }
            }, 'stops.id', 'id');
    }

    public function down(): void
    {
        Schema::table('valabilitate_cursa_stops', function (Blueprint $table) {
            $table->dropColumn('tara');
        });
    }
};
