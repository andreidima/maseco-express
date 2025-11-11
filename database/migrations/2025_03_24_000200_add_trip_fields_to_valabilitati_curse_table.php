<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('valabilitati_curse', function (Blueprint $table) {
            $table->string('descarcare_tara', 2)->nullable()->after('localitate_sosire');
            $table->time('ora')->nullable()->after('sosire_la');
            $table->boolean('ultima_cursa')->default(false)->after('ora');
        });
    }

    public function down(): void
    {
        Schema::table('valabilitati_curse', function (Blueprint $table) {
            $table->dropColumn(['descarcare_tara', 'ora', 'ultima_cursa']);
        });
    }
};
