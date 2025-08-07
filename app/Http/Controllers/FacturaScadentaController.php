<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Factura;
use App\Models\Comanda;

use Carbon\Carbon;

class FacturaScadentaController extends Controller
{
    public function index(Request $request)
    {
        $request->session()->forget('facturaScadentaReturnUrl');

        $searchTransportator = $request->searchTransportator;
        $searchTransportatorContract = $request->searchTransportatorContract;

        $query = Comanda::select('id', 'data_creare', 'transportator_transportator_id', 'transportator_contract', 'factura_transportator', 'data_factura_transportator', 'data_scadenta_plata_transportator')
            ->with('transportator:id,nume')
            ->when($searchTransportator, function ($query, $searchTransportator) {
                return $query->whereHas('transportator', function ($query) use ($searchTransportator) {
                    $query->where('nume', 'like', "%{$searchTransportator}%");
                });
            })
            ->when($searchTransportatorContract, function ($query, $searchTransportatorContract) {
                return $query->where('transportator_contract', 'like', "%{$searchTransportatorContract}%");
            })
            ->whereDate('data_creare', '>', Carbon::today()->subDays(90))
            ->whereNull('data_plata_transportator')
            ->whereNotNull('data_scadenta_plata_transportator')
            ->orderBy('data_scadenta_plata_transportator');

        $comenzi = $query->simplePaginate(25);

        return view('facturi_scadente.index', compact('comenzi', 'searchTransportator', 'searchTransportatorContract'));
    }
}
