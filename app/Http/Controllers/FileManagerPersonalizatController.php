<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class FileManagerPersonalizatController extends Controller
{
    public function afisareDirectoareSiFisiere(Request $request, $cale = null){
        $searchFisier = $request->searchFisier;
        $fisiereGasite = [];
        if ($searchFisier){
            $toateFisierele = Storage::disk('filemanager')->allFiles();
            foreach ($toateFisierele as $fisier){
                if (strpos(strtolower($fisier), strtolower($searchFisier)) !== false){
                    $fisiereGasite[] = $fisier;
                }
            }
        }
        $directoare = Storage::disk('filemanager')->directories($cale);

        $fisiere = Storage::disk('filemanager')->files($cale);
        // natcasesort — Sort an array using a case insensitive "natural order" algorithm
        natcasesort($directoare);
        natcasesort($fisiere);

        // Get all directories for tree explorer
        $directories = Storage::disk('filemanager')->directories(null);
        $tree = [];
        foreach ($directories as $directory) {
            $tree[] = [
                'name'     => basename($directory),
                'path'     => $directory,
                // Recursively get the subdirectories of this directory.
                'children' => $this->getDirectoryTree($directory),
            ];
        }

        // Build the full directory tree starting from $path (or root if empty)
        $directoryTree = $this->getDirectoryTree(null);

        return view('fileManagerPersonalizat.index', compact('cale', 'directoare', 'fisiere', 'searchFisier', 'fisiereGasite', 'directoryTree'));
    }

    public function directorCreaza(Request $request)
    {
        $request->validate(
            [
                'cale' => '',
                'numeDirector' => 'required',
            ],
        );

        Storage::disk('filemanager')->makeDirectory($request->cale . '/' . $request->numeDirector);

        return back()->with('status', 'Directorul „' . $request->numeDirector . '" a fost creat cu succes!');
    }

    public function directorSterge($cale = null)
    {
        if (auth()->user()->role != "1"){
            return back()->with('error', 'Nu aveți dreptul să ștergeti directoare! Contactați administratorul aplicației.');
        }

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

        foreach ($request->file('fisiere') as $fisier) {
            $numeFisier = $fisier->getClientOriginalName();
            if (Storage::disk('filemanager')->exists($request->cale . '/' . $numeFisier)){
                return back()->with('error', 'Există deja un fișier cu numele „' . $numeFisier . '”. Redenumește fișierul și încearcă din nou.');
            }
        }
        foreach ($request->file('fisiere') as $fisier) {
            $numeFisier = $fisier->getClientOriginalName();
            if (! Storage::disk('filemanager')->putFileAs($request->cale, $fisier, $numeFisier)){
                return back()->with('error', 'Fișierele nu au putut fi încărcate.');
            }
        }

        return back()->with('status', 'Fișierele au fost adăugate cu succes!');
    }

    public function fisierDeschide($cale = null)
    {
        // dd('here');
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
        if (auth()->user()->role != "1"){
            return back()->with('error', 'Nu aveți dreptul să ștergeti fișiere! Contactați administratorul aplicației.');
        }

        Storage::disk('filemanager')->delete($cale);

        $exploded = explode("/", $cale);

        return back()->with('status', '„' . end($exploded) . '" a fost șters cu succes!');
    }

    public function modificaCaleNume(Request $request)
    {
        if (auth()->user()->role != "1"){
            return back()->with('error', 'Nu aveți dreptul de modificare! Contactați administratorul aplicației.');
        }

        $request->validate(
            [
                'cale' => '',
                'extensieFisier' => '', // necesar atunci cand este vorba de fisiere
                'numeVechi' => 'required',
                'numeNou' => 'required',
            ],
        );

        $caleNumeVechi = $request->cale . '\\' . $request->numeVechi;
        $caleNumeNou = $request->cale . '\\' . $request->numeNou;

        // Daca este fisier, se adauga si extensia
        if ($request->extensieFisier) {
            $caleNumeVechi .= '.' . $request->extensieFisier;
            $caleNumeNou .= '.' . $request->extensieFisier;
        }

        Storage::disk('filemanager')->move($caleNumeVechi, $caleNumeNou);

        return back()->with('status', '„' . $request->numeNou . '" a fost modificat cu succes!');
    }

    /**
     * Recursively builds an array representing the directory tree.
     *
     * @param string $path
     * @return array
     */
    private function getDirectoryTree($path)
    {
        // Get immediate subdirectories
        $directories = Storage::disk('filemanager')->directories($path);
        $tree = [];

        foreach ($directories as $directory) {
            $tree[] = [
                'name'     => basename($directory),
                'path'     => $directory,
                'isOpen'   => false,
                // Recursively get the subdirectories of this directory.
                'children' => $this->getDirectoryTree($directory),
            ];
        }

        return $tree;
    }
}
