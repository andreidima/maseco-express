<?php

namespace App\Http\Controllers\Masini;

use App\Http\Controllers\Controller;
use App\Models\Masini\Masina;
use App\Models\Masini\MasinaDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MasiniMementoController extends Controller
{
    public function index(Request $request): View
    {
        $masini = Masina::orderBy('numar_inmatriculare')->get();

        $masini->each(function (Masina $masina): void {
            $masina->syncDefaultDocuments();
            $masina->loadMissing(['memento', 'documente.fisiere']);
        });

        $gridDocumentTypes = MasinaDocument::gridDocumentTypes();
        $vignetteCountries = MasinaDocument::vignetteCountries();

        return view('masini-mementouri.index', compact(
            'masini',
            'gridDocumentTypes',
            'vignetteCountries'
        ));
    }

    public function create(): View
    {
        return view('masini-mementouri.create', [
            'defaultEmail' => 'masecoexpres@gmail.com',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'numar_inmatriculare' => ['required', 'string', 'max:50', 'unique:masini,numar_inmatriculare'],
            'descriere' => ['nullable', 'string', 'max:255'],
            'marca_masina' => ['nullable', 'string', 'max:255'],
            'serie_sasiu' => ['nullable', 'string', 'max:255'],
            'email_notificari' => ['nullable', 'email:rfc'],
            'observatii' => ['nullable', 'string'],
        ]);

        $masina = Masina::create(Arr::only($validated, [
            'numar_inmatriculare',
            'descriere',
            'marca_masina',
            'serie_sasiu',
        ]));

        $masina->memento?->update(Arr::only($validated, ['email_notificari', 'observatii']));

        $redirect = $request->input('redirect');

        if ($redirect === 'index') {
            return Redirect::route('masini-mementouri.index')->with('status', 'Mașina a fost adăugată cu succes.');
        }

        return Redirect::route('masini-mementouri.show', $masina)->with('status', 'Mașina a fost adăugată cu succes.');
    }

    public function show(Masina $masini_mementouri): View
    {
        $masini_mementouri->syncDefaultDocuments();
        $masini_mementouri->loadMissing(['memento', 'documente.fisiere']);

        return view('masini-mementouri.show', [
            'masina' => $masini_mementouri,
        ]);
    }

    public function edit(Masina $masini_mementouri): View
    {
        $masini_mementouri->syncDefaultDocuments();
        $masini_mementouri->loadMissing(['documente.fisiere']);

        $uploadDocumentLabels = MasinaDocument::uploadDocumentLabels();

        return view('masini-mementouri.edit', [
            'masina' => $masini_mementouri,
            'uploadDocumentLabels' => $uploadDocumentLabels,
        ]);
    }

    public function update(Request $request, Masina $masini_mementouri): RedirectResponse
    {
        $validated = $request->validate([
            'numar_inmatriculare' => ['required', 'string', 'max:50', Rule::unique('masini', 'numar_inmatriculare')->ignore($masini_mementouri->id)],
            'descriere' => ['nullable', 'string', 'max:255'],
            'marca_masina' => ['nullable', 'string', 'max:255'],
            'serie_sasiu' => ['nullable', 'string', 'max:255'],
            'email_notificari' => ['nullable', 'email:rfc'],
            'observatii' => ['nullable', 'string'],
        ]);

        $masini_mementouri->update(Arr::only($validated, [
            'numar_inmatriculare',
            'descriere',
            'marca_masina',
            'serie_sasiu',
        ]));
        $masini_mementouri->memento?->update(Arr::only($validated, ['email_notificari', 'observatii']));

        $redirect = $request->input('redirect');

        if ($redirect === 'index') {
            return Redirect::route('masini-mementouri.index')->with('status', 'Datele mașinii au fost actualizate.');
        }

        return Redirect::route('masini-mementouri.show', $masini_mementouri)->with('status', 'Datele mașinii au fost actualizate.');
    }

    public function destroy(Masina $masini_mementouri): RedirectResponse
    {
        $masini_mementouri->delete();

        return Redirect::route('masini-mementouri.index')->with('status', 'Mașina a fost ștearsă.');
    }
}
