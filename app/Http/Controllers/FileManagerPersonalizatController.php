<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class FileManagerPersonalizatController extends Controller
{
    public function afisareDirectoareSiFisiere($cale = null){
        // $files = File::allFiles(storage_path('app/filemanager/Folder 1'));
echo $cale;
echo '<br><br>';
        $directories = Storage::disk('filemanager')->directories($cale);
        // dd($cale);

        foreach ($directories as $directory) {
            echo '<a href="/file-manager-personalizat/' . $directory . '">' . substr($directory, strrpos($directory, '/') + 1) . '</a>';
            echo '<br>';
        }

        $files = Storage::disk('filemanager')->files($cale);

        foreach ($files as $file) {
            echo '<a href="/file-manager-personalizat-fisier/' . $file . '" target="_blank">' . substr($file, strrpos($file, '/') + 1) . '</a>';
            echo '<br>';
        }

        // dd($files, $directories);
    }

    public function showFile($cale = null)
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
}
