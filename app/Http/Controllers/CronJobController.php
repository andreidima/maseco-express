<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Comanda;
use App\Models\ComandaCronJob;
use App\Models\Masini\MasinaDocument;
use App\Models\MesajTrimisEmail;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Mail;
use App\Models\MementoAlerta;
use App\Models\Factura;

use Illuminate\Support\Facades\Validator;

use App\Traits\TrimiteSmsTrait;

use App\Notifications\CerereStatus;
use App\Models\CronJobLog;
use Illuminate\Support\Arr;
use Throwable;

class CronJobController extends Controller
{
    use TrimiteSmsTrait;

    public function cerereStatusComanda($key = null)
    {
        $config_key = \Config::get('variabile.cron_job_key');

        if ($key !== $config_key){
            echo 'Cheia pentru Cron Joburi nu este corectă!';
            $this->logCronJobFailure('cron.cerere_status_comanda', 'Invalid cron key provided.', [
                'provided_key' => $key,
            ]);

            return ;
        }

        $this->executeCronJobWithLogging('cron.cerere_status_comanda', function () {
            $startNotificationEmails = 0;

            // Trimitere mesaj catre Maseco, doar o singura data, in momentul in care incepe prima incarcare din comanda
            $cronjobs = ComandaCronJob::with('comanda')
                ->where('inceput', '<=', Carbon::now()->addMinutes(14)->todatetimestring())
                ->where('sfarsit', '>=', Carbon::now()->subMinutes(14)->todatetimestring())
                ->where('informare_incepere_comanda', 0)
                ->whereHas('comanda', function($query){
                    $query->where('stare', '<>', 3); // comanda nu este anulata
                })
                ->get();

            $startNotificationEvaluated = $cronjobs->count();

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
                $startNotificationEmails++;

                // Se seteaza trimiterea si in CronJob pentru a nu se mai trimite inca odata
                $cronjob->informare_incepere_comanda = 1;
                $cronjob->save();
            }


            // Trimitere mesaje catre transportatori
            $statusCronjobs = ComandaCronJob::with('comanda')
                ->where('inceput', '<=', Carbon::now()->addMinutes(14)->addMinutes(15)->todatetimestring()) // se trimite mesaj cu 15 minute inainte de a incepe comanda
                ->where('sfarsit', '>=', Carbon::now()->subMinutes(14)->todatetimestring())
                ->where('contract_trimis_pe_email_catre_transportator', 1)
                ->whereHas('comanda', function($query){
                    $query->where('stare', '<>', 3) // comanda nu este anulata
                            ->where('interval_notificari', '<>', '00:00');
                })
                ->get();

            $statusEvaluated = $statusCronjobs->count();
            $statusSmsCount = 0;
            $statusEmailCount = 0;

            // Afisare in pagina pentru debug
            // foreach ($cronjobs as $cronjob){
            //     echo 'Comanda id: ' . $cronjob->comanda->id;
            //     echo '<br>';
            // }

            foreach ($statusCronjobs as $cronjob){
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
                        $statusSmsCount++;

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
                                $statusEmailCount++;

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

            return [
                'message' => sprintf(
                    'Trimise %d e-mailuri de pornire și procesate %d cronjoburi pentru notificări de status.',
                    $startNotificationEmails,
                    $statusEvaluated
                ),
                'payload' => [
                    'start_notifications' => [
                        'evaluated' => $startNotificationEvaluated,
                        'emails_sent' => $startNotificationEmails,
                    ],
                    'status_notifications' => [
                        'evaluated' => $statusEvaluated,
                        'sms_sent' => $statusSmsCount,
                        'emails_sent' => $statusEmailCount,
                    ],
                ],
            ];
        });
    }

