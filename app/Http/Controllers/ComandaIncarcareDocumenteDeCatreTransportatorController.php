<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Comanda;
use App\Models\ComandaFisier;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class ComandaIncarcareDocumenteDeCatreTransportatorController extends Controller
{
    public function afisareDocumenteIncarcateDejaSiFormular(Request $request, $cheie_unica)
    {
        // se verifica pe langa cheia unica
        $comanda = Comanda::with('transportator:id,nume', 'camion:id,numar_inmatriculare', 'locuriOperareIncarcari', 'locuriOperareDescarcari', 'fisiereIncarcateDeTransportator')->where('cheie_unica', $cheie_unica)->first();

        return view('comenziIncarcareDocumenteDeCatreTransportator.afisareDocumenteIncarcateDejaSiFormular', compact('comanda'));
    }

    public function salvareDocumente(Request $request, $cheie_unica)
    {
        $comanda = Comanda::where('cheie_unica', $cheie_unica)->with('fisiereIncarcateDeTransportator')->first();

        if (isset($comanda)){
            $request->validate(
                [
                    'fisiere' => 'required|max:30000',
                    'fisiere.*' => 'max:30000'
                ],
                [
                    'fisiere.required' => 'Nu ați adăugat nici un fișier.'
                ]
            );

            // foreach ($request->file('fisiere') as $fisier) {
            //     $numeFisier = $fisier->getClientOriginalName();
            //     if (Storage::disk('filemanager')->exists($request->cale . '/' . $numeFisier)){
            //         return back()->with('error', 'Există deja un fișier cu numele „' . $numeFisier . '”. Redenumește fișierul și încearcă din nou.');
            //     }
            // }
            // foreach ($request->file('fisiere') as $fisier) {
            //     $numeFisier = $fisier->getClientOriginalName();
            //     if (! Storage::disk('filemanager')->putFileAs($request->cale, $fisier, $numeFisier)){
            //         return back()->with('error', 'Fișierele nu au putut fi încărcate.');
            //     }
            // }

            foreach ($request->file('fisiere') as $fisier) {
                $numeFisier = $fisier->getClientOriginalName();
                $cale = 'comenzi/' . $comanda->id . '/fisiereIncarcateDeTransportator/';

                if (Storage::exists($cale . '/' . $numeFisier)){
                    $numeFisier .= uniqid(3);
                }

                try {
                    // Storage::putFileAs($cale, $fisier, $numeFisier);
                    $fisier = new ComandaFisier;
                    $fisier->comanda_id = $comanda->id;
                    $fisier->categorie = 1; // uploaded by Transporter
                    $fisier->cale = $cale;
                    $fisier->nume = $numeFisier;
                    $fisier->save();
                } catch (Exception $e) {
                    return back()->with('error', 'Fișierul nu a putut fi încărcat.');
                }
            }
        }

        return redirect('comanda-incarcare-documente-de-catre-transportator/' .$cheie_unica);
    }
}
