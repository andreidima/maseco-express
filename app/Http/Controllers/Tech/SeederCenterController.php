<?php

namespace App\Http\Controllers\Tech;

use App\Http\Controllers\Controller;
use App\Services\MigrationCenterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class SeederCenterController extends Controller
{
    public function index(Request $request, MigrationCenterService $service): View
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

    public function run(Request $request, MigrationCenterService $service): RedirectResponse
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
