<?php

namespace App\Services;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrationCenterService
{
    public function __construct(private readonly Migrator $migrator)
    {
    }

    public function getExecutedMigrations(): Collection
    {
        if (! Schema::hasTable('migrations')) {
            return collect();
        }

        return DB::table('migrations')
            ->select('id', 'migration', 'batch')
            ->orderByDesc('batch')
            ->orderBy('migration')
            ->get();
    }

    public function getPendingMigrations(): array
    {
        $paths = array_merge([database_path('migrations')], $this->migrator->paths());
        $files = $this->migrator->getMigrationFiles($paths);

        if (! $this->migrator->repositoryExists()) {
            return array_values(array_keys($files));
        }

        $ran = $this->migrator->getRepository()->getRan();

        return array_values(array_diff(array_keys($files), $ran));
    }

    public function previewPendingMigrations(): string
    {
        Artisan::call('migrate', ['--pretend' => true]);

        return Artisan::output();
    }

    public function runMigrations(): string
    {
        Artisan::call('migrate', ['--force' => true]);

        return Artisan::output();
    }
}
