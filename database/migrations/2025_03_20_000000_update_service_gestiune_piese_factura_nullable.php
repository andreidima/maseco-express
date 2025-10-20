<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_gestiune_piese', function (Blueprint $table) {
            $table->dropForeign(['factura_id']);
        });

        DB::statement('ALTER TABLE service_gestiune_piese MODIFY factura_id BIGINT UNSIGNED NULL');

        Schema::table('service_gestiune_piese', function (Blueprint $table) {
            $table->foreign('factura_id')
                ->references('id')
                ->on('service_ff_facturi')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_gestiune_piese', function (Blueprint $table) {
            $table->dropForeign(['factura_id']);
        });

        if (DB::table('service_gestiune_piese')->whereNull('factura_id')->exists()) {
            throw new \RuntimeException('Cannot revert migration while service_gestiune_piese contains records without an invoice.');
        }

        DB::statement('ALTER TABLE service_gestiune_piese MODIFY factura_id BIGINT UNSIGNED NOT NULL');

        Schema::table('service_gestiune_piese', function (Blueprint $table) {
            $table->foreign('factura_id')
                ->references('id')
                ->on('service_ff_facturi')
                ->cascadeOnDelete();
        });
    }
};
