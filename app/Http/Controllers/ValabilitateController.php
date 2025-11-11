<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Valabilitate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ValabilitateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $filters = [
            'status' => $request->input('status', 'toate'),
            'denumire' => trim((string) $request->input('denumire', '')),
            'sofer' => trim((string) $request->input('sofer', '')),
            'numar_auto' => trim((string) $request->input('numar_auto', '')),
            'inceput_de_la' => $request->input('inceput_de_la', ''),
            'inceput_pana_la' => $request->input('inceput_pana_la', ''),
            'sfarsit_de_la' => $request->input('sfarsit_de_la', ''),
            'sfarsit_pana_la' => $request->input('sfarsit_pana_la', ''),
            'fara_sfarsit' => $request->boolean('fara_sfarsit'),
        ];

        $query = Valabilitate::query()->with('sofer');

        if ($filters['denumire'] !== '') {
            $query->where('denumire', 'like', '%' . $filters['denumire'] . '%');
        }

        if ($filters['sofer'] !== '') {
            $query->whereHas('sofer', function ($query) use ($filters): void {
                $query->where('name', 'like', '%' . $filters['sofer'] . '%');
            });
        }

        if ($filters['numar_auto'] !== '') {
            $query->where('numar_auto', 'like', '%' . $filters['numar_auto'] . '%');
        }

        if ($filters['inceput_de_la'] !== '') {
            $query->whereDate('data_inceput', '>=', $filters['inceput_de_la']);
        }

        if ($filters['inceput_pana_la'] !== '') {
            $query->whereDate('data_inceput', '<=', $filters['inceput_pana_la']);
        }

        if ($filters['sfarsit_de_la'] !== '') {
            $query->whereDate('data_sfarsit', '>=', $filters['sfarsit_de_la']);
        }

        if ($filters['sfarsit_pana_la'] !== '') {
            $query->whereDate('data_sfarsit', '<=', $filters['sfarsit_pana_la']);
        }

        if ($filters['fara_sfarsit']) {
            $query->whereNull('data_sfarsit');
        }

        $today = now()->startOfDay();
        $statusFilter = $filters['status'];

        if ($statusFilter === 'active') {
            $query->where(function ($subQuery) use ($today): void {
                $subQuery
                    ->whereNull('data_sfarsit')
                    ->orWhereDate('data_sfarsit', '>=', $today);
            });
        } elseif ($statusFilter === 'expirate') {
            $query->whereNotNull('data_sfarsit')->whereDate('data_sfarsit', '<', $today);
        }

        $valabilitati = $query
            ->orderByDesc('data_inceput')
            ->orderBy('denumire')
            ->paginate(20)
            ->withQueryString();

        $statusCounts = [
            'active' => Valabilitate::query()
                ->where(function ($subQuery) use ($today): void {
                    $subQuery
                        ->whereNull('data_sfarsit')
                        ->orWhereDate('data_sfarsit', '>=', $today);
                })
                ->count(),
            'expirate' => Valabilitate::query()
                ->whereNotNull('data_sfarsit')
                ->whereDate('data_sfarsit', '<', $today)
                ->count(),
        ];

        $denumiri = Valabilitate::query()
            ->select('denumire')
            ->distinct()
            ->orderBy('denumire')
            ->pluck('denumire');

        $numereAuto = Valabilitate::query()
            ->select('numar_auto')
            ->distinct()
            ->orderBy('numar_auto')
            ->pluck('numar_auto');

        $soferi = User::query()
            ->select('name')
            ->whereHas('valabilitati')
            ->orderBy('name')
            ->pluck('name');

        return view('valabilitati.index', [
            'valabilitati' => $valabilitati,
            'filters' => $filters,
            'statusCounts' => $statusCounts,
            'denumiri' => $denumiri,
            'numereAuto' => $numereAuto,
            'soferi' => $soferi,
        ]);
    }
}
