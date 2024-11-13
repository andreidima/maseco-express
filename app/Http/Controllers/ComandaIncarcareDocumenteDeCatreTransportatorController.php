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
use Closure;

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
                    'fisiere' => 'required',
                    'fisiere.*' => ['mimes:pdf', 'max:10240',
                        function (string $attribute, mixed $value, Closure $fail) use ($comanda) {
                            foreach ($comanda->fisiere as $fisier) {
                                if ($fisier->nume === $value->getClientOriginalName()){
                                    $fail("Nu puteți încărca de mai multe ori același fișier. Există deja un fișier încărcat cu denumirea " . $value->getClientOriginalName());
                                }
                            }
                        },
                    ]
                ],
                [
                    'fisiere.required' => 'Nu ați adăugat nici un fișier.',
                    'fisiere.*.mimes' => 'Puteți adăuga fișiere doar în format PDF.',
                    'fisiere.*.max' => 'Nu puteți adăuga fișiere mai mari de 10Mb.'
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

                // It's not really needed in this case because putting same name files is allready blocked in the validation
                if (Storage::exists($cale . '/' . $numeFisier)){
                    $numeFisier .= uniqid(3);
                }

                try {
                    Storage::putFileAs($cale, $fisier, $numeFisier);
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

        return redirect('comanda-incarcare-documente-de-catre-transportator/' .$cheie_unica)->success('status', 'Fișierele au fost încărcate, și pot fi vizualizate în tabelul de mai jos!');;
    }

    public function fisierDeschide($cheie_unica, $numeFisier)
    {
        //This method will look for the file and get it from drive
        if ($comanda = Comanda::where('cheie_unica', $cheie_unica)->with('fisiereIncarcateDeTransportator')->first()) {
            foreach ($comanda->fisiereIncarcateDeTransportator as $fisier) {
                if ($fisier->nume === $numeFisier) {
                    try {
                        $file = Storage::get($fisier->cale . '/' . $fisier->nume);
                        $type = Storage::mimeType($fisier->cale . '/' . $fisier->nume);
                        $response = Response::make($file, 200);
                        $response->header("Content-Type", $type);
                        return $response;
                    } catch (FileNotFoundException $exception) {
                        abort(404);
                    }
                }
            }
        }
        abort(404, 'Page not found');
    }

    public function fisierSterge($cheie_unica, $numeFisier)
    {
        if ($comanda = Comanda::where('cheie_unica', $cheie_unica)->with('fisiereIncarcateDeTransportator')->first()) {
            foreach ($comanda->fisiereIncarcateDeTransportator as $fisier) {
                if (($fisier->nume === $numeFisier) && ($fisier->validat !== 1)) {
                    Storage::delete($fisier->cale . '/' . $fisier->nume);
                    $fisier->delete();
                    return back()->with('status', '„' . $numeFisier . '" a fost șters cu succes!');
                }
            }
        }
        return back()->with('error', '„' . $numeFisier . '" nu a putut fi șters!');
    }

    public function validareInvalidareDocumente($cheie_unica, $numeFisier)
    {
        if ($comanda = Comanda::where('cheie_unica', $cheie_unica)->with('fisiereIncarcateDeTransportator')->first()) {
            foreach ($comanda->fisiereIncarcateDeTransportator as $fisier) {
                if ($fisier->nume === $numeFisier) {
                    if ($fisier->validat === 1){
                        $fisier->update(['validat' => 0]);
                        return back()->with('status', '„' . $numeFisier . '" a fost invalidat cu succes!');
                    }else{
                        $fisier->update(['validat' => 1]);
                        return back()->with('status', '„' . $numeFisier . '" a fost validat cu succes!');
                    }
                }
            }
        }
        return back()->with('error', 'Nu puteți accesa această pagină');
    }

    public function blocareDeblocareIncarcareDocumente($cheie_unica)
    {
        if ($comanda = Comanda::where('cheie_unica', $cheie_unica)->first()) {
            if ($comanda->transportator_blocare_incarcare_documente === 1){
                $comanda->update(['transportator_blocare_incarcare_documente' => 0]);
                return back()->with('status', 'Accesul la încărcarea de documente este acum permis!');
            }else{
                $comanda->update(['transportator_blocare_incarcare_documente' => 1]);
                return back()->with('status', 'Accesul la încărcarea de documente este acum blocat!');
            }
        }
        return back()->with('error', 'Nu puteți accesa această pagină');
    }
}
