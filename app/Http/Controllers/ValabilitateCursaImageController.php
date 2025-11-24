<?php

namespace App\Http\Controllers;

use App\Models\Valabilitate;
use App\Models\ValabilitateCursa;
use App\Models\ValabilitateCursaImage;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\PDF;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ValabilitateCursaImageController extends Controller
{
    public function index(Valabilitate $valabilitate, ValabilitateCursa $cursa): View
    {
        $this->authorize('view', $valabilitate);

        $this->assertBelongsToValabilitate($valabilitate, $cursa);
        $cursa->loadMissing('images');

        return view('valabilitati.curse.imagini.index', [
            'valabilitate' => $valabilitate,
            'cursa' => $cursa,
        ]);
    }

    public function download(Valabilitate $valabilitate, ValabilitateCursa $cursa, ValabilitateCursaImage $imagine): StreamedResponse
    {
        $this->authorize('view', $valabilitate);

        $this->assertBelongsToValabilitate($valabilitate, $cursa);
        $this->assertBelongsToCursa($cursa, $imagine);

        abort_if($imagine->trashed(), 404);

        if (! Storage::exists($imagine->path)) {
            abort(404);
        }

        $imageContent = Storage::get($imagine->path);

        $imageDataUri = sprintf(
            'data:%s;base64,%s',
            $imagine->mime_type ?: 'application/octet-stream',
            base64_encode($imageContent)
        );

        $pdf = PDF::loadView('valabilitati.curse.imagini.pdf', [
            'imagine' => $imagine,
            'imageDataUri' => $imageDataUri,
        ]);
        $pdf->getDomPDF()->set_option('enable_php', true);

        $filename = pathinfo($imagine->original_name ?: 'imagine', PATHINFO_FILENAME) . '.pdf';

        $pdfContent = $pdf->output();

        return response()->streamDownload(
            static function () use ($pdfContent): void {
                echo $pdfContent;
            },
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    private function assertBelongsToValabilitate(Valabilitate $valabilitate, ValabilitateCursa $cursa): void
    {
        abort_unless((int) $cursa->valabilitate_id === (int) $valabilitate->id, 404);
    }

    private function assertBelongsToCursa(ValabilitateCursa $cursa, ValabilitateCursaImage $imagine): void
    {
        abort_unless((int) $imagine->valabilitate_cursa_id === (int) $cursa->id, 404);
    }
}
