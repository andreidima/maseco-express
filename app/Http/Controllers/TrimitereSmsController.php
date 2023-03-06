<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Comanda;
use Carbon\Carbon;

use App\Traits\TrimiteSmsTrait;

class TrimitereSmsController extends Controller
{
    use TrimiteSmsTrait;

    public function cronJobTrimitereAutomataSmsCerereStatusComanda($key = null)
    {
        $config_key = \Config::get('variabile.cron_job_key');
        // dd($key, $config_key);

        if ($key === $config_key){

            $comenzi = Comanda::with('locuriOperareIncarcari', 'locuriOperareDescarcari')
                ->whereHas('locuriOperareIncarcari', function($query){
                    $query->where('data_ora', '<=', Carbon::now()->todatetimestring());
                })
                ->whereHas('locuriOperareDescarcari', function($query){
                    $query->where('data_ora', '>=', Carbon::now()->subMinutes(4)->todatetimestring());
                })
                // ->whereDoesntHave('statusuri', function $query){
                //     $query->
                // })
                ->get();

                // Afisare in pagina pentru debug
                // foreach ($comenzi as $comanda){
                //     echo $comanda->id . '<br>';
                //     foreach ($comanda->locuriOperareIncarcari as $locOperareIncarcare){
                //         echo $locOperareIncarcare->pivot->ordine . '. ' . $locOperareIncarcare->pivot->data_ora . '<br>';
                //     }
                //     echo '<br><br>';
                //     foreach ($comanda->locuriOperareDescarcari as $locOperareDescarcare){
                //         echo $locOperareDescarcare->pivot->ordine . '. ' . $locOperareDescarcare->pivot->data_ora . '<br>';
                //     }
                //     echo '<br><br><br>';
                // }
                // dd('stop');

            foreach ($comenzi as $comanda){
                $mesaj = 'Vă rugăm accesati ' . url('/cerere-status-comanda/sofer/' . $comanda->cheie_unica) . ', pentru a ne transmite statusul comenzii.' .
                            ' Multumim, Maseco Expres!';
                $this->trimiteSms('Comenzi', 'Status', $comanda->id, [$comanda->transportator->telefon ?? ''], $mesaj);
            }


        } else {
            echo 'Cheia pentru Cron Joburi nu este corectă!';
        }
    }
}