    public function trimiteMementoAlerte($key = null){
        if ($key !== \Config::get('variabile.cron_job_key')){
            echo 'Cheia pentru Cron Joburi nu este corectă!';
            $this->logCronJobFailure('cron.trimite_memento_alerte', 'Invalid cron key provided.', [
                'provided_key' => $key,
            ]);

            return ;
        }

        $this->executeCronJobWithLogging('cron.trimite_memento_alerte', function () {
            $mementouriAlerte = MementoAlerta::with('memento')->whereDate('data', '=', Carbon::today())->get();

            // For personal tests
            echo '<b>Număr mementouri pentru ziua de azi</b>: ' . $mementouriAlerte->count() . '<br>';
            foreach ($mementouriAlerte as $index => $mementoAlerta) {
                echo ($index+1) . '. Memento: ';
                echo $mementoAlerta->memento->nume ?? '';
                echo '<br>';
            }

            $smsTrimise = 0;
            $emailTrimise = 0;

            if ($mementouriAlerte->isNotEmpty()){
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
                    $destinatar = $mementouriAlerteDeTrimisSms->first();
                    $this->trimiteSms('Memento', null, null, [$destinatar->memento->telefon ?? ''], $mesaj);
                    $smsTrimise++;
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
                                // mailer('office')
                                // to('masecoexpres@gmail.com')
                                to($alerta->memento->email)
                                // to('adima@validsoftware.ro')
                                // ->bcc(['contact@validsoftware.ro', 'adima@validsoftware.ro'])
                                // ->bcc(['andrei.dima@usm.ro'])
                                ->send(new \App\Mail\MementoAlerta($subiect, $mesaj)
                            );
                            $emailTrimise++;
                        }
                    }
                }
            }

            $alerteMasiniTrimise = $this->trimiteMasiniMementouriAlerte();

