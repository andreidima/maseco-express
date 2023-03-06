<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Comanda;
use App\Models\ComandaStatus;
use Carbon\Carbon;

class StatusComandaActualizatDeTransportatorController extends Controller
{
    public function cerereStatusComanda(Request $request, $recipient, $cheie_unica)
    {
        // se verifica pe langa cheia unica, si daca comanda mai este valabila, mai este in tranzit
        $comanda = Comanda::where('cheie_unica', $cheie_unica)
            ->whereHas('locuriOperareIncarcari', function($query){
                $query->where('data_ora', '<=', Carbon::now()->todatetimestring());
            })
            ->whereHas('locuriOperareDescarcari', function($query){
                $query->where('data_ora', '>=', Carbon::now()->subDays(1)->todatetimestring()); // se mai adauga o zi la dispozitie sa completeze statusul
            })
            ->first();

        return view('comenziStatusuri.actualizateDeTransportator.cerereStatusComanda', compact('comanda', 'recipient'));
    }

    public function salvareStatusComanda(Request $request, $recipient, $cheie_unica)
    {
        $validated = $request->validate([
            'raspuns' => 'required|max:2000',
        ]);

        // se verifica pe langa cheia unica, si daca comanda mai este valabila, mai este in tranzit
        $comanda = Comanda::where('cheie_unica', $cheie_unica)
            ->whereHas('locuriOperareIncarcari', function($query){
                $query->where('data_ora', '<=', Carbon::now()->todatetimestring());
            })
            ->whereHas('locuriOperareDescarcari', function($query){
                $query->where('data_ora', '>=', Carbon::now()->subDays(1)->todatetimestring()); // se mai adauga o zi la dispozitie sa completeze statusul
            })
            ->first();

        if (isset($comanda)){
            $statusComanda = new ComandaStatus;
            $statusComanda->comanda_id = $comanda->id;
            $statusComanda->status = $request->raspuns;
            $statusComanda->recipient = $recipient;
            $statusComanda->save();
        }

        return redirect('afisare-status-comanda/' . $recipient . '/' .$cheie_unica);
    }

    public function afisareStatusComanda(Request $request, $recipient, $cheie_unica)
    {
        // se verifica pe langa cheia unica, si daca comanda mai este valabila, mai este in tranzit
        $comanda = Comanda::
            // with('statusuri')
            with(['statusuri' => function($query){
                $query->latest();
            }])
            ->where('cheie_unica', $cheie_unica)
            ->whereHas('locuriOperareIncarcari', function($query){
                $query->where('data_ora', '<=', Carbon::now()->todatetimestring());
            })
            ->whereHas('locuriOperareDescarcari', function($query){
                $query->where('data_ora', '>=', Carbon::now()->subDays(1)->todatetimestring()); // se mai adauga o zi la dispozitie sa completeze statusul
            })
            ->first();

        return view('comenziStatusuri.actualizateDeTransportator.afisareStatusComanda', compact('comanda', 'recipient'));
    }
}
