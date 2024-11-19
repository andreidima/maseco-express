<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\StatiePeco;

class StatiePecoController extends Controller
{
    public function index(Request $request)
    {
        $searchNumarStatie = $request->searchNumarStatie;

        $statiiPeco = StatiePeco::
            when($searchNumarStatie, function ($query, $searchNumarStatie) {
                $query->where('numar_statie', 'like', '%' . $searchNumarStatie . '%');
            }, function ($query) {
                $query->where('id', 0); // return nothing if there is no search by station number
            })
            ->simplePaginate(25);

        return view('statiiPeco.index', compact('statiiPeco', 'searchNumarStatie'));
    }
}
