<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('valabilitati_curse', function (Blueprint $table): void {
            $table->unsignedSmallInteger('incarcare_tara_id')->nullable()->after('incarcare_cod_postal');
            $table->unsignedSmallInteger('descarcare_tara_id')->nullable()->after('descarcare_cod_postal');
            $table
                ->foreign('incarcare_tara_id')
                ->references('id')
                ->on('tari')
                ->nullOnDelete();
            $table
                ->foreign('descarcare_tara_id')
                ->references('id')
                ->on('tari')
                ->nullOnDelete();
            $table->unsignedInteger('km_bord')->nullable()->after('observatii');
        });
    }

    public function down(): void
    {
        Schema::table('valabilitati_curse', function (Blueprint $table): void {
            $table->dropColumn('km_bord');
            $table->dropForeign(['descarcare_tara_id']);
            $table->dropForeign(['incarcare_tara_id']);
            $table->dropColumn(['descarcare_tara_id', 'incarcare_tara_id']);
        });
    }
};
