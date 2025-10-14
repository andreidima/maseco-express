<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if ($this->databaseAlreadyContainsLegacyTables()) {
            return;
        }

        $schemaPath = database_path('schema/mysql-schema.sql');

        if (! file_exists($schemaPath)) {
            throw new \RuntimeException('Legacy schema dump not found at ' . $schemaPath);
        }

        DB::unprepared(file_get_contents($schemaPath));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left blank. Dropping the entire legacy schema is risky and should be handled manually.
    }

    private function databaseAlreadyContainsLegacyTables(): bool
    {
        if (Schema::hasTable('users')) {
            return true;
        }

        $result = DB::select(<<<'SQL'
            SELECT 1
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
              AND table_name = 'users'
            LIMIT 1
        SQL);

        return ! empty($result);
    }
};
