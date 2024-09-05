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
use App\Models\Factura;

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
            if (isset($cronjob->comanda->transportator->email) && ($cronjob->comanda->transportator->email === 'adima@validsoftware.ro') && ($cronjob->comanda->transportator->email === 'andrei.dima@usm.ro')){
                // echo 'Este Andrei';
                Mail::to('andrei.dima@usm.ro')->send(new \App\Mail\ComandaIncepere($cronjob->comanda));
                $emailTrimis->email = 'andrei.dima@usm.ro';
            } else {
                // echo 'nu este Andrei';
                Mail::to('info@masecoexpres.net')->send(new \App\Mail\ComandaIncepere($cronjob->comanda));
                $emailTrimis->email = 'info@masecoexpres.net';
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
                $query->where('stare', '<>', 3) // comanda nu este anulata
                        ->where('interval_notificari', '<>', '00:00');
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

        $mementouriAlerte = MementoAlerta::with('memento')->whereDate('data', '=', Carbon::today())->get();

        // Daca nu este nici o alerta setata pentru ziua curenta, se termina functia
        if (count($mementouriAlerte) === 0){
            return;
        }

        // Trimitere SMS
        $mementouriAlerteDeTrimisSmsGrupateDupaTelefon = $mementouriAlerte->whereNotNull('memento.telefon')->groupBy('memento.telefon');
        foreach($mementouriAlerteDeTrimisSmsGrupateDupaTelefon as $mementouriAlerteDeTrimisSms){
            $mesaj = 'Memento aplicatie! ';
            foreach($mementouriAlerteDeTrimisSms as $alerta){
                $mesaj .= ($alerta->memento->nume ?? '');
                if ($alerta->memento->data_expirare){
                    $mesaj .= ', expirare ' . Carbon::parse($alerta->memento->data_expirare)->isoFormat('DD.MM.YYYY');
                }
                if ($alerta->memento->descriere){
                    $mesaj .= ', ' . $alerta->memento->descriere;
                }
                if ($alerta->memento->observatii){
                    $mesaj .= ', ' . $alerta->memento->observatii;
                }
                $mesaj .= ". ";
            }
            $this->trimiteSms('Memento', null, null, [$alerta->memento->telefon ?? ''], $mesaj);
        }

        // Trimitere email
        foreach ($mementouriAlerte as $alerta){
            if (isset($alerta->memento->email)){
                $validator = Validator::make(['email' => $alerta->memento->email], ['email' => 'email:rfc,dns',]);

                if ($validator->passes()){
                    $subiect = $alerta->memento->nume ?? '';

                    $mesaj = '';
                    $mesaj .= 'Nume: ' . ($alerta->memento->nume ?? '') . '<br>';
                    $mesaj .= 'Dată expirare: ' . ($alerta->memento->data_expirare ? Carbon::parse($alerta->memento->data_expirare)->isoFormat('DD.MM.YYYY') : '') . '<br>';
                    $mesaj .= 'Descriere: ' . ($alerta->memento->descriere ?? '') . '<br>';
                    $mesaj .= 'Observații: ' . ($alerta->memento->observatii ?? '') . '<br>';
                    $mesaj .= '<br>';

                    // Trimitere alerta prin email
                    \Mail::
                        // to('masecoexpres@gmail.com')
                        to($alerta->memento->email)
                        // to('adima@validsoftware.ro')
                        // ->bcc(['contact@validsoftware.ro', 'adima@validsoftware.ro'])
                        // ->bcc(['andrei.dima@usm.ro'])
                        ->send(new \App\Mail\MementoAlerta($subiect, $mesaj)
                    );
                }
            }
        }

            // echo $mesaj;
    }

    public function trimiteMementoFacturi($key = null){
        if ($key !== \Config::get('variabile.cron_job_key')){
            echo 'Cheia pentru Cron Joburi nu este corectă!';
            return ;
        }

        echo 'Trimitere memento facturi - scadenta';
        echo '<br>';
        echo 'Mai jos se vor afisa emailurile catre care s-au trimis mementourile.';
        echo '<br><br>';

        // $comenzi = Comanda::select('id', 'client_data_factura', 'client_zile_scadente', 'client_zile_inainte_de_scadenta_memento_factura')
        //     ->whereDate('client_data_factura', '>', Carbon::now()->subDays(100))
        //     ->get();

        // $arrayIdComenziDeTrimisMesaj = [];
        // foreach ($comenzi as $comanda){
        //     if ($comanda->client_zile_scadente){ // daca exista zile scadente
        //         if (Carbon::parse($comanda->client_data_factura)->addDays($comanda->client_zile_scadente) >= Carbon::now()){  // daca nu a trecut scadenta
        //             $zileInainte = preg_split ("/\,/", $comanda->client_zile_inainte_de_scadenta_memento_factura);
        //             foreach ($zileInainte as $ziInainte){
        //                 // daca scandenta - ziua inainte = astazi, se salveaza in arrayul de comenzi pentru trimitere mesaj
        //                 if (Carbon::parse($comanda->client_data_factura)->addDays($comanda->client_zile_scadente - $ziInainte)->toDateString() == Carbon::today()->toDateString()){
        //                     array_push($arrayIdComenziDeTrimisMesaj, $comanda->id,);
        //                 }
        //             }
        //         }
        //     }
        // }

        // $comenziDeTrimisMesaj = Comanda::with('client')->whereIn('id', $arrayIdComenziDeTrimisMesaj)->get();

        // // Daca nu este nici un memento de trimis pentru ziua curenta, se termina functia
        // if (count($comenziDeTrimisMesaj) === 0){
        //     return;
        // }


        $facturi = Factura::select('id', 'data', 'zile_scadente', 'alerte_scadenta')
            ->whereDate('data', '>', Carbon::now()->subDays(100))
            // ->where(function ($query) {  // not invoices that  allready received the alert today
            //     $query->whereNull('ultima_alerta_trimisa')
            //           ->orwhereDate('ultima_alerta_trimisa', '<>', Carbon::now());
            // })
            ->get();

// dd($facturi->count());
        $arrayIdFacturiDeTrimisMesaj = [];
        foreach ($facturi as $factura){
            if ($factura->zile_scadente){ // daca exista zile scadente
                if (Carbon::parse($factura->data)->addDays($factura->zile_scadente) >= Carbon::today()){  // daca nu a trecut scadenta
                    $zileInainte = preg_split ("/\,/", $factura->alerte_scadenta);
                    // echo "Zile scadente (" . $factura->zile_scadente . ")este integer? " . is_int($factura->zile_scadente) . '<br>';
                    foreach ($zileInainte as $ziInainte){
                        $ziInainte = intval($ziInainte);
                        if (is_int($ziInainte)) {
                            // dd($factura->zile_scadente, intval($ziInainte));
                            // echo $ziInainte . " este integer? " . is_int($factura->zile_scadente) . '<br>';
                            // echo Carbon::parse($factura->data)->addDays($factura->zile_scadente - $ziInainte) . '<br>' . Carbon::today();
                            // daca scandenta - ziua inainte = astazi, se salveaza in arrayul de facturi pentru trimitere mesaj
                            if (Carbon::parse($factura->data)->addDays($factura->zile_scadente - $ziInainte)->eq(Carbon::today())){
                                    array_push($arrayIdFacturiDeTrimisMesaj, $factura->id);
                                }
                        }
                    }
                }
            }
        }
// dd($arrayIdFacturiDeTrimisMesaj);
        $facturiDeTrimisMesaj = Factura::with('comanda.client')->whereIn('id', $arrayIdFacturiDeTrimisMesaj)->get();

        // Daca nu este nici un memento de trimis pentru ziua curenta, se termina functia
        if (count($facturiDeTrimisMesaj) === 0){
            return;
        }

// dd($facturiDeTrimisMesaj->count(), $facturiDeTrimisMesaj);

        // Trimitere email
        $subiectEmailCatreMaseco = 'FACTURI SCADENTE - ALERTE';
        $mesajEmailCatreMaseco = 'Bună,
            <br><br>
            Acestea sunt următoarele alerte de facturi scadente pe ziua de astăzi. Alertele au fost trimise si catre emailurile clientilor:
            <ul>';

        foreach ($facturiDeTrimisMesaj as $factura){
            // // The invoice is marked, to not send another email to the same invoice today
            // $factura->ultima_alerta_trimisa = Carbon::today();
            // $factura->save();

            if (isset($factura->client_email)){
                $validator = Validator::make(['email' => $factura->client_email], ['email' => 'email:rfc,dns',]);

                if ($validator->passes()){
                    if ($factura->client_limba_id == 1) { // limba Romana
                        $subiect = 'SCADENȚĂ FACTURĂ ' . $factura->seria . $factura->numar . ', pentru comanda ' . $factura->client_contract . ' - Maseco Expres';
                        $mesaj = 'Bună ' . $factura->client_nume . ',
                            <br><br>
                            Te informăm că factura <b>' . $factura->seria . $factura->numar . '</b>, pentru comanda <b>' . $factura->client_contract . '</b>, va fi scadentă pe <b>' . Carbon::parse($factura->data)->addDays($factura->zile_scadente)->isoFormat('DD.MM.YYYY') . '</b>' .
                            '<br>
                            Te rugăm să confirmi dovada de plată pe <b>pod@masecoexpres.net</b>.
                            <br><br>
                            Acest mesaj este automat, te rugăm să nu răspunzi.
                            <br><br>
                            Mulțumim!
                            <br>
                            Echipa Maseco Expres
                            <br>';
                    } else {
                        $subiect = 'INVOICE DUE DATE ' . $factura->seria . $factura->numar . ', for order ' . $factura->client_contract . ' - Maseco Express';
                        $mesaj = 'Hi ' . $factura->client_nume . ',
                            <br><br>
                            Please note that the invoice <b>' . $factura->seria . $factura->numar . '</b>, for order <b>' . $factura->client_contract . '</b>, will be due on <b>' . Carbon::parse($factura->data)->addDays($factura->zile_scadente)->isoFormat('DD.MM.YYYY') . '</b>' .
                            '<br>
                            Please send proof of payment to <b>pod@masecoexpres.net</b>.
                            <br><br>
                            This message is automated, please do not reply.
                            <br><br>
                            Thank you!
                            <br>
                            Maseco Expres Team
                            <br>';
                    }

                    // // Trimitere memento prin email
                    // \Mail::mailer('invoice')
                    //     // to('masecoexpres@gmail.com')
                    //     ->to($factura->client_email)
                    //     // to(['andrei.dima@usm.ro'])
                    //     // to('adima@validsoftware.ro')
                    //     // ->bcc(['contact@validsoftware.ro', 'adima@validsoftware.ro'])
                    //     ->bcc('pod@masecoexpres.net')
                    //     // ->bcc('adima@validsoftware.ro')
                    //     ->send(new \App\Mail\MementoFactura($subiect, $mesaj)
                    // );

                    echo 'Mesaj trimis catre ' . $factura->client_email;
                    echo '<br><br>';

                    $mesajEmailCatreMaseco .= '<li>Client ' . $factura->client_nume . ', factura <b>' . $factura->seria . $factura->numar . '</b>, comanda <b>' . $factura->client_contract . '</b></li>';
                } else {
                    $mesajEmailCatreMaseco .= '<li>Client ' . $factura->client_nume . ', factura <b>' . $factura->seria . $factura->numar . '</b>, comanda <b>' . $factura->client_contract . '</b><span style="color:red"> - EMAIL GREȘIT - NU S-A PUTUT TRIMITE NOTIFICAREA PRIN EMAIL</span>.</li>';
                }
            } else {
                $mesajEmailCatreMaseco .= '<li>Client ' . $factura->client_nume . ', factura <b>' . $factura->seria . $factura->numar . '</b>, comanda <b>' . $factura->client_contract . '</b><span style="color:red"> - EMAIL LIPSĂ - NU S-A PUTUT TRIMITE NOTIFICAREA PRIN EMAIL</span>.</li>';
            }

        }

        $mesajEmailCatreMaseco .= "</ul>
            Acest mesaj este automat, te rugăm să nu răspunzi.
            <br><br>
            Mulțumim!
            <br>
            Echipa Maseco Expres
            <br>";

        // Send email to to Maseco with today alerts
        \Mail::mailer('invoice')
            // to('masecoexpres@gmail.com')
            // ->to($factura->client_email)
            ->to(['andrei.dima@usm.ro'])
            // to('adima@validsoftware.ro')
            // ->bcc(['contact@validsoftware.ro', 'adima@validsoftware.ro'])
            // ->bcc('pod@masecoexpres.net')
            // ->bcc('adima@validsoftware.ro')
            ->send(new \App\Mail\MementoFactura($subiectEmailCatreMaseco, $mesajEmailCatreMaseco)
        );
    }
}
