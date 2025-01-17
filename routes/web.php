<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FirmaController;
use App\Http\Controllers\CamionController;
use App\Http\Controllers\LocOperareController;
use App\Http\Controllers\ComandaController;
use App\Http\Controllers\AxiosController;
use App\Http\Controllers\MesajTrimisSmsController;
use App\Http\Controllers\CronJobController;
use App\Http\Controllers\StatusComandaActualizatDeTransportatorController;
use App\Http\Controllers\ComandaStatusController;
use App\Http\Controllers\FisierController;
use App\Http\Controllers\MementoController;
use App\Http\Controllers\FileManagerPersonalizatController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RaportController;
use App\Http\Controllers\DiverseTesteController;
use App\Http\Controllers\ComandaFisierInternController;
use App\Http\Controllers\ComandaIncarcareDocumenteDeCatreTransportatorController;
use App\Http\Controllers\StatiePecoController;
use App\Http\Controllers\IntermediereController;
use App\Http\Controllers\FlotaStatusController;
use App\Http\Controllers\FlotaStatusInformatieController;
use App\Http\Controllers\DocumentWordController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Auth::routes(['register' => false, 'password.request' => false, 'reset' => false]);

// Trimitere Cron joburi din Cpanel
Route::any('/cron-jobs/cerere-status-comanda/{key}', [CronJobController::class, 'cerereStatusComanda']);
Route::get('/cron-jobs/memento-alerte/{key}', [CronJobController::class, 'trimiteMementoAlerte']);
Route::get('/cron-jobs/memento-facturi/{key}', [CronJobController::class, 'trimiteMementoFacturi']);

Route::get('cerere-status-comanda/{modTransmitere}/{cheie_unica}', [StatusComandaActualizatDeTransportatorController::class, 'cerereStatusComanda']);
Route::post('salvare-status-comanda/{modTransmitere}/{cheie_unica}', [StatusComandaActualizatDeTransportatorController::class, 'salvareStatusComanda']);
Route::get('afisare-status-comanda/{modTransmitere}/{cheie_unica}', [StatusComandaActualizatDeTransportatorController::class, 'afisareStatusComanda']);

Route::get('/axios/trimitere-cod-autentificare-prin-email', [AxiosController::class, 'trimitereCodAutentificarePrinEmail']);

// For transporters to upload their documents
Route::get('/comanda-incarcare-documente-de-catre-transportator/{cheie_unica}', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'afisareDocumenteIncarcateDejaSiFormular']);
Route::post('/comanda-incarcare-documente-de-catre-transportator/{cheie_unica}', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'salvareDocumente']);
Route::get('/comanda-incarcare-documente-de-catre-transportator/{cheie_unica}/deschide/{numeFisier?}', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'fisierDeschide']);
Route::delete('/comanda-incarcare-documente-de-catre-transportator/{cheie_unica}/sterge/{numeFisier?}', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'fisierSterge']);
Route::post('/comanda-incarcare-documente-de-catre-transportator/{cheie_unica}/trimitere-email-transportator-catre-maseco-documente-incarcate', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'trimitereEmailTransportatorCatreMasecoDocumenteIncarcate']);
Route::get('/comanda-incarcare-documente-de-catre-transportator/{cheie_unica}/mesaj-succes-trimitere-notificare', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'trimitereEmailTransportatorCatreMasecoDocumenteIncarcateMesajSucces']);


Route::redirect('/', '/acasa');

