<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Comanda;
use App\Models\ComandaCronJob;
use App\Models\MesajTrimisEmail;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Mail;
use App\Models\MementoAlerta;

use Illuminate\Support\Facades\Validator;

use App\Traits\TrimiteSmsTrait;

use App\Notifications\CerereStatus;

class CronJobController extends Controller
{
    use TrimiteSmsTrait;

    public function cerereStatusComanda($key = null)
    {
        $config_key = \Config::get('variabile.cron_job_key');

        if ($key !== $config_key){
            echo 'Cheia pentru Cron Joburi nu este corectă!';
            return ;
        }

        // Trimitere mesaj catre Maseco, doar o singura data, in momentul in care incepe prima incarcare din comanda
        $cronjobs = ComandaCronJob::with('comanda')
            ->where('inceput', '<=', Carbon::now()->addMinutes(14)->todatetimestring())
            ->where('sfarsit', '>=', Carbon::now()->subMinutes(14)->todatetimestring())
            ->where('informare_incepere_comanda', 0)
            ->whereHas('comanda', function($query){
                $query->where('stare', '<>', 3); // comanda nu este anulata
            })
            ->get();

            // Afisare in pagina pentru debug
            // foreach ($cronjobs as $cronjob){
            //     echo 'Comanda id: ' . $cronjob->comanda->id;
            //     echo '<br>';
            // }

        foreach ($cronjobs as $cronjob){
            // Trimitere email
            $emailTrimis = new \App\Models\MesajTrimisEmail;
            if (isset($cronjob->comanda->transportator->email) && ($cronjob->comanda->transportator->email !== 'adima@validsoftware.ro') && ($cronjob->comanda->transportator->email !== 'andrei.dima@usm.ro')){
                // echo 'nu este Andrei';
                Mail::to('info@masecoexpres.net')->send(new \App\Mail\ComandaIncepere($cronjob->comanda));
                $emailTrimis->email = 'info@masecoexpres.net';
            } else {
                // echo 'Este Andrei';
                Mail::to('andrei.dima@usm.ro')->send(new \App\Mail\ComandaIncepere($cronjob->comanda));
                $emailTrimis->email = 'andrei.dima@usm.ro';
            }
            $emailTrimis->comanda_id = $cronjob->comanda->id ?? '';
            $emailTrimis->firma_id = $cronjob->comanda->transportator->id ?? '';
            $emailTrimis->categorie = 1;
            $emailTrimis->save();

            // Se seteaza trimiterea si in CronJob pentru a nu se mai trimite inca odata
            $cronjob->informare_incepere_comanda = 1;
            $cronjob->save();
        }


        // Trimitere mesaje catre transportatori
        $cronjobs = ComandaCronJob::with('comanda')
            ->where('inceput', '<=', Carbon::now()->addMinutes(14)->addMinutes(15)->todatetimestring()) // se trimite mesaj cu 15 minute inainte de a incepe comanda
            ->where('sfarsit', '>=', Carbon::now()->subMinutes(14)->todatetimestring())
            ->where('contract_trimis_pe_email_catre_transportator', 1)
            ->whereHas('comanda', function($query){
                $query->where('stare', '<>', 3); // comanda nu este anulata
            })
            ->get();

            // Afisare in pagina pentru debug
            // foreach ($cronjobs as $cronjob){
            //     echo 'Comanda id: ' . $cronjob->comanda->id;
            //     echo '<br>';
            // }

        foreach ($cronjobs as $cronjob){
            // if ($cronjob->urmatorul_mesaj_incepand_cu ? Carbon::parse($cronjob->urmatorul_mesaj_incepand_cu)->lessThan(Carbon::now()->addMinutes(14)->todatetimestring()) : ''){
            //     echo 'da';
            //     echo 'Comanda id: ' . $cronjob->comanda->id;
            //     echo '<br>';
            // } else{
            //     echo 'nu';
            //     echo 'Comanda id: ' . $cronjob->comanda->id;
            //     echo '<br>';
            // }


            if (
                ($cronjob->urmatorul_mesaj_incepand_cu ? Carbon::parse($cronjob->urmatorul_mesaj_incepand_cu)->lessThan(Carbon::now()->addMinutes(14)->todatetimestring()) : true)
                // daca este ultima descarcare, se trimite notificare chiar daca a mai fost una trimisa in ultimul interval
                || ($cronjob->sfarsit ? Carbon::parse($cronjob->sfarsit)->lessThan(Carbon::now()) : '')
                ){
                    // Trimitere SMS
                    $mesaj = 'Va rugam accesati ' . url('/cerere-status-comanda/sms/' . $cronjob->comanda->cheie_unica) . ', pentru a ne transmite statusul comenzii.' .
                                ' Multumim, Maseco Expres!';
                    $this->trimiteSms('Comenzi', 'Status', $cronjob->comanda->id, [$cronjob->comanda->camion->telefon_sofer ?? ''], $mesaj);

                    // Trimitere email
                    if (isset($cronjob->comanda->transportator->email)){
                        $validator = Validator::make(['email' => $cronjob->comanda->transportator->email], ['email' => 'email:rfc,dns',]);

                        if ($validator->passes()){
                            Mail::to($cronjob->comanda->transportator->email)->send(new \App\Mail\ComandaStatus($cronjob->comanda));

                            $emailTrimis = new \App\Models\MesajTrimisEmail;
                            $emailTrimis->comanda_id = $cronjob->comanda->id;
                            $emailTrimis->firma_id = $cronjob->comanda->transportator->id ?? '';
                            $emailTrimis->categorie = 2;
                            $emailTrimis->email = $cronjob->comanda->transportator->email;
                            $emailTrimis->save();

                            // echo ('Corect: ' . $cronjob->comanda->transportator->email . '<br>');
                        } else {
                            // echo ('Gresit: ' . $cronjob->comanda->transportator->email . '<br>');
                        }
                    }

                    // Se seteaza trimiterea in CronJob pentru a nu se mai trimite inca odata
                    $cronjob->urmatorul_mesaj_incepand_cu = Carbon::now()->addMinutes(
                        $cronjob->comanda->interval_notificari ? CarbonInterval::createFromFormat('H:i:s', $cronjob->comanda->interval_notificari)->totalMinutes : 180
                    );
                    $cronjob->save();

            }

                    // Trimitere WhatsApp
                    // if (
                    //     (($cronjob->comanda->transportator->email ?? '') === 'adima@validsoftware.ro')
                    //     || (($cronjob->comanda->transportator->email ?? '') === 'andrei.dima@usm.ro'))
                    // {
                    //     $cronjob->comanda->transportator->notify(new CerereStatus($cronjob->comanda));

                    //     echo 'WhatsApp sent';
                    // }
        }

    }

