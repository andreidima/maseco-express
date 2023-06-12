<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\MementoAlerta;
use \Carbon\Carbon;

class CronJobAlertaController extends Controller
{
    public function trimiteAlerte(){
        $alerte = MementoAlerta::whereDate('data', Carbon::today())->get();
// dd($alerte);
        foreach ($alerte as $alerta) {
            echo $alerta->id . '<br>';
        }
    }
}
