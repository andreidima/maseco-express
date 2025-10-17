<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_masina_service_entries', function (Blueprint $table) {
            $table->dropForeign(['gestiune_piesa_id']);
        });

        Schema::table('service_masina_service_entries', function (Blueprint $table) {
            $table->foreign('gestiune_piesa_id')
                ->references('id')
                ->on('service_gestiune_piese')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_masina_service_entries', function (Blueprint $table) {
            $table->dropForeign(['gestiune_piesa_id']);
        });

        Schema::table('service_masina_service_entries', function (Blueprint $table) {
            $table->foreign('gestiune_piesa_id')
                ->references('id')
                ->on('service_gestiune_piese')
                ->nullOnDelete();
        });
    }
};
