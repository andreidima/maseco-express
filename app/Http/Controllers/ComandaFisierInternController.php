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

class ComandaFisierInternController extends Controller
{
    public function afisareFisiereIncarcateDejaSiFormular(Request $request, Comanda $comanda)
    {
        $request->session()->get('ComandaReturnUrl') ?? $request->session()->put('ComandaReturnUrl', url()->previous());

        return view('comenzi.fisiereInterne.afisareFisiereIncarcateDejaSiFormular', compact('comanda'));
    }

    public function salvareDocumente(Request $request, Comanda $comanda)
    {
        $request->session()->get('ComandaReturnUrl') ?? $request->session()->put('ComandaReturnUrl', url()->previous());

        $request->validate(
            [
                'fisiere' => 'required',
                'fisiere.*' => ['max:10240',
                    function (string $attribute, mixed $value, Closure $fail) use ($comanda) {
                        foreach ($comanda->fisiereInterne as $fisier) {
                            if ($fisier->nume === $value->getClientOriginalName()){
                                $fail("Nu puteți încărca de mai multe ori același fișier. Există deja un fișier încărcat cu denumirea " . $value->getClientOriginalName());
                            }
                        }
                    },
                ]
            ],
            [
                'fisiere.required' => 'Nu ați adăugat nici un fișier.',
                'fisiere.*.max' => 'Nu puteți adăuga fișiere mai mari de 10Mb.'
            ]
        );

        foreach ($request->file('fisiere') as $fisier) {
            $numeFisier = $fisier->getClientOriginalName();
            $cale = 'comenzi/' . $comanda->id . '/fisiereInterne/';

            // It's not really needed in this case because putting same name files is allready blocked in the validation
            if (Storage::exists($cale . '/' . $numeFisier)){
                $numeFisier .= uniqid(3);
            }

            try {
                Storage::putFileAs($cale, $fisier, $numeFisier);
                $fisier = new ComandaFisier;
                $fisier->comanda_id = $comanda->id;
                $fisier->categorie = 2; // fisiere Interne
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

        return redirect('comenzi/' . $comanda->id . '/fisiere-interne')->with('status', 'Fișierele au fost încărcate cu succes!');
    }

    public function fisierDeschide(Comanda $comanda, $numeFisier)
    {
        //This method will look for the file and get it from drive
        foreach ($comanda->fisiereInterne as $fisier) {
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

    public function fisierSterge(Comanda $comanda, $numeFisier)
    {
        foreach ($comanda->fisiereInterne as $fisier) {
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
                if (empty($files = Storage::allFiles($fisier->cale))){ // fisiereInterne directory
                    Storage::deleteDirectory($fisier->cale);
                    if (empty($files = Storage::allFiles(dirname($fisier->cale)))){ // If the parent directory (comand directory) is empty too, it will be deleted aswell
                        Storage::deleteDirectory(dirname($fisier->cale));
                    }
                }

                return back()->with('status', '„' . $numeFisier . '" a fost șters cu succes!');
            }
        }
        return back()->with('error', '„' . $numeFisier . '" nu a putut fi șters!');
    }
}