Route::group(['middleware' => 'auth'], function () {
    Route::view('acasa', 'acasa');


    Route::view('file-manager', 'fileManager');
    Route::get('/file-manager-personalizat/{cale?}', [FileManagerPersonalizatController::class, 'afisareDirectoareSiFisiere'])->where('cale', '.*');
    Route::post('/file-manager-personalizat-director/creaza', [FileManagerPersonalizatController::class, 'directorCreaza']);
    // Route::post('/file-manager-personalizat-director/modifica-cale-nume', [FileManagerPersonalizatController::class, 'directorModificaCaleNume']);
    Route::delete('/file-manager-personalizat-director/sterge/{cale?}', [FileManagerPersonalizatController::class, 'directorSterge'])->where('cale', '.*');
    Route::post('/file-manager-personalizat-fisiere/adauga', [FileManagerPersonalizatController::class, 'fisiereAdauga']);
    Route::get('/file-manager-personalizat-fisier/deschide/{cale?}', [FileManagerPersonalizatController::class, 'fisierDeschide'])->where('cale', '.*');
    Route::delete('/file-manager-personalizat-fisier/sterge/{cale?}', [FileManagerPersonalizatController::class, 'fisierSterge'])->where('cale', '.*');
    Route::post('/file-manager-personalizat-resursa/modifica-cale-nume', [FileManagerPersonalizatController::class, 'ModificaCaleNume']);


    Route::resource('/firme/{tipPartener}', FirmaController::class)->parameters(['{tipPartener}' => 'firma']);
    Route::get('/firme/{tipPartener}/{firma}/contract/{view_type}', [FirmaController::class, 'contractExportPDF']);
    Route::get('/firme/{tipPartener}/{firma}/contract-cca/trimite-catre-transportator', [FirmaController::class, 'contractCcaTrimiteCatreTransportator']);

    Route::resource('/camioane', CamionController::class)->parameters(['camioane' => 'camion']);
    Route::resource('/locuri-operare', LocOperareController::class)->parameters(['locuri-operare' => 'locOperare']);


    Route::view('comenzi/totaluri-luna-curenta', 'comenzi.export.totaluriLunaCurenta'); // Route to show detailed info about how is calculated the total sum, from the first page, containing the comands from this month

    // Maseco intern documents
    Route::get('/comenzi/{comanda}/fisiere-interne', [ComandaFisierInternController::class, 'afisareFisiereIncarcateDejaSiFormular']);
    Route::post('/comenzi/{comanda}/fisiere-interne', [ComandaFisierInternController::class, 'salvareDocumente']);
    Route::get('/comenzi/{comanda}/fisiere-interne/deschide/{numeFisier?}', [ComandaFisierInternController::class, 'fisierDeschide']);
    Route::delete('/comenzi/{comanda}/fisiere-interne/sterge/{numeFisier?}', [ComandaFisierInternController::class, 'fisierSterge']);

    Route::resource('/comenzi', ComandaController::class)->parameters(['comenzi' => 'comanda']);
    Route::get('/comenzi/{comanda}/trimite-catre-transportator', [ComandaController::class, 'comandaTrimiteCatreTransportator']);
    Route::any('/comenzi/{comanda}/adauga-resursa/{resursa}/{tip?}/{ordine?}', [ComandaController::class, 'comandaAdaugaResursa']);
    Route::get('/comenzi/{comanda}/export-excel', [ComandaController::class, 'comandaExportExcel']);
    Route::get('/comenzi/{comanda}/{view_type}', [ComandaController::class, 'comandaExportPDF']);
    Route::get('/comenzi/{comanda}/stare/{stare}', [ComandaController::class, 'stare']);


    Route::resource('mesaje-trimise-sms', MesajTrimisSmsController::class,  ['parameters' => ['mesaje-trimise-sms' => 'mesaj_trimis_sms']]);
    Route::resource('/mementouri/{tip}/mementouri', MementoController::class)->parameters(['mementouri' => 'memento']);

    // Extras date cu Axios
    Route::get('/axios/locuri-operare', [AxiosController::class, 'locuriOperare']);
    Route::get('/axios/clienti', [AxiosController::class, 'clienti']);
    Route::get('/axios/statusuri', [AxiosController::class, 'statusuri']);

    Route::resource('/fisiere/{categorieFisier}', FisierController::class)->parameters(['{categorieFisier}' => 'fisier']);
    Route::get('/fisiere/{categorieFisier}/{fisier}/descarca', [FisierController::class, 'descarca']);

    Route::get('/facturi/axios/cauta-client', [FacturaController::class, 'axiosCautaClient']);
    Route::post('/facturi/axios/cauta-comanda', [FacturaController::class, 'axiosCautaComanda']);
    Route::resource('/facturi', FacturaController::class)->parameters(['facturi' => 'factura']);
    Route::any('/facturi/{factura}/storneaza', [FacturaController::class, 'storneaza']);
    Route::get('/facturi/{factura}/export/{view_type}', [FacturaController::class, 'exportPdf']);

    // Doar pentru mementouri facturi, create din pagina de comenzi, eventual pana este gata modulul de facturare, daca se va mai face
    // Removed on 14.01.2025 - to set more clients to a command, so more invoices, not just one
    // Route::get('/facturi-memento/deschide/{comanda}', [FacturaController::class, 'createOrUpdateMementoFactura']);
    // Route::post('/facturi-memento/salveaza/{factura}', [FacturaController::class, 'storeOrUpdateMementoFactura']);
    // Added on 14.01.2025 - to set more clients to a command, so more invoices, not just one
    Route::get('/facturi-memento/deschide/comanda/{comanda}', [FacturaController::class, 'createOrUpdateMementoFactura']);
    Route::post('/facturi-memento/salveaza/comanda/{comanda}', [FacturaController::class, 'storeOrUpdateMementoFactura']);

    Route::get('/rapoarte/incasari-utilizatori', [RaportController::class, 'incasariUtilizatori']);
    Route::get('/rapoarte/documente-transportatori', [RaportController::class, 'documenteTransportatori']);

    Route::group(['middleware' => 'role:1'], function () {
        Route::resource('/utilizatori', UserController::class)->parameters(['utilizatori' => 'user']);
    });

    // For transporters to upload their documents
    Route::get('/comanda-documente-transportator/{cheie_unica}', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'afisareDocumenteIncarcateDejaSiFormular']);
    Route::post('/comanda-documente-transportator/{cheie_unica}', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'salvareDocumente']);
    Route::get('/comanda-documente-transportator/{cheie_unica}/valideaza-invalideaza/{numeFisier}', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'validareInvalidareDocumente']);
    Route::get('/comanda-documente-transportator/{cheie_unica}/blocare-deblocare-incarcare-documente', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'blocareDeblocareIncarcareDocumente']);
    Route::post('/comanda-documente-transportator/{cheie_unica}/trimitere-email-catre-transportator-privind-documente-incarcate', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'trimitereEmailCatreTransportatorPrivindDocumenteIncarcate']);

    Route::get('/statii-peco', [StatiePecoController::class, 'index']);
    Route::post('/statii-peco/excel-import', [StatiePecoController::class, 'excelImport']);

    Route::get('/intermedieri/export-html', [IntermediereController::class, 'exportHtml']);
    Route::get('/intermedieri/schimbaPredatLaContabilitate/{comanda}', [IntermediereController::class, 'schimbaPredatLaContabilitate']);
    Route::resource('/intermedieri', IntermediereController::class)->parameters(['intermedieri' => 'intermediere']);

    Route::resource('/flota-statusuri', FlotaStatusController::class)->parameters(['flota-statusuri' => 'flotaStatus']);
    Route::resource('/flota-statusuri-informatii', FlotaStatusInformatieController::class)->parameters(['flota-statusuri-informatii' => 'flotaStatusInformatie']);

    Route::resource('/documente-word', DocumentWordController::class)->parameters(['documente-word' => 'documentWord']);

    // Clear application cache:
    Route::get('/clear-all', function() {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        return 'All caches cleared';
    });

    Route::view('teste', 'teste');





    Route::get('send-whatsapp-curl', function() {
        // $params=array(
        //     'token' => 'YourToken',
        //     'to' => '40749262658',
        //     'body' => 'WhatsApp API on alvochat.com works good',
        //     'priority' => '',
        //     'preview_url' => '',
        //     'message_id' => ''
        // );
        // $curl = curl_init();
        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => "https://graph.facebook.com/v16.0/107361422335397/messages",
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => "",
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 30,
        //     CURLOPT_SSL_VERIFYHOST => 0,
        //     CURLOPT_SSL_VERIFYPEER => 0,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => "POST",
        //     CURLOPT_POSTFIELDS => http_build_query($params),
        //     CURLOPT_HTTPHEADER => array(
        //     "content-type: application/json"
        // ),
        // CURLOPT_HTTPHEADER => array(
        //     'Authorization: EAACaR6ZCEVDIBAHgP5T5s0ZA6O5iHiDF77Bg1vl1MWxbFyaxURn0ERKdDPNqsZA3bvr04TbF3SHiBXtjQDoVwdWmfrcQEvX0sd8D7OuDIBO5GP8eXHO5DHhcZBkN5zswbKa33ng6TGdsTqcTYVBy7aXdEbzXedqJd5gTDdYNFVwme1SnwgVsHoEYRlPuANDZA3Dw5a5Er4gZDZD' //change TOKEN to your actual token
        // ),
        // ));

        // $response = curl_exec($curl);
        // $err = curl_error($curl);

        // curl_close($curl);

        // if ($err) {
        // echo "cURL Error #:" . $err;
        // } else {
        // echo $response;
        // }


        $number = '40749262658'; //you can use POST, I tried GET for testing
     $template = array(
       'name'=>'hello_world', //your your own or any default template. The names and samples are listed under message templates
       'language'=>array('code'=>'en_us') //you can use yours
       );

     $endpoint = 'https://graph.facebook.com/v16.0/107361422335397/messages';
     $params = array('messaging_product'=>'whatsapp', 'to'=>$number, 'type'=>'template',
        'from'=>'+1 555 072 3489',
        'access_token'=>'EAACaR6ZCEVDIBADEfYVhPFZCUbSBM4BZAQ135XqvXvKPrgdUFL2MNUXqZBfAk3FiZAyZAZAFhCXS4vVp44pxb6JtgaJpOMtAT9w2jXVroTOT4DLN4PmXwbxjpzyVs7YOrFoGlQbEGe4czeqZBb7qgaOUuhdHjbLRF59ZAkkE8aPbKbYmaNA3qEWZAGcj36Vcpl1zLbiN37hiI7OgZDZD',
        'template'=>json_encode($template));

       $headers = array('Authorization'=>'Bearer EAACaR6ZCEVDIBAHgP5T5s0ZA6O5iHiDF77Bg1vl1MWxbFyaxURn0ERKdDPNqsZA3bvr04TbF3SHiBXtjQDoVwdWmfrcQEvX0sd8D7OuDIBO5GP8eXHO5DHhcZBkN5zswbKa33ng6TGdsTqcTYVBy7aXdEbzXedqJd5gTDdYNFVwme1SnwgVsHoEYRlPuANDZA3Dw5a5Er4gZDZD','Content-Type'=>'application/json', 'User-Agent'=>'(Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36');
       $url = $endpoint . '?' . http_build_query($params);
  //echo $params.'<br>';
       $ch = curl_init();
       curl_setopt( $ch,CURLOPT_URL, $endpoint);
       curl_setopt( $ch,CURLOPT_POST, true );
       curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
       curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
       curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
       curl_setopt( $ch,CURLOPT_POSTFIELDS, $params);


       $result = curl_exec($ch );
       echo $result; //you can skip this, I did it to check the results
       curl_close( $ch );


// curl -i -X POST `https://graph.facebook.com/v16.0/107361422335397/messages `
//   -H 'Authorization: Bearer EAACaR6ZCEVDIBAHgP5T5s0ZA6O5iHiDF77Bg1vl1MWxbFyaxURn0ERKdDPNqsZA3bvr04TbF3SHiBXtjQDoVwdWmfrcQEvX0sd8D7OuDIBO5GP8eXHO5DHhcZBkN5zswbKa33ng6TGdsTqcTYVBy7aXdEbzXedqJd5gTDdYNFVwme1SnwgVsHoEYRlPuANDZA3Dw5a5Er4gZDZD' `
//   -H 'Content-Type: application/json' `
//   -d '{ \"messaging_product\": \"whatsapp\", \"to\": \"40749262658\", \"type\": \"template\", \"template\": { \"name\": \"hello_world\", \"language\": { \"code\": \"en_US\" } } }'
    });


    Route::get('get-whatsapp-curl', function() {

    });





    Route::get('/co3', [DiverseTesteController::class, 'co3']);
});
