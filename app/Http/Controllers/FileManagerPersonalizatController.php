<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class FileManagerPersonalizatController extends Controller
{
    public function afisareDirectoareSiFisiere($cale = null){
        $directories = Storage::disk('filemanager')->directories($cale);

        $fisiere = Storage::disk('filemanager')->files($cale);

        return view('fileManagerPersonalizat.index', compact('cale', 'directories', 'fisiere'));
    }

    public function fisierDeschide($cale = null)
    {
        //This method will look for the file and get it from drive
        try {
            $file = Storage::disk('filemanager')->get($cale);
            $type = Storage::disk('filemanager')->mimeType($cale);
            $response = Response::make($file, 200);
            $response->header("Content-Type", $type);
            return $response;
        } catch (FileNotFoundException $exception) {
            abort(404);
        }
    }

    public function fisierSterge($cale = null)
    {
        dd($cale);
        Storage::delete($cale);

        $exploded = explode("/", $cale);

        return back()->with('status', end($exploded) . '" a fost șters cu succes!');
    }
}
