<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Comanda;
use App\Models\ComandaStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class StatusComandaActualizatDeTransportatorController extends Controller
{
    public function cerereStatusComanda(Request $request, $modTransmitere, $cheie_unica)
    {
        // se verifica pe langa cheia unica, si daca comanda mai este valabila, mai este in tranzit
        $comanda = Comanda::where('cheie_unica', $cheie_unica)
            ->whereHas('locuriOperareIncarcari', function($query){
                $query->where('data_ora', '<=', Carbon::now()->addHours(2)->todatetimestring());
            })
            ->whereHas('locuriOperareDescarcari', function($query){
                $query->where('data_ora', '>=', Carbon::now()->subDays(1)->todatetimestring()); // se mai adauga o zi la dispozitie sa completeze statusul
            })
            ->first();

        return view('comenziStatusuri.actualizateDeTransportator.cerereStatusComanda', compact('comanda', 'modTransmitere'));
    }

    public function salvareStatusComanda(Request $request, $modTransmitere, $cheie_unica)
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
            $statusComanda->mod_transmitere = $modTransmitere;
            $statusComanda->save();

            // Se trimite email de notificare cu schimbarea statusului catre Maseco
            $emailTrimis = new \App\Models\MesajTrimisEmail;
            if (isset($comanda->transportator->email) && ($comanda->transportator->email !== 'adima@validsoftware.ro') && ($comanda->transportator->email !== 'andrei.dima@usm.ro')){
                // echo 'nu este Andrei';
                Mail::to('info@masecoexpres.net')->send(new \App\Mail\InformareLaActualizareStatusComanda($comanda));
                $emailTrimis->email = 'info@masecoexpres.net';
            } else {
                // echo 'Este Andrei';
                Mail::to('andrei.dima@usm.ro')->send(new \App\Mail\InformareLaActualizareStatusComanda($comanda));
                $emailTrimis->email = 'andrei.dima@usm.ro';
            }
            $emailTrimis->comanda_id = $comanda->id;
            $emailTrimis->firma_id = $comanda->transportator->id ?? '';
            $emailTrimis->categorie = 4;
            $emailTrimis->save();
        }

        return redirect('afisare-status-comanda/' . $modTransmitere . '/' .$cheie_unica);
    }

    public function afisareStatusComanda(Request $request, $modTransmitere, $cheie_unica)
    {
        // se verifica pe langa cheia unica, si daca comanda mai este valabila, mai este in tranzit
        $comanda = Comanda::
            // with('statusuri')
            with(['statusuri' => function($query) use ($modTransmitere){
                $query->where('mod_transmitere', $modTransmitere)->latest();
            }])
            ->where('cheie_unica', $cheie_unica)
            ->whereHas('locuriOperareIncarcari', function($query){
                $query->where('data_ora', '<=', Carbon::now()->todatetimestring());
            })
            ->whereHas('locuriOperareDescarcari', function($query){
                $query->where('data_ora', '>=', Carbon::now()->subDays(1)->todatetimestring()); // se mai adauga o zi la dispozitie sa completeze statusul
            })
            ->first();

        return view('comenziStatusuri.actualizateDeTransportator.afisareStatusComanda', compact('comanda', 'modTransmitere'));
    }
}
