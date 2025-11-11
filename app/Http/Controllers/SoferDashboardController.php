<?php

namespace App\Http\Controllers;

use App\Models\MasinaValabilitati;
use Illuminate\Support\Carbon;

class SoferDashboardController extends Controller
{
    public function __invoke()
    {
        $today = Carbon::today();

        $valabilitati = MasinaValabilitati::query()
            ->select([
                'id',
                'nr_auto',
                'nume_sofer',
                'detalii_sofer',
                'divizie',
                'valabilitate_1',
                'valabilitate_1_inceput',
                'valabilitate_1_sfarsit',
                'observatii_1',
                'valabilitate_2',
                'valabilitate_2_inceput',
                'valabilitate_2_sfarsit',
                'observatii_2',
            ])
            ->where(function ($query) use ($today) {
                $query->where(function ($subQuery) use ($today) {
                    $subQuery
                        ->whereNotNull('valabilitate_1_sfarsit')
                        ->whereDate('valabilitate_1_sfarsit', '>=', $today);
                })
                ->orWhere(function ($subQuery) use ($today) {
                    $subQuery
                        ->whereNull('valabilitate_1_sfarsit')
                        ->whereNotNull('valabilitate_1_inceput')
                        ->whereDate('valabilitate_1_inceput', '<=', $today);
                })
                ->orWhere(function ($subQuery) use ($today) {
                    $subQuery
                        ->whereNotNull('valabilitate_2_sfarsit')
                        ->whereDate('valabilitate_2_sfarsit', '>=', $today);
                })
                ->orWhere(function ($subQuery) use ($today) {
                    $subQuery
                        ->whereNull('valabilitate_2_sfarsit')
                        ->whereNotNull('valabilitate_2_inceput')
                        ->whereDate('valabilitate_2_inceput', '<=', $today);
                });
            })
            ->orderByRaw('COALESCE(valabilitate_1_sfarsit, valabilitate_2_sfarsit) IS NULL ASC')
            ->orderByRaw('COALESCE(valabilitate_1_sfarsit, valabilitate_2_sfarsit) ASC')
            ->orderBy('nr_auto')
            ->get();

        return view('sofer.dashboard', [
            'valabilitati' => $valabilitati,
        ]);
    }
}
