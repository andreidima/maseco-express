<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Comanda;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RaportController extends Controller
{
    public function incasariUtilizatori(Request $request){
        $searchInterval = $request->searchInterval ?? (Carbon::today()->startOfMonth()->format('Y-m-d') . "," . Carbon::today()->endOfMonth()->format('Y-m-d'));

        $useri = User::with(['comenzi' => function ($query) use ($searchInterval) {
                return $query->whereBetween('data_creare', [strtok($searchInterval, ','), strtok( '' )]);
            }])
            ->whereHas('comenzi', function ($query) use($searchInterval) {
                return $query->whereBetween('data_creare', [strtok($searchInterval, ','), strtok( '' )]);
            })
            ->get();

        $monede = \App\Models\Moneda::select('id', 'nume')->get();

        return view('rapoarte.incasariUtilizatori', compact('useri', 'monede', 'searchInterval'));
    }

    public function documenteTransportatori(Request $request){
        $comenzi = Comanda::select('id', 'transportator_contract', 'cheie_unica')
            ->with('ultimulEmailPentruFisiereIncarcateDeTransportator:id,comanda_id,tip,created_at')
            ->whereHas('ultimulEmailPentruFisiereIncarcateDeTransportator', function ($query) {
                $query->where(function ($query) {
                    $query->where('tip', 2)
                    ->where('created_at', '>', Carbon::now()->subDay());
                })
                ->orWhere(function ($query) {
                    $query->where('tip', '<>', 2)
                    ->where('created_at', '<', Carbon::now()->subDay());
                });
            })
            ->latest()
            ->get();

        $comenziFaraFisiere = Comanda::select('id', 'data_creare', 'transportator_contract', 'cheie_unica')
            ->with(['locuriOperareDescarcari', 'fisiere:id'])
            ->whereDoesntHave('locuriOperareDescarcari', function ($query) {
                $query->where('data_ora', '>', Carbon::now()->subDay());
            })
            ->whereDoesntHave('fisiere')
            ->where('id', '>' , 4419)
            // ->where('id', '>' , 4319)
            ->latest()
            ->get();

        return view('rapoarte.documenteTransportatori', compact('comenzi', 'comenziFaraFisiere'));
    }
}
