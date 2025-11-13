<?php

namespace App\Http\Controllers;

use App\Models\Valabilitate;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SoferDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $today = Carbon::today();
        $driver = $request->user();

        $activeValabilitate = null;

        if ($driver) {
            $activeValabilitate = Valabilitate::query()
                ->select(['id', 'numar_auto', 'denumire', 'data_inceput', 'data_sfarsit'])
                ->where('sofer_id', $driver->id)
                ->whereDate('data_inceput', '<=', $today)
                ->where(function ($query) use ($today) {
                    $query
                        ->whereNull('data_sfarsit')
                        ->orWhereDate('data_sfarsit', '>=', $today);
                })
                ->withCount('curse')
                ->orderByDesc('data_inceput')
                ->first();
        }

        return view('sofer.dashboard', [
            'activeValabilitate' => $activeValabilitate,
        ]);
    }
}
