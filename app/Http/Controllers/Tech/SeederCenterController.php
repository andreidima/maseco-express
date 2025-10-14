<?php

namespace App\Http\Controllers\Tech;

use App\Http\Controllers\Controller;
use App\Services\SeederCenterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Throwable;

class SeederCenterController extends Controller
{
    public function index(Request $request, SeederCenterService $service): View
    {
        return view('tech.seeders.index', [
            'availableSeeders' => $service->getAvailableSeeders(),
            'executionOutput' => $request->session()->get('seeder_output'),
            'statusMessage' => $request->session()->get('seeder_status'),
            'statusLevel' => $request->session()->get('seeder_status_level', 'info'),
            'selectedSeeder' => $request->session()->get('seeder_selected'),
        ]);
    }

    public function run(Request $request, SeederCenterService $service): RedirectResponse
    {
        $availableSeeders = $service->getAvailableSeeders();
        $allowedClasses = $availableSeeders->pluck('class')->all();

        $rules = [
            'seeder' => [
                'nullable',
                'string',
            ],
        ];

        if (! empty($allowedClasses)) {
            $rules['seeder'][] = Rule::in($allowedClasses);
        }

        $validated = $request->validate($rules);
        $selected = $validated['seeder'] ?? null;
        $selected = $selected === '' ? null : $selected;

        try {
            $output = $service->runSeeder($selected);

            return redirect()
                ->route('tech.seeders.index')
                ->with([
                    'seeder_output' => $output,
                    'seeder_status' => 'Seeder executed. Review the output below.',
                    'seeder_status_level' => 'success',
                    'seeder_selected' => $selected,
                ]);
        } catch (Throwable $exception) {
            return redirect()
                ->route('tech.seeders.index')
                ->with([
                    'seeder_status' => 'Seeder failed: ' . $exception->getMessage(),
                    'seeder_status_level' => 'danger',
                    'seeder_selected' => $selected,
                ]);
        }
    }
}
