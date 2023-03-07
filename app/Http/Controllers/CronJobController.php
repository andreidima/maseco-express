<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Comanda;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

use App\Traits\TrimiteSmsTrait;

class CronJobController extends Controller
{
    use TrimiteSmsTrait;

    public function cerereStatusComanda($key = null)
    {
        $config_key = \Config::get('variabile.cron_job_key');
        // dd($key, $config_key);

        if ($key === $config_key){

            // Trimitere mesaje catre transportatori
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
                //     echo 'Comanda id: ' . $comanda->id;
                //     echo '<br>';
                //     if (isset($comanda->transportator->email)){
                //         echo 'Email transportator: ' . $comanda->transportator->email . '<br>';
                //     }
                //     echo '<br>';
                //     echo 'Incarcari: ';
                //     foreach ($comanda->locuriOperareIncarcari as $locOperareIncarcare){
                //         echo $locOperareIncarcare->pivot->ordine . '. ' . $locOperareIncarcare->pivot->data_ora . ', ';
                //     }

                //     echo '<br>';

                //     echo 'Descarcari: ';
                //     foreach ($comanda->locuriOperareDescarcari as $locOperareDescarcare){
                //         echo $locOperareDescarcare->pivot->ordine . '. ' . $locOperareDescarcare->pivot->data_ora . ', ';
                //     }
                //     echo '<br><br><br><br>';
                // }
                // dd('stop');

            foreach ($comenzi as $comanda){
                // Trimitere SMS
                $mesaj = 'Vă rugăm accesati ' . url('/cerere-status-comanda/sms/' . $comanda->cheie_unica) . ', pentru a ne transmite statusul comenzii.' .
                            ' Multumim, Maseco Expres!';
                $this->trimiteSms('Comenzi', 'Status', $comanda->id, [$comanda->transportator->telefon ?? ''], $mesaj);

                // Trimitere email
                if (isset($comanda->transportator->email)){
                    Mail::to($comanda->transportator->email)->send(new \App\Mail\ComandaStatus($comanda));
                }
            }


            // Trimitere mesaj catre Maseco, doar o singura data, in momentul in care incepe prima incarcare din comanda
            $comenzi = Comanda::with('locuriOperareIncarcari', 'locuriOperareDescarcari')
                ->whereHas('locuriOperareIncarcari', function($query){
                    $query->where('ordine', 1)
                        ->where('data_ora', '<=', Carbon::now()->addMinutes(4)->todatetimestring())
                        ->where('data_ora', '>=', Carbon::now()->subMinutes(4)->todatetimestring());
                })
                ->get();

                // Afisare in pagina pentru debug
                foreach ($comenzi as $comanda){
                    // echo 'Comanda id: ' . $comanda->id;
                    // echo '<br>';
                    // if (isset($comanda->transportator->email)){
                    //     echo 'Email transportator: ' . $comanda->transportator->email . '<br>';
                    // }
                    // echo '<br>';
                    // echo 'Incarcari: ';
                    // foreach ($comanda->locuriOperareIncarcari as $locOperareIncarcare){
                    //     echo $locOperareIncarcare->pivot->ordine . '. ' . $locOperareIncarcare->pivot->data_ora . ', ';
                    // }

                    // echo '<br>';

                    // echo 'Descarcari: ';
                    // foreach ($comanda->locuriOperareDescarcari as $locOperareDescarcare){
                    //     echo $locOperareDescarcare->pivot->ordine . '. ' . $locOperareDescarcare->pivot->data_ora . ', ';
                    // }
                    // echo '<br><br>';
                }
                // dd('stop');


            foreach ($comenzi as $comanda){
                // Trimitere email
                Mail::to('info@masecoexpres.net')->send(new \App\Mail\ComandaIncepere($comanda));
            }


        } else {
            echo 'Cheia pentru Cron Joburi nu este corectă!';
        }
    }
}
