<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('valabilitati_curse', function (Blueprint $table): void {
            $table->unsignedInteger('nr_ordine')->nullable()->first();
        });

        DB::statement(<<<'SQL'
            UPDATE valabilitati_curse vc
            JOIN (
                SELECT
                    id,
                    valabilitate_id,
                    (@row_num := IF(@current_valabilitate = valabilitate_id, @row_num + 1, 1)) AS row_num,
                    (@current_valabilitate := valabilitate_id) AS current_valabilitate
                FROM valabilitati_curse
                CROSS JOIN (SELECT @row_num := 0, @current_valabilitate := NULL) vars
                ORDER BY valabilitate_id, created_at, id
            ) ranked ON vc.id = ranked.id
            SET vc.nr_ordine = ranked.row_num
        SQL);

        DB::statement('ALTER TABLE valabilitati_curse MODIFY nr_ordine INT UNSIGNED NOT NULL');
    }

    public function down(): void
    {
        Schema::table('valabilitati_curse', function (Blueprint $table): void {
            $table->dropColumn('nr_ordine');
        });
    }
};
