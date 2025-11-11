<?php

namespace App\Http\Controllers\Tech;

use App\Http\Controllers\Controller;
use App\Services\MigrationCenterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class MigrationDashboardController extends Controller
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

}
