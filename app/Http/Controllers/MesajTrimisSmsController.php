<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\MesajTrimisSms;

class MesajTrimisSmsController extends Controller
{
    public function index()
    {
        $search_transportator_contract = \Request::get('search_transportator_contract');
        $search_telefon = \Request::get('search_telefon');

        $mesaje_sms = MesajTrimisSms::with('comanda:id,transportator_contract')
            ->when($search_transportator_contract, function ($query, $search_transportator_contract) {
                return $query->whereHas('comanda', function ($query) use ($search_transportator_contract){
                    return $query->where('transportator_contract', 'like', '%'.$search_transportator_contract.'%');
                });
            })
            ->when($search_telefon, function ($query, $search_telefon) {
                return $query->where('telefon', 'like', '%' . $search_telefon . '%');
            })
            ->latest()
            ->simplePaginate(25);

        return view('mesajeTrimiseSms.index', compact('mesaje_sms', 'search_transportator_contract', 'search_telefon'));
    }
}