    public function trimiteMementoAlerte($key = null){
        if ($key !== \Config::get('variabile.cron_job_key')){
            echo 'Cheia pentru Cron Joburi nu este corectă!';
            return ;
        }

        $mementouriAlerte = MementoAlerta::whereDate('data', '=', Carbon::today())->get();

        // Daca nu este nici o alerta setata pentru ziua curenta, se termina functia
        if (count($mementouriAlerte) === 0){
            return;
        }

        $mesaj = '';
// dd($mementouriAlerte);
        foreach ($mementouriAlerte as $alerta){
            $mesaj .= 'Nume: ' . ($alerta->memento->nume ?? '') . '<br>';
            $mesaj .= 'Dată expirare: ' . ($alerta->memento->data_expirare ? Carbon::parse($alerta->memento->data_expirare)->isoFormat('DD.MM.YYYY') : '') . '<br>';
            $mesaj .= 'Descriere: ' . ($alerta->memento->descriere ?? '') . '<br>';
            $mesaj .= 'Observații: ' . ($alerta->memento->observatii ?? '') . '<br>';
            $mesaj .= '<br>';
        }

            // Trimitere alerta prin email
            \Mail::
                to('info@masecoexpres.net')
                // to('andrei.dima@usm.ro')
                // ->bcc(['contact@validsoftware.ro', 'adima@validsoftware.ro'])
                ->bcc(['andrei.dima@usm.ro'])
                ->send(new \App\Mail\MementoAlerta($mesaj)
            );
            echo $mesaj;


        if (isset($cronjobs)){
            foreach ($cronjobs as $cronjob){
                // Trimitere email
                $emailTrimis = new \App\Models\MesajTrimisEmail;
                if (isset($cronjob->comanda->transportator->email) && ($cronjob->comanda->transportator->email !== 'adima@validsoftware.ro') && ($cronjob->comanda->transportator->email !== 'andrei.dima@usm.ro')){
                    // echo 'nu este Andrei';
                    Mail::to('info@masecoexpres.net')->send(new \App\Mail\ComandaIncepere($cronjob->comanda));
                    $emailTrimis->email = 'info@masecoexpres.net';
                } else {
                    // echo 'Este Andrei';
                    Mail::to('andrei.dima@usm.ro')->send(new \App\Mail\ComandaIncepere($cronjob->comanda));
                    $emailTrimis->email = 'andrei.dima@usm.ro';
                }
                $emailTrimis->comanda_id = $cronjob->comanda->id ?? '';
                $emailTrimis->firma_id = $cronjob->comanda->transportator->id ?? '';
                $emailTrimis->categorie = 1;
                $emailTrimis->save();

                // Se seteaza trimiterea si in CronJob pentru a nu se mai trimite inca odata
                $cronjob->informare_incepere_comanda = 1;
                $cronjob->save();
            }
        }
    }
}
