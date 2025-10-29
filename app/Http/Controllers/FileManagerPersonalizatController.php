<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use App\Support\BrowserViewableFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileManagerPersonalizatController extends Controller
{
    public function afisareDirectoareSiFisiere(Request $request, $cale = null)
    {
        $searchFisier = $request->searchFisier;
        $fisiereGasite = [];
        if ($searchFisier) {
            $toateFisierele = Storage::disk('filemanager')->allFiles();
            foreach ($toateFisierele as $fisier) {
                if (strpos(strtolower($fisier), strtolower($searchFisier)) !== false) {
                    $fisiereGasite[] = $fisier;
                }
            }
        }
        $directoare = Storage::disk('filemanager')->directories($cale);
        $fisiere = Storage::disk('filemanager')->files($cale);

        // Sort directories and files using a case insensitive "natural order" algorithm
        natcasesort($directoare);
        natcasesort($fisiere);

        // Build the full directory tree starting from the root
        $directoryTree = $this->getDirectoryTree(null);

        return view('fileManagerPersonalizat.index', compact('cale', 'directoare', 'fisiere', 'searchFisier', 'fisiereGasite', 'directoryTree'));
    }

    /**
     * Recursively builds an array representing the directory tree.
     *
     * @param string $path
     * @return array
     */
    private function getDirectoryTree($path)
    {
        $directories = Storage::disk('filemanager')->directories($path);
        $tree = [];

        foreach ($directories as $directory) {
            $tree[] = [
                'name'     => basename($directory),
                'path'     => $directory,
                'isOpen'   => false,
                'children' => $this->getDirectoryTree($directory),
            ];
        }

        return $tree;
    }

    public function directorCreaza(Request $request)
    {
        $this->authorize('documente-manage');

        $request->validate([
            'cale'         => '',
            'numeDirector' => 'required',
        ]);

        // Build the directory path using forward slashes
        $directoryPath = $this->joinPath($request->cale, $request->numeDirector);

        try {
            if (! Storage::disk('filemanager')->makeDirectory($directoryPath)) {
                return back()->with('error', 'Nu s-a putut crea directorul.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'A apărut o eroare: ' . $e->getMessage());
        }

        return back()->with('status', 'Directorul „' . $request->numeDirector . '" a fost creat cu succes!');
    }

    public function directorSterge(Request $request, $cale = null)
    {
        $this->authorize('documente-manage');

        try {
            Storage::disk('filemanager')->deleteDirectory($cale);
        } catch (\Exception $e) {
            return back()->with('error', 'A apărut o eroare la ștergerea directorului: ' . $e->getMessage());
        }

        $exploded = explode("/", $cale);
        return back()->with('status', '„' . end($exploded) . '" a fost șters cu succes!');
    }

    public function fisiereAdauga(Request $request)
    {
        $this->authorize('documente-manage');

        $request->validate([
            'fisiere.*' => 'required|max:300000'
        ]);

        $directoryPath = $request->cale ? $this->joinPath($request->cale) : '';

        // Check for duplicate file names
        foreach ($request->file('fisiere') as $fisier) {
            $numeFisier = $fisier->getClientOriginalName();
            $filePath = $this->joinPath($directoryPath, $numeFisier);
            if (Storage::disk('filemanager')->exists($filePath)) {
                return back()->with('error', 'Există deja un fișier cu numele „' . $numeFisier . '”. Redenumește fișierul și încearcă din nou.');
            }
        }

        // Upload files and catch potential errors
        foreach ($request->file('fisiere') as $fisier) {
            $numeFisier = $fisier->getClientOriginalName();
            try {
                if (! Storage::disk('filemanager')->putFileAs($directoryPath, $fisier, $numeFisier)) {
                    return back()->with('error', 'Fișierele nu au putut fi încărcate.');
                }
            } catch (\Exception $e) {
                return back()->with('error', 'A apărut o eroare la încărcarea fișierelor: ' . $e->getMessage());
            }
        }

        return back()->with('status', 'Fișierele au fost adăugate cu succes!');
    }

    public function fisierDeschide($cale = null)
    {
        try {
            $mimeType = Storage::disk('filemanager')->mimeType($cale) ?? 'application/octet-stream';

            if (! BrowserViewableFile::isViewable(basename((string) $cale), $mimeType)) {
                return $this->fisierDownload($cale);
            }

            return $this->streamFromDisk('filemanager', $cale, basename((string) $cale), $mimeType);
        } catch (FileNotFoundException $exception) {
            abort(404);
        }
    }

    public function fisierDownload($cale = null)
    {
        try {
            $mimeType = Storage::disk('filemanager')->mimeType($cale) ?? 'application/octet-stream';

            return $this->downloadFromDisk('filemanager', $cale, basename((string) $cale), $mimeType);
        } catch (FileNotFoundException $exception) {
            abort(404);
        }
    }

    protected function streamFromDisk(string $disk, string $path, string $filename, string $mimeType): StreamedResponse
    {
        $stream = Storage::disk($disk)->readStream($path);

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
            'Content-Disposition' => BrowserViewableFile::contentDisposition('inline', $filename),
        ]);
    }

    protected function downloadFromDisk(string $disk, string $path, string $filename, string $mimeType): StreamedResponse
    {
        $stream = Storage::disk($disk)->readStream($path);

        if ($stream === false) {
            abort(404);
        }

        return response()->streamDownload(function () use ($stream) {
            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, $filename, [
            'Content-Type' => $mimeType,
        ]);
    }

    public function fisierSterge(Request $request, $cale = null)
    {
        $this->authorize('documente-manage');

        try {
            Storage::disk('filemanager')->delete($cale);
        } catch (\Exception $e) {
            return back()->with('error', 'A apărut o eroare la ștergerea fișierului: ' . $e->getMessage());
        }

        $exploded = explode("/", $cale);
        return back()->with('status', '„' . end($exploded) . '" a fost șters cu succes!');
    }

    public function modificaCaleNume(Request $request)
    {
        $this->authorize('documente-manage');

        $request->validate([
            'cale'           => '',
            'extensieFisier' => '',
            'numeVechi'      => 'required',
            'numeNou'       => 'required',
        ]);

        // Build full paths using forward slashes
        $caleNumeVechi = $this->joinPath($request->cale, $request->numeVechi);
        $caleNumeNou  = $this->joinPath($request->cale, $request->numeNou);

        // Append extension if provided (useful for files)
        if ($request->filled('extensieFisier')) {
            $caleNumeVechi .= '.' . $request->extensieFisier;
            $caleNumeNou  .= '.' . $request->extensieFisier;
        }

        try {
            if (! Storage::disk('filemanager')->move($caleNumeVechi, $caleNumeNou)) {
                return back()->with('error', 'Eroare la modificarea numelui resursei.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'A apărut o eroare: ' . $e->getMessage());
        }

        return back()->with('status', '„' . $request->numeNou . '" a fost modificat cu succes!');
    }

    /**
     * Copy a file.
     */
    public function copyFile(Request $request)
    {
        $this->authorize('documente-manage');

        $request->validate([
            'source'      => 'required',
            'destination' => 'nullable',
        ]);

        $source      = $request->source;
        $destination = $this->joinPath($request->destination ?? '', basename($source));

        // Check if destination file already exists
        if (Storage::disk('filemanager')->exists($destination)) {
            return back()->with('error', 'Fișierul există deja la destinație.');
        }

        try {
            if (! Storage::disk('filemanager')->copy($source, $destination)) {
                return back()->with('error', 'Nu s-a putut copia fișierul.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'A apărut o eroare la copierea fișierului: ' . $e->getMessage());
        }

        return back()->with('status', 'Fișierul a fost copiat cu succes!');
    }

    /**
     * Move a file.
     */
    public function moveFile(Request $request)
    {
        $this->authorize('documente-manage');

        $request->validate([
            'source'      => 'required',
            'destination' => 'nullable',
        ]);

        $source      = $request->source;
        $destination = $this->joinPath($request->destination ?? '', basename($source));

        // Check if destination file already exists
        if (Storage::disk('filemanager')->exists($destination)) {
            return back()->with('error', 'Fișierul există deja la destinație.');
        }

        try {
            if (! Storage::disk('filemanager')->move($source, $destination)) {
                return back()->with('error', 'Nu s-a putut muta fișierul.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'A apărut o eroare la mutarea fișierului: ' . $e->getMessage());
        }

        return back()->with('status', 'Fișierul a fost mutat cu succes!');
    }

    /**
     * Copy a directory.
     */
    public function copyDirectory(Request $request)
    {
        $this->authorize('documente-manage');

        $request->validate([
            'source'      => 'required',
            'destination' => 'nullable',
        ]);

        // For directories, destination should be the target parent folder plus a new folder name.
        $source      = $request->source;
        $folderName  = basename($source);
        $destination = $this->joinPath($request->destination ?? '', $folderName);

        // Check if destination directory already exists
        if (Storage::disk('filemanager')->exists($destination)) {
            return back()->with('error', 'Directorul există deja la destinație.');
        }

        try {
            // Use the custom recursive copy function.
            $this->recursiveCopy($source, $destination);
        } catch (\Exception $e) {
            return back()->with('error', 'A apărut o eroare la copierea directorului: ' . $e->getMessage());
        }

        return back()->with('status', 'Directorul a fost copiat cu succes!');
    }

    /**
     * Move a directory.
     */
    public function moveDirectory(Request $request)
    {
        $this->authorize('documente-manage');

        $request->validate([
            'source'      => 'required',
            'destination' => 'nullable',
        ]);

        $source      = $request->source;
        $folderName  = basename($source);
        $destination = $this->joinPath($request->destination ?? '', $folderName);

        // Check if destination directory already exists
        if (Storage::disk('filemanager')->exists($destination)) {
            return back()->with('error', 'Directorul există deja la destinație.');
        }

        try {
            // Use the custom recursive copy function.
            $this->recursiveCopy($source, $destination);
            // After copying, delete the original directory.
            Storage::disk('filemanager')->deleteDirectory($source);
        } catch (\Exception $e) {
            return back()->with('error', 'A apărut o eroare la mutarea directorului: ' . $e->getMessage());
        }

        return back()->with('status', 'Directorul a fost mutat cu succes!');
    }

    /**
    * Recursively copies a directory from source to destination.
    *
    * @param string $source
    * @param string $destination
    * @return void
    */
    private function recursiveCopy($source, $destination)
    {
        // Create the destination directory if it doesn't exist.
        if (! Storage::disk('filemanager')->exists($destination)) {
            Storage::disk('filemanager')->makeDirectory($destination);
        }

        // Copy all files from the source directory.
        foreach (Storage::disk('filemanager')->files($source) as $file) {
            $fileName = basename($file);
            Storage::disk('filemanager')->copy($file, $destination . '/' . $fileName);
        }

        // Recursively copy all subdirectories.
        foreach (Storage::disk('filemanager')->directories($source) as $dir) {
            $dirName = basename($dir);
            $newDestination = $destination . '/' . $dirName;
            $this->recursiveCopy($dir, $newDestination);
        }
    }

    /**
     * Joins given path parts using a forward slash and trims any extra slashes.
     *
     * @param mixed ...$parts
     * @return string
     */
    private function joinPath(...$parts)
    {
        $filtered = array_filter($parts, function ($p) {
            return $p !== null && $p !== '';
        });
        $trimmed = array_map(function ($p) {
            return trim($p, '/');
        }, $filtered);
        return implode('/', $trimmed);
    }
}
