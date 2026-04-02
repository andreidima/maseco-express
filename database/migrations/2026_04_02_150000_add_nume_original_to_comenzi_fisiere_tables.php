<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comenzi_fisiere', function (Blueprint $table) {
            $table->string('nume_original')->nullable()->after('nume');
        });

        Schema::table('comenzi_fisiere_istoric', function (Blueprint $table) {
            $table->string('nume_original')->nullable()->after('nume');
        });

        $this->backfillOriginalNames('comenzi_fisiere', 'id');
        $this->backfillOriginalNames('comenzi_fisiere_istoric', 'id_pk');
    }

    public function down(): void
    {
        Schema::table('comenzi_fisiere_istoric', function (Blueprint $table) {
            $table->dropColumn('nume_original');
        });

        Schema::table('comenzi_fisiere', function (Blueprint $table) {
            $table->dropColumn('nume_original');
        });
    }

    protected function backfillOriginalNames(string $table, string $keyColumn): void
    {
        DB::table($table)
            ->whereNull('nume_original')
            ->orderBy($keyColumn)
            ->chunkById(500, function ($rows) use ($table, $keyColumn) {
                foreach ($rows as $row) {
                    DB::table($table)
                        ->where($keyColumn, $row->{$keyColumn})
                        ->update([
                            'nume_original' => $this->normalizeLegacyDisplayName($row->nume),
                        ]);
                }
            }, $keyColumn);
    }

    protected function normalizeLegacyDisplayName(?string $filename): string
    {
        $filename = (string) $filename;

        if (preg_match('/^(?<base>.+)\.(?<extension>[^.]+)(?<suffix>3[0-9a-f]{13})(?<trailingDot>\.?)$/iu', $filename, $matches)) {
            return $matches['base'] . '_' . $matches['suffix'] . '.' . $matches['extension'];
        }

        return $filename;
    }
};
