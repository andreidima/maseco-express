<?php

namespace App\Http\Controllers;

use App\Models\Comanda;
use App\Models\ComandaFisier;
use App\Models\ComandaFisierEmail;
use App\Models\ComandaFisierIstoric;
use App\Support\BrowserViewableFile;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ComandaIncarcareDocumenteDeCatreTransportatorController extends Controller
{
    public function afisareDocumenteIncarcateDejaSiFormular(Request $request, $cheie_unica)
    {
        $request->session()->get('ComandaReturnUrl') ?? $request->session()->put('ComandaReturnUrl', url()->previous());

        $comanda = Comanda::with(
            'transportator:id,nume',
            'camion:id,numar_inmatriculare',
            'locuriOperareIncarcari',
            'locuriOperareDescarcari',
            'fisiereIncarcateDeTransportator',
            'emailuriPentruFisiereIncarcateDeTransportator'
        )->where('cheie_unica', $cheie_unica)->first();

        if (! $comanda) {
            abort(404, 'Page not found');
        }

        return view('comenziIncarcareDocumenteDeCatreTransportator.afisareDocumenteIncarcateDejaSiFormular', compact('comanda'));
    }

    public function salvareInformatiiDocumente(Request $request, $cheie_unica)
    {
        $comanda = Comanda::where('cheie_unica', $cheie_unica)
            ->with('fisiereIncarcateDeTransportator')
            ->first();

        if ($comanda) {
            $request->validate([
                'documente_transport_incarcate' => '',
            ]);

            $comanda->documente_transport_incarcate = $request->documente_transport_incarcate;
            $comanda->save();
            $comanda->syncFacturaTransportatorIncarcataStatus();
        }

        return redirect('comanda-documente-transportator/' . $cheie_unica)
            ->with('status', 'Datele au fost salvate cu succes!');
    }

    public function salvareDocumente(Request $request, $cheie_unica)
    {
        $comanda = Comanda::where('cheie_unica', $cheie_unica)
            ->with('fisiereIncarcateDeTransportator')
            ->first();

        if ($comanda) {
            $validated = $request->validate([
                'este_factura' => 'nullable|in:0,1',
            ]);

            $esteFactura = (int) ($validated['este_factura'] ?? 0);

            $request->validate(
                [
                    'fisiere' => 'required',
                    'fisiere.*' => [
                        'mimes:pdf',
                        'max:10240',
                        function (string $attribute, mixed $value, Closure $fail) use ($comanda) {
                            foreach ($comanda->fisiereIncarcateDeTransportator as $fisier) {
                                if ($fisier->nume === $value->getClientOriginalName()) {
                                    $fail('Nu puteti incarca de mai multe ori acelasi fisier. Exista deja un fisier incarcat cu denumirea ' . $value->getClientOriginalName());
                                }
                            }
                        },
                    ],
                ],
                [
                    'fisiere.required' => 'Nu ati adaugat niciun fisier.',
                    'fisiere.*.mimes' => 'Puteti adauga fisiere doar in format PDF.',
                    'fisiere.*.max' => 'Nu puteti adauga fisiere mai mari de 10 MB.',
                ]
            );

            if ($request->file('fisiere')) {
                foreach ($request->file('fisiere') as $fisierUpload) {
                    $numeFisier = $fisierUpload->getClientOriginalName();
                    $cale = 'comenzi/' . $comanda->id . '/fisiereIncarcateDeTransportator/';

                    if (Storage::exists($cale . '/' . $numeFisier)) {
                        $numeFisier .= uniqid('3');
                    }

                    try {
                        Storage::putFileAs($cale, $fisierUpload, $numeFisier);

                        $fisier = new ComandaFisier;
                        $fisier->comanda_id = $comanda->id;
                        $fisier->categorie = 1;
                        $fisier->cale = $cale;
                        $fisier->nume = $numeFisier;
                        $fisier->este_factura = $esteFactura;
                        $fisier->user_id = auth()->user()->id ?? null;
                        $fisier->save();

                        $fisierIstoric = new ComandaFisierIstoric;
                        $fisierIstoric->fill($fisier->makeHidden(['created_at', 'updated_at'])->attributesToArray());
                        $fisierIstoric->operare_user_id = auth()->user()->id ?? null;
                        $fisierIstoric->operare_descriere = 'Adaugare';
                        $fisierIstoric->save();
                    } catch (\Exception $e) {
                        report($e);

                        return back()->with('error', 'Fisierul nu a putut fi incarcat.');
                    }
                }
            }

            $comanda->syncFacturaTransportatorIncarcataStatus();
        }

        return $this->redirectToDocumenteTransportatorPage($cheie_unica)
            ->with('status', 'Fisierele au fost incarcate si pot fi vizualizate in tabelul de mai jos.');
    }

    public function fisierDeschide($cheie_unica, $numeFisier)
    {
        $comanda = Comanda::where('cheie_unica', $cheie_unica)
            ->with('fisiereIncarcateDeTransportator')
            ->first();

        if ($comanda) {
            foreach ($comanda->fisiereIncarcateDeTransportator as $fisier) {
                if ($fisier->nume === $numeFisier) {
                    $path = $fisier->cale . '/' . $fisier->nume;

                    if (! Storage::exists($path)) {
                        abort(404);
                    }

                    $mimeType = Storage::mimeType($path) ?? 'application/octet-stream';

                    if (! BrowserViewableFile::isViewable($fisier->nume, $mimeType)) {
                        return $this->fisierDownload($cheie_unica, $numeFisier);
                    }

                    return $this->streamStorageFile($path, $fisier->nume, $mimeType);
                }
            }
        }

        abort(404, 'Page not found');
    }

    public function fisierDownload($cheie_unica, $numeFisier)
    {
        $comanda = Comanda::where('cheie_unica', $cheie_unica)
            ->with('fisiereIncarcateDeTransportator')
            ->first();

        if ($comanda) {
            foreach ($comanda->fisiereIncarcateDeTransportator as $fisier) {
                if ($fisier->nume === $numeFisier) {
                    $path = $fisier->cale . '/' . $fisier->nume;

                    if (! Storage::exists($path)) {
                        abort(404);
                    }

                    $mimeType = Storage::mimeType($path) ?? 'application/octet-stream';

                    return $this->downloadStorageFile($path, $fisier->nume, $mimeType);
                }
            }
        }

        abort(404, 'Page not found');
    }

    protected function streamStorageFile(string $path, string $downloadName, string $mimeType): StreamedResponse
    {
        $stream = Storage::readStream($path);

        if ($stream === false) {
            abort(404);
        }

        return response()->stream(function () use ($stream) {
            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => BrowserViewableFile::contentDisposition('inline', $downloadName),
        ]);
    }

    protected function downloadStorageFile(string $path, string $downloadName, string $mimeType): StreamedResponse
    {
        $stream = Storage::readStream($path);

        if ($stream === false) {
            abort(404);
        }

        return response()->streamDownload(function () use ($stream) {
            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, $downloadName, [
            'Content-Type' => $mimeType,
        ]);
    }

    public function fisierSterge($cheie_unica, $numeFisier)
    {
        $comanda = Comanda::where('cheie_unica', $cheie_unica)
            ->with('fisiereIncarcateDeTransportator')
            ->first();

        if ($comanda) {
            foreach ($comanda->fisiereIncarcateDeTransportator as $fisier) {
                if (($fisier->nume === $numeFisier) && ($fisier->validat !== 1)) {
                    $wasInvoice = (int) $fisier->este_factura === 1;

                    Storage::delete($fisier->cale . '/' . $fisier->nume);
                    $fisier->delete();

                    $fisierIstoric = new ComandaFisierIstoric;
                    $fisierIstoric->fill($fisier->makeHidden(['created_at', 'updated_at'])->attributesToArray());
                    $fisierIstoric->operare_user_id = auth()->user()->id ?? null;
                    $fisierIstoric->operare_descriere = 'Stergere';
                    $fisierIstoric->save();

                    if (empty($files = Storage::allFiles($fisier->cale))) {
                        Storage::deleteDirectory($fisier->cale);

                        if (empty($files = Storage::allFiles(dirname($fisier->cale)))) {
                            Storage::deleteDirectory(dirname($fisier->cale));
                        }
                    }

                    if ($wasInvoice) {
                        $comanda->syncFacturaTransportatorIncarcataStatus();
                    }

                    return back()->with('status', '"' . $numeFisier . '" a fost sters cu succes!');
                }
            }
        }

        return back()->with('error', '"' . $numeFisier . '" nu a putut fi sters!');
    }

    public function validareInvalidareDocumente($cheie_unica, $numeFisier)
    {
        $comanda = Comanda::where('cheie_unica', $cheie_unica)
            ->with('fisiereIncarcateDeTransportator')
            ->first();

        if ($comanda) {
            foreach ($comanda->fisiereIncarcateDeTransportator as $fisier) {
                if ($fisier->nume === $numeFisier) {
                    if ($fisier->validat === 1) {
                        $fisier->update(['validat' => 0]);
                    } else {
                        $fisier->update(['validat' => 1]);
                    }

                    $fisierIstoric = new ComandaFisierIstoric;
                    $fisierIstoric->fill($fisier->makeHidden(['created_at', 'updated_at'])->attributesToArray());
                    $fisierIstoric->operare_user_id = auth()->user()->id ?? null;
                    $fisierIstoric->operare_descriere = 'Validare / Invalidare';
                    $fisierIstoric->save();

                    return back()->with('status', '"' . $numeFisier . '" a fost ' . ($fisier->validat == 1 ? 'validat' : 'invalidat') . ' cu succes!');
                }
            }
        }

        return back()->with('error', 'Nu puteti accesa aceasta pagina.');
    }

    public function blocareDeblocareIncarcareDocumente($cheie_unica)
    {
        $comanda = Comanda::where('cheie_unica', $cheie_unica)->first();

        if ($comanda) {
            if ($comanda->transportator_blocare_incarcare_documente === 1) {
                $comanda->update(['transportator_blocare_incarcare_documente' => 0]);

                return back()->with('status', 'Accesul la incarcarea de documente este acum permis!');
            }

            $comanda->update(['transportator_blocare_incarcare_documente' => 1]);

            return back()->with('status', 'Accesul la incarcarea de documente este acum blocat!');
        }

        return back()->with('error', 'Nu puteti accesa aceasta pagina.');
    }

    public function trimitereEmailTransportatorCatreMasecoDocumenteIncarcate($cheie_unica, $categorieEmail)
    {
        $comanda = Comanda::where('cheie_unica', $cheie_unica)->first();

        if (! $comanda) {
            abort(404, 'Page not found');
        }

        Mail::to(['pod@masecoexpres.net', $comanda->transportator->email])
            ->send(new \App\Mail\ComandaTransportatorDocumente($comanda, 'transportatorCatreMaseco', $categorieEmail));

        $emailTrimis = new ComandaFisierEmail;
        $emailTrimis->comanda_id = $comanda->id;
        $emailTrimis->tip = $categorieEmail === 'facturaTransport' ? 4 : 1;
        $emailTrimis->email = 'pod@masecoexpres.net';
        $emailTrimis->save();

        return redirect('comanda-incarcare-documente-de-catre-transportator/' . $cheie_unica . '/mesaj-succes-trimitere-notificare')
            ->with('status', 'Multumim, notificarea catre Maseco a fost transmisa cu succes! O copie a notificarii a fost trimisa si catre adresa ta de email pentru confirmare.');
    }

    public function trimitereEmailTransportatorCatreMasecoDocumenteIncarcateMesajSucces($cheie_unica)
    {
        $comanda = Comanda::where('cheie_unica', $cheie_unica)->first();

        if (! $comanda) {
            abort(404, 'Page not found');
        }

        return view('comenziIncarcareDocumenteDeCatreTransportator.mesajSuccesTrimitereNotificare', compact('comanda'));
    }

    public function trimitereEmailCatreTransportatorPrivindDocumenteIncarcate(Request $request, $cheie_unica)
    {
        $comanda = Comanda::where('cheie_unica', $cheie_unica)->first();

        if (! $comanda) {
            abort(404, 'Page not found');
        }

        if (! isset($comanda->transportator->email)) {
            return back()->with('error', 'Clientul nu are adaugat un email valid! Mergi la clienti si verifica datele clientului.');
        }

        if ($request->action === 'emailGoodDocuments') {
            $tipEmail = 'MasecoCatreTransportatorGoodDocuments';
            $mesaj = null;
        } elseif ($request->action === 'emailBadDocuments') {
            $request->validate(
                ['mesaj' => 'required|max:2000'],
                ['mesaj.required' => 'Este obligatoriu sa adaugi un motiv ca sa poti trimite mesajul']
            );
            $tipEmail = 'MasecoCatreTransportatorBadDocuments';
            $mesaj = $request->mesaj;
        } else {
            abort(400, 'Actiune invalida.');
        }

        $categorieEmail = 'documenteTransport';

        Mail::to($comanda->transportator->email)
            ->send(new \App\Mail\ComandaTransportatorDocumente($comanda, $tipEmail, $categorieEmail, $mesaj));

        $emailTrimis = new ComandaFisierEmail;
        $emailTrimis->comanda_id = $comanda->id;
        $emailTrimis->tip = $tipEmail === 'MasecoCatreTransportatorGoodDocuments' ? 2 : 3;
        $emailTrimis->email = $comanda->transportator->email;
        $emailTrimis->mesaj = $mesaj;
        $emailTrimis->save();

        return back()->with('status', 'Mesajul catre transportator a fost trimis cu succes!');
    }

    protected function redirectToDocumenteTransportatorPage(string $cheieUnica)
    {
        if (auth()->check()) {
            return redirect('comanda-documente-transportator/' . $cheieUnica);
        }

        return redirect('comanda-incarcare-documente-de-catre-transportator/' . $cheieUnica);
    }
}
