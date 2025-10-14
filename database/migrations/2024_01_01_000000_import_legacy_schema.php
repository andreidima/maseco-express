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
        if (Schema::hasTable('users')) {
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
};
