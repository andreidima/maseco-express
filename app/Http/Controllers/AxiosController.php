<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\LocOperare;
use App\Models\ComandaStatus;
use Carbon\Carbon;

class AxiosController extends Controller
{

    /**
     * Returnarea oraselor de sosire
     */
    public function locuriOperare(Request $request)
    {
        $raspuns = '';
        switch ($_GET['request']) {
            case 'locuriOperare':
                $raspuns = LocOperare::select('id', 'nume', 'oras', 'tara_id', 'adresa')
                    ->with('tara')
                    ->where('nume', 'like', '%' . $request->nume . '%')
                    ->orderBy('nume')
                    ->take(100)
                    ->get();
                break;
            default:
                break;
        }
        return response()->json([
            'raspuns' => $raspuns,
        ]);
    }

    public function statusuri(Request $request)
    {
        $comada_id = $request->comanda_id;
        $statusuri = ComandaStatus::
            with('comanda:id,transportator_contract,transportator_transportator_id,client_client_id', 'comanda.transportator:id,nume,persoana_contact,telefon', 'comanda.client:id,nume')
            ->whereHas('comanda', function ($query) use ($comada_id) {
                $query->where('id', $comada_id);
            })
            // ->with('comanda.transportator:nume')
            // ->whereDate('created_at', '>',  Carbon::today()->subDays(5)) // statusurile din ultimele 3 zile
            ->latest()
            ->get();

        foreach ($statusuri as $status){
            $status->data = Carbon::parse($status->created_at)->isoFormat('DD.MM.YYYY');
            $status->ora = Carbon::parse($status->created_at)->isoFormat('HH:mm');
        }

        return response()->json([
            'raspuns' => $statusuri,
        ]);
    }
}
