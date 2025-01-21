<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Comanda;
use App\Models\ComandaFisier;
use App\Models\ComandaFisierIstoric;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Closure;
use App\Models\ComandaFisierEmail;

class ComandaIncarcareDocumenteDeCatreTransportatorController extends Controller
{
    public function afisareDocumenteIncarcateDejaSiFormular(Request $request, $cheie_unica)
    {
        $request->session()->get('ComandaReturnUrl') ?? $request->session()->put('ComandaReturnUrl', url()->previous());

        // se verifica pe langa cheia unica
        if($comanda = Comanda::with('transportator:id,nume', 'camion:id,numar_inmatriculare', 'locuriOperareIncarcari', 'locuriOperareDescarcari', 'fisiereIncarcateDeTransportator', 'emailuriPentruFisiereIncarcateDeTransportator')->where('cheie_unica', $cheie_unica)->first()){
            return view('comenziIncarcareDocumenteDeCatreTransportator.afisareDocumenteIncarcateDejaSiFormular', compact('comanda'));
        } else {
            abort(404, 'Page not found');
        }
    }

    public function salvareInformatiiDocumente(Request $request, $cheie_unica)
    {
        $comanda = Comanda::where('cheie_unica', $cheie_unica)->with('fisiereIncarcateDeTransportator')->first();

        if (isset($comanda)){
            $request->validate(
                [
                    'documente_transport_incarcate' => '',
                    'factura_incarcata' => '',
                ]
            );
            $comanda->documente_transport_incarcate = $request->documente_transport_incarcate;
            $comanda->factura_transportator_incarcata = $request->factura_transportator_incarcata;
            $comanda->save();
        }

        return redirect('comanda-documente-transportator/' .$cheie_unica)->with('status', 'Datele au fost salvate cu succes!');
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
                            foreach ($comanda->fisiereIncarcateDeTransportator as $fisier) {
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

            if ($request->file('fisiere')) {
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
                        $fisier->user_id = auth()->user()->id ?? null;
                        $fisier->save();

                        // Istoric save
                        $fisier_istoric = new ComandaFisierIstoric;
                        $fisier_istoric->fill($fisier->makeHidden(['created_at', 'updated_at'])->attributesToArray());
                        $fisier_istoric->operare_user_id = auth()->user()->id ?? null;
                        $fisier_istoric->operare_descriere = 'Adaugare';
                        $fisier_istoric->save();
                    } catch (Exception $e) {
                        return back()->with('error', 'Fișierul nu a putut fi încărcat.');
                    }
                }
            }
        }

        if (auth()->user()){
            return redirect('comanda-documente-transportator/' .$cheie_unica)->with('status', 'Fișierele au fost încărcate, și pot fi vizualizate în tabelul de mai jos!');
        } else{
            return redirect('comanda-incarcare-documente-de-catre-transportator/' .$cheie_unica)->with('status', 'Fișierele au fost încărcate, și pot fi vizualizate în tabelul de mai jos!');
        }
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

                    // Istoric save
                    $fisier_istoric = new ComandaFisierIstoric;
                    $fisier_istoric->fill($fisier->makeHidden(['created_at', 'updated_at'])->attributesToArray());
                    $fisier_istoric->operare_user_id = auth()->user()->id ?? null;
                    $fisier_istoric->operare_descriere = 'Stergere';
                    $fisier_istoric->save();

                    // Delete the directories too if they are empty
                    if (empty($files = Storage::allFiles($fisier->cale))){ // fisiereIncarcateDeTransportator directory
                        Storage::deleteDirectory($fisier->cale);
                        if (empty($files = Storage::allFiles(dirname($fisier->cale)))){ // If the parent directory (comand directory) is empty too, it will be deleted aswell
                            Storage::deleteDirectory(dirname($fisier->cale));
                        }
                    }

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
                        // return back()->with('status', '„' . $numeFisier . '" a fost invalidat cu succes!');
                    }else{
                        $fisier->update(['validat' => 1]);
                        // return back()->with('status', '„' . $numeFisier . '" a fost validat cu succes!');
                    }

                    // Istoric save
                    $fisier_istoric = new ComandaFisierIstoric;
                    $fisier_istoric->fill($fisier->makeHidden(['created_at', 'updated_at'])->attributesToArray());
                    $fisier_istoric->operare_user_id = auth()->user()->id ?? null;
                    $fisier_istoric->operare_descriere = 'Validare / Invalidare';
                    $fisier_istoric->save();

                    return back()->with('status', '„' . $numeFisier . '" a fost ' . (($fisier->validat == 1) ? 'validat' : 'invalidat') . ' cu succes!');
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

    public function trimitereEmailTransportatorCatreMasecoDocumenteIncarcate($cheie_unica, $categorieEmail)
    {
        if ($comanda = Comanda::where('cheie_unica', $cheie_unica)->first()) {
            Mail::to(['pod@masecoexpres.net', $comanda->transportator->email])->send(new \App\Mail\ComandaTransportatorDocumente($comanda, 'transportatorCatreMaseco', $categorieEmail));

            $emailTrimis = new ComandaFisierEmail;
            $emailTrimis->comanda_id = $comanda->id;
            $emailTrimis->tip = 1;
            if ($categorieEmail == 'documenteTransport'){
                $emailTrimis->tip = 1;
            } else if ($categorieEmail == 'facturaTransport'){
                $emailTrimis->tip = 4;
            }
            $emailTrimis->email = 'pod@masecoexpres.net';
            $emailTrimis->save();

            return redirect('comanda-incarcare-documente-de-catre-transportator/' . $cheie_unica . '/mesaj-succes-trimitere-notificare')->with('status', 'Mulțumim, notificarea către Maseco a fost transmisă cu success! O copie a notificării a fost trimisă și către adresa ta de email pentru a o avea ca și confirmare.');
        }
        abort(404, 'Page not found');
    }

    public function trimitereEmailTransportatorCatreMasecoDocumenteIncarcateMesajSucces($cheie_unica)
    {
        if ($comanda = Comanda::where('cheie_unica', $cheie_unica)->first()) {
            return view('comenziIncarcareDocumenteDeCatreTransportator.mesajSuccesTrimitereNotificare', compact('comanda'));
        }
        abort(404, 'Page not found');
    }

    public function trimitereEmailCatreTransportatorPrivindDocumenteIncarcate(Request $request, $cheie_unica)
    {
        if ($comanda = Comanda::where('cheie_unica', $cheie_unica)->first()) {
            if (!isset($comanda->transportator->email)){
                return back()->with('error', 'Clientul nu are adăugat un email valid! Mergi la clienți și verifică datele clientului.');
            }

            if ($request->action == "emailGoodDocuments"){
                $tipEmail = 'MasecoCatreTransportatorGoodDocuments';
                $mesaj = null;
            } elseif ($request->action == "emailBadDocuments"){
                $request->validate(['mesaj' => 'required|max:2000'],['mesaj.required' => 'Este obligatoriu să adaugi un motiv ca să poți trimite mesajul']);
                $tipEmail = 'MasecoCatreTransportatorBadDocuments';
                $mesaj = $request->mesaj;
            }

            Mail::to($comanda->transportator->email)->send(new \App\Mail\ComandaTransportatorDocumente($comanda, $tipEmail, $mesaj));

            $emailTrimis = new ComandaFisierEmail;
            $emailTrimis->comanda_id = $comanda->id;
            $emailTrimis->tip = ($tipEmail == 'MasecoCatreTransportatorGoodDocuments' ? 2 : 3);
            $emailTrimis->email = $comanda->transportator->email;
            $emailTrimis->mesaj = $mesaj;
            $emailTrimis->save();

            return back()->with('status', 'Mesajul către transportator a fost trimis cu succes!');
        }
        abort(404, 'Page not found');
    }
}
