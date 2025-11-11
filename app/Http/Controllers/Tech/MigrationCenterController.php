<?php

namespace App\Http\Controllers\Tech;

use App\Http\Controllers\Controller;
use App\Services\MigrationCenterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class MigrationCenterController extends Controller
{
    public function index(Request $request, MigrationCenterService $service): View
    {
        return view('tech.migrations.index', [
            'executedMigrations' => $service->getExecutedMigrations(),
            'pendingMigrations' => $service->getPendingMigrations(),
            'previewOutput' => $request->session()->get('migration_preview'),
            'executionOutput' => $request->session()->get('migration_output'),
            'statusMessage' => $request->session()->get('migration_status'),
            'statusLevel' => $request->session()->get('migration_status_level', 'info'),
            'seederOutput' => $request->session()->get('seeder_output'),
            'seederStatus' => $request->session()->get('seeder_status'),
            'seederStatusLevel' => $request->session()->get('seeder_status_level', 'info'),
        ]);
    }

    public function seeders(Request $request, MigrationCenterService $service): View
    {
        $availableSeeders = $service->availableSeeders();

        return view('tech.seeders.index', [
            'seeders' => $availableSeeders,
            'seederOutput' => $request->session()->get('seeder_output'),
            'seederStatus' => $request->session()->get('seeder_status'),
            'seederStatusLevel' => $request->session()->get('seeder_status_level', 'info'),
            'lastRunSeeder' => $request->session()->get('seeder_last_run'),
        ]);
    }

    public function seeders(Request $request, MigrationCenterService $service): View
    {
        $availableSeeders = $service->availableSeeders();

        return view('tech.seeders.index', [
            'seeders' => $availableSeeders,
            'seederOutput' => $request->session()->get('seeder_output'),
            'seederStatus' => $request->session()->get('seeder_status'),
            'seederStatusLevel' => $request->session()->get('seeder_status_level', 'info'),
            'lastRunSeeder' => $request->session()->get('seeder_last_run'),
        ]);
    }

    public function showSeeders(Request $request, MigrationCenterService $service): View
    {
        $availableSeeders = $service->availableSeeders();

        return view('tech.seeders.index', [
            'seeders' => $availableSeeders,
            'seederOutput' => $request->session()->get('seeder_output'),
            'seederStatus' => $request->session()->get('seeder_status'),
            'seederStatusLevel' => $request->session()->get('seeder_status_level', 'info'),
            'lastRunSeeder' => $request->session()->get('seeder_last_run'),
        ]);
    }

    public function preview(MigrationCenterService $service): RedirectResponse
    {
        try {
            $output = $service->previewPendingMigrations();

            return redirect()
                ->route('tech.migrations.index')
                ->with([
                    'migration_preview' => $output,
                    'migration_status' => 'Preview generated. Review the statements below before running the migrations.',
                    'migration_status_level' => 'info',
                ]);
        } catch (Throwable $exception) {
            return redirect()
                ->route('tech.migrations.index')
                ->with([
                    'migration_status' => 'Failed to generate migration preview: ' . $exception->getMessage(),
                    'migration_status_level' => 'danger',
                ]);
        }
    }

    public function run(MigrationCenterService $service): RedirectResponse
    {
        try {
            $output = $service->runMigrations();

            return redirect()
                ->route('tech.migrations.index')
                ->with([
                    'migration_output' => $output,
                    'migration_status' => 'Migrations executed. Review the execution log below.',
                    'migration_status_level' => 'success',
                ]);
        } catch (Throwable $exception) {
            return redirect()
                ->route('tech.migrations.index')
                ->with([
                    'migration_status' => 'Migration failed: ' . $exception->getMessage(),
                    'migration_status_level' => 'danger',
                ]);
        }
    }

    public function runSeeder(Request $request, MigrationCenterService $service): RedirectResponse
    {
        $availableSeeders = $service->availableSeeders();

        if ($availableSeeders->isEmpty()) {
            return redirect()
                ->route('tech.seeders.index')
                ->with([
                    'seeder_status' => 'No seeders are currently configured for execution.',
                    'seeder_status_level' => 'warning',
                ]);
        }

        $validated = $request->validate([
            'seeder' => ['required', Rule::in($availableSeeders->keys())],
        ]);

        $seederClass = $validated['seeder'];
        $seederMeta = $availableSeeders->get($seederClass);
        $seederName = $seederMeta['name'] ?? class_basename($seederClass);

        try {
            $output = $service->runSeeder($seederClass);

            return redirect()
                ->route('tech.seeders.index')
                ->with([
                    'seeder_output' => $output,
                    'seeder_status' => sprintf('Seeder "%s" executed successfully. Review the run log below.', $seederName),
                    'seeder_status_level' => 'success',
                    'seeder_last_run' => $seederClass,
                ]);
        } catch (Throwable $exception) {
            return redirect()
                ->route('tech.seeders.index')
                ->with([
                    'seeder_output' => $exception->getMessage(),
                    'seeder_status' => sprintf('Seeder "%s" failed: %s', $seederName, $exception->getMessage()),
                    'seeder_status_level' => 'danger',
                    'seeder_last_run' => $seederClass,
                ]);
        }
    }
}
