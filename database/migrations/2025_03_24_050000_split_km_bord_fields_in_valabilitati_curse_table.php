<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('valabilitati_curse', function (Blueprint $table): void {
            $table->unsignedInteger('km_bord_incarcare')->nullable()->after('observatii');
            $table->unsignedInteger('km_bord_descarcare')->nullable()->after('km_bord_incarcare');
        });

        DB::table('valabilitati_curse')
            ->whereNotNull('km_bord')
            ->update([
                'km_bord_incarcare' => DB::raw('km_bord'),
                'km_bord_descarcare' => DB::raw('km_bord'),
            ]);

        Schema::table('valabilitati_curse', function (Blueprint $table): void {
            $table->dropColumn('km_bord');
        });
    }

    public function down(): void
    {
        Schema::table('valabilitati_curse', function (Blueprint $table): void {
            $table->unsignedInteger('km_bord')->nullable()->after('observatii');
        });

        DB::statement('UPDATE valabilitati_curse SET km_bord = COALESCE(km_bord_incarcare, km_bord_descarcare)');

        Schema::table('valabilitati_curse', function (Blueprint $table): void {
            $table->dropColumn(['km_bord_incarcare', 'km_bord_descarcare']);
        });
    }
};
