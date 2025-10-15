<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename payment batches first so dependent tables can reference the new names.
        $this->dropLegacyForeignKeys();

        $this->renameTableIfExists('ff_plati_calupuri', 'service_ff_plati_calupuri');
        $this->renameTableIfExists('ff_plati_calupuri_fisiere', 'service_ff_plati_calupuri_fisiere');
        $this->renameTableIfExists('ff_facturi', 'service_ff_facturi');
        $this->renameTableIfExists('ff_facturi_plati', 'service_ff_facturi_plati');

        $this->addServiceForeignKeys();
    }

    public function down(): void
    {
        $this->dropServiceForeignKeys();

        $this->renameTableIfExists('service_ff_facturi_plati', 'ff_facturi_plati');
        $this->renameTableIfExists('service_ff_facturi', 'ff_facturi');
        $this->renameTableIfExists('service_ff_plati_calupuri_fisiere', 'ff_plati_calupuri_fisiere');
        $this->renameTableIfExists('service_ff_plati_calupuri', 'ff_plati_calupuri');

        $this->addLegacyForeignKeys();
    }

    private function dropLegacyForeignKeys(): void
    {
        if (Schema::hasTable('ff_facturi_plati')) {
            $this->dropForeignIfExists('ff_facturi_plati', 'ff_facturi_plati_factura_id_foreign');
            $this->dropForeignIfExists('ff_facturi_plati', 'ff_facturi_plati_calup_id_foreign');
        }

        if (Schema::hasTable('ff_plati_calupuri_fisiere')) {
            $this->dropForeignIfExists('ff_plati_calupuri_fisiere', 'ff_plati_calupuri_fisiere_plata_calup_id_foreign');
        }
    }

    private function dropServiceForeignKeys(): void
    {
        if (Schema::hasTable('service_ff_facturi_plati')) {
            $this->dropForeignIfExists('service_ff_facturi_plati', 'service_ff_facturi_plati_factura_id_foreign');
            $this->dropForeignIfExists('service_ff_facturi_plati', 'service_ff_facturi_plati_calup_id_foreign');
        }

        if (Schema::hasTable('service_ff_plati_calupuri_fisiere')) {
            $this->dropForeignIfExists('service_ff_plati_calupuri_fisiere', 'service_ff_plati_calupuri_fisiere_plata_calup_id_foreign');
        }
    }

    private function addServiceForeignKeys(): void
    {
        if (Schema::hasTable('service_ff_facturi_plati')) {
            Schema::table('service_ff_facturi_plati', function (Blueprint $table) {
                if (! $this->foreignKeyExists('service_ff_facturi_plati', 'service_ff_facturi_plati_factura_id_foreign')) {
                    $table->foreign('factura_id', 'service_ff_facturi_plati_factura_id_foreign')
                        ->references('id')
                        ->on('service_ff_facturi')
                        ->cascadeOnDelete();
                }

                if (! $this->foreignKeyExists('service_ff_facturi_plati', 'service_ff_facturi_plati_calup_id_foreign')) {
                    $table->foreign('calup_id', 'service_ff_facturi_plati_calup_id_foreign')
                        ->references('id')
                        ->on('service_ff_plati_calupuri')
                        ->cascadeOnDelete();
                }
            });
        }

        if (Schema::hasTable('service_ff_plati_calupuri_fisiere')) {
            Schema::table('service_ff_plati_calupuri_fisiere', function (Blueprint $table) {
                if (! $this->foreignKeyExists('service_ff_plati_calupuri_fisiere', 'service_ff_plati_calupuri_fisiere_plata_calup_id_foreign')) {
                    $table->foreign('plata_calup_id', 'service_ff_plati_calupuri_fisiere_plata_calup_id_foreign')
                        ->references('id')
                        ->on('service_ff_plati_calupuri')
                        ->cascadeOnDelete();
                }
            });
        }
    }

    private function addLegacyForeignKeys(): void
    {
        if (Schema::hasTable('ff_facturi_plati')) {
            Schema::table('ff_facturi_plati', function (Blueprint $table) {
                if (! $this->foreignKeyExists('ff_facturi_plati', 'ff_facturi_plati_factura_id_foreign')) {
                    $table->foreign('factura_id', 'ff_facturi_plati_factura_id_foreign')
                        ->references('id')
                        ->on('ff_facturi')
                        ->cascadeOnDelete();
                }

                if (! $this->foreignKeyExists('ff_facturi_plati', 'ff_facturi_plati_calup_id_foreign')) {
                    $table->foreign('calup_id', 'ff_facturi_plati_calup_id_foreign')
                        ->references('id')
                        ->on('ff_plati_calupuri')
                        ->cascadeOnDelete();
                }
            });
        }

        if (Schema::hasTable('ff_plati_calupuri_fisiere')) {
            Schema::table('ff_plati_calupuri_fisiere', function (Blueprint $table) {
                if (! $this->foreignKeyExists('ff_plati_calupuri_fisiere', 'ff_plati_calupuri_fisiere_plata_calup_id_foreign')) {
                    $table->foreign('plata_calup_id', 'ff_plati_calupuri_fisiere_plata_calup_id_foreign')
                        ->references('id')
                        ->on('ff_plati_calupuri')
                        ->cascadeOnDelete();
                }
            });
        }
    }

    private function renameTableIfExists(string $from, string $to): void
    {
        if (Schema::hasTable($from) && ! Schema::hasTable($to)) {
            Schema::rename($from, $to);
        }
    }

    private function dropForeignIfExists(string $table, string $constraint): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if (! $this->foreignKeyExists($table, $constraint)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($constraint) {
            $table->dropForeign($constraint);
        });
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        $connection = Schema::getConnection();
        $databaseName = $connection->getDatabaseName();

        return DB::connection($connection->getName())
            ->table('information_schema.referential_constraints')
            ->where('constraint_schema', $databaseName)
            ->where('constraint_name', $constraint)
            ->where('table_name', $table)
            ->exists();
    }
};