            return [
                'message' => sprintf(
                    'Procesate %d mementouri, trimise %d SMS-uri și %d e-mailuri. Alerte mașini: %d.',
                    $mementouriAlerte->count(),
                    $smsTrimise,
                    $emailTrimise,
                    $alerteMasiniTrimise
                ),
                'payload' => [
                    'mementouri_total' => $mementouriAlerte->count(),
                    'sms_sent' => $smsTrimise,
                    'emails_sent' => $emailTrimise,
                    'masini_alerte_trimise' => $alerteMasiniTrimise,
                ],
            ];
        });

            // echo $mesaj;
    }

    protected function trimiteMasiniMementouriAlerte(): int
    {
        $documente = MasinaDocument::with(['masina.memento'])
            ->whereNotNull('data_expirare')
            ->get();

        if ($documente->isEmpty()) {
            echo '<b>Alerte mementouri mașini</b>: 0<br>';
            return 0;
        }

        $alerteTrimise = 0;

        foreach ($documente as $document) {
            $days = $document->daysUntilExpiry();

            if ($days === null) {
                continue;
            }

            $thresholds = collect(MasinaDocument::notificationThresholds())->sortKeys();
            $lowerThresholdAlreadyTriggered = false;

            foreach ($thresholds as $threshold => $column) {
                if ($document->{$column}) {
                    $lowerThresholdAlreadyTriggered = true;
                    continue;
                }

                if ($lowerThresholdAlreadyTriggered) {
                    break;
                }

                if ($days <= $threshold) {
                    $recipients = collect([
                        $document->email_notificare,
                        optional($document->masina->memento)->email_notificari,
                    ])->filter()->unique()->values();

                    if ($recipients->isEmpty()) {
                        continue;
                    }

                    $validator = Validator::make(['emails' => $recipients->all()], ['emails.*' => 'email:rfc']);
                    if ($validator->fails()) {
                        $recipients = $recipients->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL));
                    }

                    if ($recipients->isEmpty()) {
                        continue;
                    }

                    $subiect = $document->masina->numar_inmatriculare . ' - ' . $document->label();
                    $mesaj = '';
                    $mesaj .= 'Vehicul: ' . $document->masina->numar_inmatriculare . '<br>';
                    $mesaj .= 'Document: ' . $document->label() . '<br>';
                    $mesaj .= 'Dată expirare: ' . ($document->data_expirare ? $document->data_expirare->isoFormat('DD.MM.YYYY') : '-') . '<br>';
                    $mesaj .= 'Prag alertă: ' . $threshold . ' zile.<br>';
                    $mesaj .= '<br>';

                    foreach ($recipients as $email) {
                        Mail::to($email)->send(new \App\Mail\MementoAlerta($subiect, $mesaj));
                    }

                    $document->markThresholdAsSent($threshold);
                    $alerteTrimise++;

                    break;
                }
            }
        }

        echo '<b>Alerte mementouri mașini</b>: ' . $alerteTrimise . '<br>';

        return $alerteTrimise;
    }

    public function trimiteMementoFacturi($key = null){
        if ($key !== \Config::get('variabile.cron_job_key')){
            echo 'Cheia pentru Cron Joburi nu este corectă!';
            $this->logCronJobFailure('cron.trimite_memento_facturi', 'Invalid cron key provided.', [
                'provided_key' => $key,
            ]);

            return ;
        }

        $this->executeCronJobWithLogging('cron.trimite_memento_facturi', function () {
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
            ->whereNotNull('zile_scadente')
            ->whereNotNull('alerte_scadenta')
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
            return [
                'message' => 'Nu au fost identificate facturi pentru notificare astăzi.',
                'payload' => [
                    'evaluated_invoices' => $facturi->count(),
                    'alerts_preparate' => 0,
                ],
            ];
        }

        // Trimitere email
        $subiectEmailCatreMaseco = 'FACTURI SCADENTE - ALERTE';
        $mesajEmailCatreMaseco = 'Bună,
            <br><br>
            Acestea sunt următoarele alerte de facturi scadente pe ziua de astăzi.
            <ul>';

        foreach ($facturiDeTrimisMesaj as $factura){

            $mesajEmailCatreMaseco .= '<li>Client ' . $factura->client_nume . ', factura <b>' . $factura->seria . $factura->numar . '</b>, comanda <b>' . $factura->client_contract . '</b>, scadentă <b>' . Carbon::parse($factura->data)->addDays($factura->zile_scadente)->isoFormat('DD.MM.YYYY') . '</b>';


            // Commented 15.01.2025 - We don't send to clients too anymore, just 1 email to Maseco containing all the invoices
            // if (isset($factura->client_email)){
            //     $validator = Validator::make(['email' => $factura->client_email], ['email' => 'email:rfc,dns',]);

            //     if ($validator->passes()){
            //         if ($factura->client_limba_id == 1) { // limba Romana
            //             $subiect = 'SCADENȚĂ FACTURĂ ' . $factura->seria . $factura->numar . ', pentru comanda ' . $factura->client_contract . ' - Maseco Expres';
            //             $mesaj = 'Bună ' . $factura->client_nume . ',
            //                 <br><br>
            //                 Te informăm că factura <b>' . $factura->seria . $factura->numar . '</b>, pentru comanda <b>' . $factura->client_contract . '</b>, va fi scadentă pe <b>' . Carbon::parse($factura->data)->addDays($factura->zile_scadente)->isoFormat('DD.MM.YYYY') . '</b>' .
            //                 '<br>
            //                 Te rugăm să confirmi dovada de plată pe <b>pod@masecoexpres.net</b>.
            //                 <br><br>
            //                 Acest mesaj este automat, te rugăm să nu răspunzi.
            //                 <br><br>
            //                 Mulțumim!
            //                 <br>
            //                 Echipa Maseco Expres
            //                 <br>';
            //         } else {
            //             $subiect = 'INVOICE DUE DATE ' . $factura->seria . $factura->numar . ', for order ' . $factura->client_contract . ' - Maseco Express';
            //             $mesaj = 'Hi ' . $factura->client_nume . ',
            //                 <br><br>
            //                 Please note that the invoice <b>' . $factura->seria . $factura->numar . '</b>, for order <b>' . $factura->client_contract . '</b>, will be due on <b>' . Carbon::parse($factura->data)->addDays($factura->zile_scadente)->isoFormat('DD.MM.YYYY') . '</b>' .
            //                 '<br>
            //                 Please send proof of payment to <b>pod@masecoexpres.net</b>.
            //                 <br><br>
            //                 This message is automated, please do not reply.
            //                 <br><br>
            //                 Thank you!
            //                 <br>
            //                 Maseco Expres Team
            //                 <br>';
            //         }

            //         // Trimitere memento prin email
            //         \Mail::mailer('invoice')
            //             ->to($factura->client_email)
            //             ->send(new \App\Mail\MementoFactura($subiect, $mesaj)
            //         );

            //         echo 'Mesaj trimis catre ' . $factura->client_email;
            //         echo '<br><br>';

            //         $mesajEmailCatreMaseco .= '</li>';
            //     } else {
            //         $mesajEmailCatreMaseco .= '<span style="color:red"> - EMAIL GREȘIT - NU S-A PUTUT TRIMITE NOTIFICAREA PRIN EMAIL</span>.</li>';
            //     }
            // } else {
            //     $mesajEmailCatreMaseco .= '<span style="color:red"> - EMAIL LIPSĂ - NU S-A PUTUT TRIMITE NOTIFICAREA PRIN EMAIL</span>.</li>';
            // }

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
            ->to('pod@masecoexpres.net')
            // ->bcc(['andrei.dima@usm.ro'])
            // to('adima@validsoftware.ro')
            // ->bcc(['contact@validsoftware.ro', 'adima@validsoftware.ro'])
            // ->bcc('pod@masecoexpres.net')
            // ->bcc('adima@validsoftware.ro')
            ->send(new \App\Mail\MementoFactura($subiectEmailCatreMaseco, $mesajEmailCatreMaseco)
        );

        return [
            'message' => sprintf(
                'Trimise alerte pentru %d facturi. Total facturi evaluate: %d.',
                $facturiDeTrimisMesaj->count(),
                $facturi->count()
            ),
            'payload' => [
                'evaluated_invoices' => $facturi->count(),
                'alerts_preparate' => $facturiDeTrimisMesaj->count(),
            ],
        ];
    });
}

    protected function executeCronJobWithLogging(string $jobKey, callable $callback, array $context = []): void
    {
        $route = optional(request()->route())->uri() ?? request()->path() ?? 'artisan';
        $startedAt = microtime(true);

        $log = CronJobLog::create([
            'job_key' => $jobKey,
            'route' => $route,
            'status' => CronJobLog::STATUS_STARTED,
            'message' => 'Job started.',
            'payload' => ! empty($context) ? $context : null,
        ]);

        try {
            $result = $callback();

            $message = 'Job completed successfully.';
            $payload = $context;

            if (is_array($result)) {
                $message = $result['message'] ?? $message;
                $payload = array_merge($payload, Arr::wrap($result['payload'] ?? []));
            }

            $log->update([
                'status' => CronJobLog::STATUS_COMPLETED,
                'message' => $message,
                'runtime' => round(microtime(true) - $startedAt, 4),
                'payload' => ! empty($payload) ? $payload : null,
            ]);
        } catch (Throwable $exception) {
            $log->update([
                'status' => CronJobLog::STATUS_FAILED,
                'message' => $exception->getMessage(),
                'runtime' => round(microtime(true) - $startedAt, 4),
                'payload' => array_merge($context, [
                    'exception' => [
                        'message' => $exception->getMessage(),
                        'code' => $exception->getCode(),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                    ],
                ]),
            ]);

            throw $exception;
        }
    }

    protected function logCronJobFailure(string $jobKey, string $message, array $payload = []): void
    {
        CronJobLog::create([
            'job_key' => $jobKey,
            'route' => optional(request()->route())->uri() ?? request()->path() ?? 'artisan',
            'status' => CronJobLog::STATUS_FAILED,
            'message' => $message,
            'runtime' => null,
            'payload' => ! empty($payload) ? $payload : null,
        ]);
    }
}
