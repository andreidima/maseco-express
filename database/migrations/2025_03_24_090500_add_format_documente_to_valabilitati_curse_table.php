<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('valabilitati_curse', function (Blueprint $table): void {
            $table->string('format_documente', 20)->nullable()->after('nr_cursa');
        });
    }

    public function down(): void
    {
        Schema::table('valabilitati_curse', function (Blueprint $table): void {
            $table->dropColumn('format_documente');
        });
    }
};
