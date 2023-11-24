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


Route::redirect('/', '/acasa');


Route::group(['middleware' => 'auth'], function () {
    Route::view('acasa', 'acasa');

    Route::view('file-manager', 'fileManager');
    Route::get('/file-manager-personalizat', [FileManagerPersonalizatController::class, 'afisareDirectoareSiFisiere']);

    Route::resource('/firme/{tipPartener}', FirmaController::class)->parameters(['{tipPartener}' => 'firma']);
    Route::get('/firme/{tipPartener}/{firma}/contract/{view_type}', [FirmaController::class, 'contractExportPDF']);
    Route::get('/firme/{tipPartener}/{firma}/contract-cca/trimite-catre-transportator', [FirmaController::class, 'contractCcaTrimiteCatreTransportator']);

    Route::resource('/camioane', CamionController::class)->parameters(['camioane' => 'camion']);
    Route::resource('/locuri-operare', LocOperareController::class)->parameters(['locuri-operare' => 'locOperare']);

    Route::resource('/comenzi', ComandaController::class)->parameters(['comenzi' => 'comanda']);
    Route::get('/comenzi/{comanda}/trimite-catre-transportator', [ComandaController::class, 'comandaTrimiteCatreTransportator']);
    Route::any('/comenzi/{comanda}/adauga-resursa/{resursa}/{tip?}/{ordine?}', [ComandaController::class, 'comandaAdaugaResursa']);
    Route::get('/comenzi/{comanda}/export-excel', [ComandaController::class, 'comandaExportExcel']);
    Route::get('/comenzi/{comanda}/{view_type}', [ComandaController::class, 'comandaExportPDF']);
    Route::get('/comenzi/{comanda}/stare/{stare}', [ComandaController::class, 'stare']);

    Route::resource('mesaje-trimise-sms', MesajTrimisSmsController::class,  ['parameters' => ['mesaje-trimise-sms' => 'mesaj_trimis_sms']]);
    Route::resource('/mementouri', MementoController::class)->parameters(['mementouri' => 'memento']);
    Route::resource('/facturi', FacturaController::class)->parameters(['facturi' => 'factura']);

    // Extras date cu Axios
    Route::get('/axios/locuri-operare', [AxiosController::class, 'locuriOperare']);
    Route::get('/axios/statusuri', [AxiosController::class, 'statusuri']);

    Route::resource('/fisiere/{categorieFisier}', FisierController::class)->parameters(['{categorieFisier}' => 'fisier']);
    Route::get('/fisiere/{categorieFisier}/{fisier}/descarca', [FisierController::class, 'descarca']);

    // Clear application cache:
    Route::get('/clear-cache', function() {
        Artisan::call('cache:clear');
        return 'Application cache has been cleared';
    });
    Route::get('/config-cache', function() {
        Artisan::call('config:cache');
        return 'Configuration cached successfully.';
    });





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



});
