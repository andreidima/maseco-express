<?php

namespace App\Http\Controllers\Masini;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masini\StoreMasinaFisierGeneralRequest;
use App\Models\Masini\Masina;
use App\Models\Masini\MasinaFisierGeneral;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MasinaFisierGeneralController extends Controller
{
    private const STORAGE_DISK = MasinaFisierGeneral::STORAGE_DISK;

    public function index(Request $request, Masina $masini_mementouri): View|JsonResponse
    {
        $masina = $masini_mementouri->loadMissing('fisiereGenerale');

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'files_html' => view('masini-mementouri.partials.general-files-list', [
                    'masina' => $masina,
                    'fisiere' => $masina->fisiereGenerale,
                ])->render(),
            ]);
        }

        return view('masini-mementouri.fisiere-generale.index', [
            'masina' => $masina,
            'fisiere' => $masina->fisiereGenerale,
        ]);
    }

    public function store(StoreMasinaFisierGeneralRequest $request, Masina $masini_mementouri): JsonResponse|RedirectResponse
    {
        $masina = $masini_mementouri;

        $file = $request->file('fisier');

        $directory = MasinaFisierGeneral::storageDirectoryForMasina($masina->id);
        $path = $file->store($directory, self::STORAGE_DISK);

        $masina->fisiereGenerale()->create([
            'cale' => $path,
            'nume_original' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'dimensiune' => $file->getSize(),
            'uploaded_by_id' => $request->user()?->id,
            'uploaded_by_name' => $request->user()?->name,
            'uploaded_by_email' => $request->user()?->email,
        ]);

        $message = __('Fișierul a fost încărcat.');

        if ($request->expectsJson()) {
            $masina->load('fisiereGenerale');

            return response()->json([
                'status' => 'ok',
                'message' => $message,
                'files_html' => view('masini-mementouri.partials.general-files-list', [
                    'masina' => $masina,
                    'fisiere' => $masina->fisiereGenerale,
                ])->render(),
            ]);
        }

        return Redirect::back()->with('status', $message);
    }

    public function download(Masina $masini_mementouri, MasinaFisierGeneral $fisier)
    {
        $masina = $masini_mementouri;

        abort_unless($fisier->masina_id === $masina->id, 404);

        $disk = Storage::disk(self::STORAGE_DISK);

        abort_unless($disk->exists($fisier->cale), 404);

        $headers = [];

        if ($mimeType = $fisier->guessMimeType()) {
            $headers['Content-Type'] = $mimeType;
        }

        return $disk->download(
            $fisier->cale,
            $fisier->downloadName(),
            $headers
        );
    }

    public function preview(Masina $masini_mementouri, MasinaFisierGeneral $fisier)
    {
        $masina = $masini_mementouri;

        abort_unless($fisier->masina_id === $masina->id, 404);
        abort_unless($fisier->isPreviewable(), 404);

        $disk = Storage::disk(self::STORAGE_DISK);

        abort_unless($disk->exists($fisier->cale), 404);

        $headers = [];

        if ($mimeType = $fisier->guessMimeType()) {
            $headers['Content-Type'] = $mimeType;
        }

        return $disk->response(
            $fisier->cale,
            $fisier->downloadName(),
            $headers
        );
    }

    public function destroy(Request $request, Masina $masini_mementouri, MasinaFisierGeneral $fisier): JsonResponse|RedirectResponse
    {
        $masina = $masini_mementouri;

        abort_unless($fisier->masina_id === $masina->id, 404);

        if ($fisier->cale) {
            Storage::disk(self::STORAGE_DISK)->delete($fisier->cale);
        }

        $fisier->delete();

        $message = __('Fișierul a fost șters.');

        if ($request->expectsJson()) {
            $masina->load('fisiereGenerale');

            return response()->json([
                'status' => 'ok',
                'message' => $message,
                'files_html' => view('masini-mementouri.partials.general-files-list', [
                    'masina' => $masina,
                    'fisiere' => $masina->fisiereGenerale,
                ])->render(),
            ]);
        }

        return Redirect::back()->with('status', $message);
    }
}
