<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing('service_ff_facturi', 'sff_data_scadenta_furnizor_idx', ['data_scadenta', 'denumire_furnizor']);
        $this->addIndexIfMissing('service_ff_facturi', 'sff_numar_factura_idx', ['numar_factura']);
        $this->addIndexIfMissing('service_ff_facturi', 'sff_moneda_idx', ['moneda']);
        $this->addIndexIfMissing('service_ff_facturi', 'sff_denumire_furnizor_idx', ['denumire_furnizor']);
        $this->addIndexIfMissing('service_ff_facturi', 'sff_departament_vehicul_idx', ['departament_vehicul']);

        $this->addIndexIfMissing('service_masina_service_entries', 'smse_piesa_tip_idx', ['gestiune_piesa_id', 'tip']);
    }

    public function down(): void
    {
        $this->dropIndexIfExists('service_masina_service_entries', 'smse_piesa_tip_idx');

        $this->dropIndexIfExists('service_ff_facturi', 'sff_departament_vehicul_idx');
        $this->dropIndexIfExists('service_ff_facturi', 'sff_denumire_furnizor_idx');
        $this->dropIndexIfExists('service_ff_facturi', 'sff_moneda_idx');
        $this->dropIndexIfExists('service_ff_facturi', 'sff_numar_factura_idx');
        $this->dropIndexIfExists('service_ff_facturi', 'sff_data_scadenta_furnizor_idx');
    }

    /**
     * @param array<int, string> $columns
     */
    private function addIndexIfMissing(string $table, string $index, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return;
            }
        }

        if ($this->indexExists($table, $index)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($columns, $index) {
            $table->index($columns, $index);
        });
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (! Schema::hasTable($table) || ! $this->indexExists($table, $index)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($index) {
            $table->dropIndex($index);
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $databaseName = $connection->getDatabaseName();

        return DB::connection($connection->getName())
            ->table('information_schema.statistics')
            ->where('table_schema', $databaseName)
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};
