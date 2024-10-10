<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Comanda;
use App\Models\User;
use Carbon\Carbon;

class RaportController extends Controller
{
    public function index(Request $request){
        // $searchUser = $request->searchUser;
        $searchInterval = $request->searchInterval ?? (Carbon::today()->startOfMonth()->format('Y-m-d') . "," . Carbon::today()->endOfMonth()->format('Y-m-d'));

        $useri = User::with(['comenzi' => function ($query) use ($searchInterval) {
                return $query->whereBetween('data_creare', [strtok($searchInterval, ','), strtok( '' )]);
            }])
            ->whereHas('comenzi', function ($query) use($searchInterval) {
                return $query->whereBetween('data_creare', [strtok($searchInterval, ','), strtok( '' )]);
            })
            ->get();

        $monede = \App\Models\Moneda::select('id', 'nume')->get();

        // dd($useri);

        // $statisticiUseriGrupatePerUser = Comanda::with('user:id,name')
        //     ->when($searchInterval, function ($query, $searchInterval) {
        //         return $query->whereBetween('data_creare', [strtok($searchInterval, ','), strtok( '' )]);
        //     })
            // ->when($searchUser, function ($query, $searchUser) {
            //     return $query->whereHas('user', function ($query) use ($searchUser) {
            //         $query->where('id', $searchUser);
            //     });
            // })
            // ->groupBy('user_id')
            // ->get();

        // $useri = User::select('id' , 'name')->where('name', '<>', 'Andrei Dima')->where('activ', 1)->orderBy('name')->get();
        // return view('rapoarte.index', compact('statisticiUseriGrupatePerUser', 'searchInterval'));
        return view('rapoarte.index', compact('useri', 'monede', 'searchInterval'));
    }
}
