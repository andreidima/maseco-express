<?php

namespace App\Services;

use Database\Seeders\RolesTableSeeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class MigrationCenterService
{
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
        $migrations = $this->discoverMigrationNames();

        if (! Schema::hasTable('migrations')) {
            return $migrations;
        }

        $ran = DB::table('migrations')->pluck('migration')->all();

        return collect($migrations)
            ->diff($ran)
            ->values()
            ->all();
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

    public function runSeeder(?string $seederClass = null): string
    {
        $class = $seederClass ?? RolesTableSeeder::class;

        Artisan::call('db:seed', [
            '--class' => $class,
            '--force' => true,
        ]);

        return Artisan::output();
    }

    private function discoverMigrationNames(): array
    {
        $paths = array_merge([database_path('migrations')], $this->additionalMigrationPaths());

        return collect($paths)
            ->filter(fn ($path) => is_dir($path))
            ->flatMap(function ($path) {
                return collect(glob($path . DIRECTORY_SEPARATOR . '*.php') ?: [])
                    ->map(fn ($file) => Str::replaceLast('.php', '', basename($file)));
            })
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function additionalMigrationPaths(): array
    {
        if (! app()->bound('migrator')) {
            return [];
        }

        try {
            return app('migrator')->paths();
        } catch (Throwable) {
            return [];
        }
    }
}
