<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('valabilitati_divizii', function (Blueprint $table): void {
            $table->decimal('contributie_zilnica', 10, 3)->nullable()->after('pret_km_cu_taxa');
        });
    }

    public function down(): void
    {
        Schema::table('valabilitati_divizii', function (Blueprint $table): void {
            $table->dropColumn('contributie_zilnica');
        });
    }
};
