<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('valabilitati_curse', function (Blueprint $table): void {
            $table->foreignId('incarcare_tara_id')->nullable()->after('incarcare_cod_postal')->constrained('tari');
            $table->foreignId('descarcare_tara_id')->nullable()->after('descarcare_cod_postal')->constrained('tari');
            $table->unsignedInteger('km_bord')->nullable()->after('observatii');
        });
    }

    public function down(): void
    {
        Schema::table('valabilitati_curse', function (Blueprint $table): void {
            $table->dropColumn('km_bord');
            $table->dropConstrainedForeignId('descarcare_tara_id');
            $table->dropConstrainedForeignId('incarcare_tara_id');
        });
    }
};
