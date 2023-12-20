<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class FileManagerPersonalizatController extends Controller
{
    public function afisareDirectoareSiFisiere($cale = null){
        $directoare = Storage::disk('filemanager')->directories($cale);

        $fisiere = Storage::disk('filemanager')->files($cale);

        return view('fileManagerPersonalizat.index', compact('cale', 'directoare', 'fisiere'));
    }

    public function directorCreaza(Request $request)
    {
        $request->validate(
            [
                'cale' => 'required',
                'numeDirector' => 'required',
            ],
        );

        Storage::disk('filemanager')->makeDirectory($request->cale . '\\' . $request->numeDirector);

        return back()->with('status', 'Directorul „' . $request->numeDirector . '" a fost creat cu succes!');
    }

    public function directorSterge($cale = null)
    {
        Storage::disk('filemanager')->deleteDirectory($cale);

        $exploded = explode("/", $cale);

        return back()->with('status', '„' . end($exploded) . '" a fost șters cu succes!');
    }

    public function fisiereAdauga(Request $request)
    {
        $request->validate(
            [
                'fisiere.*' => 'required|max:300000'
            ],
        );

        Storage::disk('filemanager')->makeDirectory($request->cale . '\\' . $request->numeDirector);

        return back()->with('status', 'Directorul „' . $request->numeDirector . '" a fost creat cu succes!');
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
        Storage::disk('filemanager')->delete($cale);

        $exploded = explode("/", $cale);

        return back()->with('status', '„' . end($exploded) . '" a fost șters cu succes!');
    }
}
