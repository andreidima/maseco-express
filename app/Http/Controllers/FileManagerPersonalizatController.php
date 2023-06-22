<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use File;
use Illuminate\Support\Facades\Storage;

class FileManagerPersonalizatController extends Controller
{
    public function afisareDirectoareSiFisiere(){
        // $files = File::allFiles(storage_path('app/filemanager/Folder 1'));

        $directories = Storage::disk('filemanager')->directories('');

        foreach ($directories as $directory) {
            echo $directory;
        }
        echo

        // $directory = "app/filemanager/Folder 1";
        $directory = "filemanager/Folder 1";
        $files = Storage::files($directory);

        dd($files, $directories);
    }
}
