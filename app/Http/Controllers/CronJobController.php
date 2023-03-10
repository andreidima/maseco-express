<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Comanda;
use App\Models\MesajTrimisEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Validator;

use App\Traits\TrimiteSmsTrait;

use App\Notifications\CerereStatus;

class CronJobController extends Controller
{
    use TrimiteSmsTrait;

    public function cerereStatusComanda($key = null)
    {
        $config_key = \Config::get('variabile.cron_job_key');
        // dd($key, $config_key);

        if ($key === $config_key){

            // Trimitere mesaj catre Maseco, doar o singura data, in momentul in care incepe prima incarcare din comanda
            $comenzi = Comanda::with('locuriOperareIncarcari', 'locuriOperareDescarcari')
                ->whereHas('locuriOperareIncarcari', function($query){
                    $query->where('ordine', 1)
                        ->where('data_ora', '<=', Carbon::now()->addMinutes(14)->todatetimestring())
                        ->where('data_ora', '>=', Carbon::now()->subMinutes(14)->todatetimestring());
                })
                ->whereDoesntHave('emailInformareIncepereComanda')
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
                $emailTrimis = new \App\Models\MesajTrimisEmail;
                if (isset($comanda->transportator->email) && ($comanda->transportator->email !== 'adima@validsoftware.ro') && ($comanda->transportator->email !== 'andrei.dima@usm.ro')){
                    // echo 'nu este Andrei';
                    Mail::to('info@masecoexpres.net')->send(new \App\Mail\ComandaIncepere($comanda));
                    $emailTrimis->email = 'info@masecoexpres.net';
                } else {
                    // echo 'Este Andrei';
                    Mail::to('andrei.dima@usm.ro')->send(new \App\Mail\ComandaIncepere($comanda));
                    $emailTrimis->email = 'andrei.dima@usm.ro';
                }
                $emailTrimis->comanda_id = $comanda->id;
                $emailTrimis->firma_id = $comanda->transportator->id ?? '';
                $emailTrimis->categorie = 1;
                $emailTrimis->save();
            }


            // Trimitere mesaje catre transportatori
            $comenzi = Comanda::with('locuriOperareIncarcari', 'locuriOperareDescarcari')
                ->whereHas('locuriOperareIncarcari', function($query){
                    $query->where('data_ora', '<=', Carbon::now()->addMinutes(15)->todatetimestring());
                })
                ->whereHas('locuriOperareDescarcari', function($query){
                    $query->where('data_ora', '>=', Carbon::now()->subMinutes(14)->todatetimestring());
                })
                ->whereDoesntHave('emailuriCerereStatusComandaInUltimaPerioada')
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
                $mesaj = 'V?? rug??m accesati ' . url('/cerere-status-comanda/sms/' . $comanda->cheie_unica) . ', pentru a ne transmite statusul comenzii.' .
                            ' Multumim, Maseco Expres!';
                $this->trimiteSms('Comenzi', 'Status', $comanda->id, [$comanda->transportator->telefon ?? ''], $mesaj);

                // Trimitere email
                if (isset($comanda->transportator->email)){
                    // Validator::make($comanda->transportator->all(), [
                    //     'email' => 'email:rfc,dns',
                    // ])->validate();
                        // $comanda->transportator->validate([
                        //     'email' => 'email:rfc,dns',
                        // ]);


                    Mail::to($comanda->transportator->email)->send(new \App\Mail\ComandaStatus($comanda));

                    $emailTrimis = new \App\Models\MesajTrimisEmail;
                    $emailTrimis->comanda_id = $comanda->id;
                    $emailTrimis->firma_id = $comanda->transportator->id ?? '';
                    $emailTrimis->categorie = 2;
                    $emailTrimis->email = $comanda->transportator->email;
                    $emailTrimis->save();

                }

                // Trimitere WhatsApp
                $comanda->transportator->notify(new CerereStatus($comanda));
                // $request->user()->notify(new CerereStatus($comanda));
            }


        } else {
            echo 'Cheia pentru Cron Joburi nu este corect??!';
        }
    }
}
