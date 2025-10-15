<?php

namespace App\Http\Controllers\FacturiFurnizori;

use App\Http\Controllers\Controller;
use App\Models\FacturiFurnizori\FacturaFurnizor;
use App\Models\FacturiFurnizori\FacturaFurnizorFisier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class FacturaFurnizorFisierController extends Controller
{
    public function vizualizeaza(FacturaFurnizor $factura, FacturaFurnizorFisier $fisier)
    {
        $this->ensureBelongsToFactura($factura, $fisier);

        if (! $fisier->isPreviewable()) {
            return back()->with('error', 'Fișierul nu poate fi deschis în browser.');
        }

        if (! Storage::exists($fisier->cale)) {
            return back()->with('error', 'Fișierul nu a putut fi găsit.');
        }

        $displayName = $fisier->nume_original ?: basename($fisier->cale);
        $safeDisplayName = addcslashes($displayName, "\\\"");

        $headers = [
            'Content-Disposition' => 'inline; filename="' . $safeDisplayName . '"',
        ];

        $mimeType = Storage::mimeType($fisier->cale);

        if ($mimeType) {
            $headers['Content-Type'] = $mimeType;
        }

        return Storage::response($fisier->cale, $displayName, $headers);
    }

    public function descarca(FacturaFurnizor $factura, FacturaFurnizorFisier $fisier)
    {
        $this->ensureBelongsToFactura($factura, $fisier);

        if (! Storage::exists($fisier->cale)) {
            return back()->with('error', 'Fișierul nu a putut fi găsit.');
        }

        $downloadName = $fisier->nume_original ?: basename($fisier->cale);

        return Storage::download($fisier->cale, $downloadName);
    }

    public function destroy(FacturaFurnizor $factura, FacturaFurnizorFisier $fisier): RedirectResponse
    {
        $this->ensureBelongsToFactura($factura, $fisier);

        if ($fisier->cale && Storage::exists($fisier->cale)) {
            Storage::delete($fisier->cale);
        }

        $fisier->delete();

        return back()->with('status', 'Fișierul a fost șters.');
    }

    private function ensureBelongsToFactura(FacturaFurnizor $factura, FacturaFurnizorFisier $fisier): void
    {
        if ($fisier->factura_id !== $factura->id) {
            abort(404);
        }
    }
}
