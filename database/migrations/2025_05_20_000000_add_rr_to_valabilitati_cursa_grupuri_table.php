<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('valabilitati_cursa_grupuri', function (Blueprint $table): void {
            $table->string('rr')->nullable()->after('nume');
        });
    }

    public function down(): void
    {
        Schema::table('valabilitati_cursa_grupuri', function (Blueprint $table): void {
            $table->dropColumn('rr');
        });
    }
};
