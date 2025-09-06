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
use App\Http\Controllers\FacturaScadentaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RaportController;
use App\Http\Controllers\DiverseTesteController;
use App\Http\Controllers\ComandaFisierInternController;
use App\Http\Controllers\ComandaIncarcareDocumenteDeCatreTransportatorController;
use App\Http\Controllers\StatiePecoController;
use App\Http\Controllers\IntermediereController;
use App\Http\Controllers\FlotaStatusController;
use App\Http\Controllers\FlotaStatusInformatieController;
use App\Http\Controllers\FlotaStatusCController;
use App\Http\Controllers\MasinaValabilitatiController;
use App\Http\Controllers\DocumentWordController;
use App\Http\Controllers\KeyPerformanceIndicatorController;
use App\Http\Controllers\OfertaCursaController;


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
Route::post('/comanda-incarcare-documente-de-catre-transportator/{cheie_unica}/trimitere-email-transportator-catre-maseco-documente-incarcate/{categorieEmail}', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'trimitereEmailTransportatorCatreMasecoDocumenteIncarcate']);
Route::get('/comanda-incarcare-documente-de-catre-transportator/{cheie_unica}/mesaj-succes-trimitere-notificare', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'trimitereEmailTransportatorCatreMasecoDocumenteIncarcateMesajSucces']);

// Those routes are unprotected for Ionut to be able to check on them when he loads the courses through AI
Route::get('oferte-curse/{oferta}', [OfertaCursaController::class, 'show'])->name('oferte-curse.show')
     ->whereNumber('oferta'); // Restrict the {oferta} route parameter to numeric IDs, preventing wildcard clashes with other routes, like 'create'
Route::get('oferte-curse', [OfertaCursaController::class, 'index'])->name('oferte-curse.index');

Route::redirect('/', '/acasa');


Route::get('/debug/storage-path', fn () => storage_path('logs'));


Route::group(['middleware' => 'auth'], function () {
    Route::view('acasa', 'acasa');
    Route::view('various-tests', 'variousTests');

    Route::get('/file-manager-personalizat/{cale?}', [FileManagerPersonalizatController::class, 'afisareDirectoareSiFisiere'])->where('cale', '.*');
    Route::post('/file-manager-personalizat-director/creaza', [FileManagerPersonalizatController::class, 'directorCreaza']);
    // Route::post('/file-manager-personalizat-director/modifica-cale-nume', [FileManagerPersonalizatController::class, 'directorModificaCaleNume']);
    Route::delete('/file-manager-personalizat-director/sterge/{cale?}', [FileManagerPersonalizatController::class, 'directorSterge'])->where('cale', '.*');
    Route::post('/file-manager-personalizat-fisiere/adauga', [FileManagerPersonalizatController::class, 'fisiereAdauga']);
    Route::get('/file-manager-personalizat-fisier/deschide/{cale?}', [FileManagerPersonalizatController::class, 'fisierDeschide'])->where('cale', '.*');
    Route::delete('/file-manager-personalizat-fisier/sterge/{cale?}', [FileManagerPersonalizatController::class, 'fisierSterge'])->where('cale', '.*');
    Route::post('/file-manager-personalizat-resursa/modifica-cale-nume', [FileManagerPersonalizatController::class, 'ModificaCaleNume']);
    Route::post('/file-manager-personalizat-fisier/copy', [FileManagerPersonalizatController::class, 'copyFile']);
    Route::post('/file-manager-personalizat-fisier/move', [FileManagerPersonalizatController::class, 'moveFile']);
    Route::post('/file-manager-personalizat-director/copy', [FileManagerPersonalizatController::class, 'copyDirectory']);
    Route::post('/file-manager-personalizat-director/move', [FileManagerPersonalizatController::class, 'moveDirectory']);


    Route::resource('/firme/{tipPartener}', FirmaController::class)->parameters(['{tipPartener}' => 'firma']);
    Route::get('/firme/{tipPartener}/{firma}/contract/{view_type}', [FirmaController::class, 'contractExportPDF']);
    Route::get('/firme/{tipPartener}/{firma}/contract-cca/trimite-catre-transportator', [FirmaController::class, 'contractCcaTrimiteCatreTransportator']);

    Route::resource('/camioane', CamionController::class)->parameters(['camioane' => 'camion']);
    Route::resource('/locuri-operare', LocOperareController::class)->parameters(['locuri-operare' => 'locOperare']);


    Route::view('comenzi/totaluri-luna-curenta', 'comenzi.export.totaluriLunaCurenta'); // Route to show detailed info about how is calculated the total sum, from the first page, containing the commands from this month
    Route::get('/comenzi/observatii-interne', [ComandaController::class, 'indexObservatiiInterne']); // Page where the user has access to all intern observations, not just to the last 20 that are displayed on the home page
    Route::get('/comenzi/activitate-recenta', [ComandaController::class, 'indexActivitateRecenta']); // Page where the user has access to all "activitate recenta", not just to the last 20 that are displayed on the home page

    // Maseco intern documents
    Route::get('/comenzi/{comanda}/fisiere-interne', [ComandaFisierInternController::class, 'afisareFisiereIncarcateDejaSiFormular']);
    Route::post('/comenzi/{comanda}/fisiere-interne', [ComandaFisierInternController::class, 'salvareDocumente']);
    Route::get('/comenzi/{comanda}/fisiere-interne/deschide/{numeFisier?}', [ComandaFisierInternController::class, 'fisierDeschide']);
    Route::delete('/comenzi/{comanda}/fisiere-interne/sterge/{numeFisier?}', [ComandaFisierInternController::class, 'fisierSterge']);

    Route::resource('/comenzi', ComandaController::class)->parameters(['comenzi' => 'comanda']);
    Route::get('/comenzi/{comanda}/trimite-catre-transportator', [ComandaController::class, 'comandaTrimiteCatreTransportator']);
    Route::get('/comenzi/{comanda}/trimite-debit-note-catre-transportator', [ComandaController::class, 'DebitNoteComandaTrimiteCatreTransportator']);
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

    Route::get('/facturi-scadente', [FacturaScadentaController::class, 'index']);


    Route::get('/rapoarte/incasari-utilizatori', [RaportController::class, 'incasariUtilizatori']);
    Route::get('/rapoarte/documente-transportatori', [RaportController::class, 'documenteTransportatori']);

    Route::group(['middleware' => 'role:1'], function () {
        Route::resource('/utilizatori', UserController::class)->parameters(['utilizatori' => 'user']);
    });

    // For transporters to upload their documents
    Route::get('/comanda-documente-transportator/{cheie_unica}', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'afisareDocumenteIncarcateDejaSiFormular']);
    Route::post('/comanda-informatii-documente-transportator/{cheie_unica}', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'salvareInformatiiDocumente']);
    Route::post('/comanda-documente-transportator/{cheie_unica}', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'salvareDocumente']);
    Route::get('/comanda-documente-transportator/{cheie_unica}/valideaza-invalideaza/{numeFisier}', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'validareInvalidareDocumente']);
    Route::get('/comanda-documente-transportator/{cheie_unica}/blocare-deblocare-incarcare-documente', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'blocareDeblocareIncarcareDocumente']);
    Route::post('/comanda-documente-transportator/{cheie_unica}/trimitere-email-catre-transportator-privind-documente-incarcate', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'trimitereEmailCatreTransportatorPrivindDocumenteIncarcate']);

    Route::get('/statii-peco', [StatiePecoController::class, 'index']);
    Route::post('/statii-peco/excel-import', [StatiePecoController::class, 'excelImport']);

    // Route::get('/intermedieri/export-html', [IntermediereController::class, 'exportHtml']); // to delete 01.02.2025
    Route::post('/intermedieri/schimbaPredatLaContabilitate/{comanda}', [IntermediereController::class, 'schimbaPredatLaContabilitate']);
    Route::resource('/intermedieri', IntermediereController::class)->parameters(['intermedieri' => 'intermediere']);

    Route::resource('/flota-statusuri', FlotaStatusController::class)->parameters(['flota-statusuri' => 'flotaStatus']);
    Route::resource('/flota-statusuri-informatii', FlotaStatusInformatieController::class)->parameters(['flota-statusuri-informatii' => 'flotaStatusInformatie']);

    Route::resource('flota-statusuri-c', FlotaStatusCController::class)->parameters(['flota-statusuri-c' => 'flotaStatusC']);

    Route::resource('masini-valabilitati', MasinaValabilitatiController::class)->parameters(['masini-valabilitati' => 'masinaValabilitati']);

    Route::get('/documente-word/{documentWord}/unlock', [DocumentWordController::class, 'unlock'])->name('documentWord.unlock');
    Route::resource('/documente-word', DocumentWordController::class)->parameters(['documente-word' => 'documentWord']);


    Route::post('/key-performance-indicators/update-observatii', [KeyPerformanceIndicatorController::class, 'updateObservatii'])->name('kpis.updateObservatii');
    Route::resource('key-performance-indicators', KeyPerformanceIndicatorController::class)->parameters(['key-performance-indicators' => 'keyPerformanceIndicators']);


    /**
     * Oferte curse routes
     */
    Route::get('oferte-curse/citire-automata-emailuri', [OfertaCursaController::class, 'citireAutomataEmailuri']);
    Route::get('/oferte-curse/index-axios', [\App\Http\Controllers\OfertaCursaController::class, 'indexAxios'])->name('oferte-curse.index.axios');
    Route::resource('oferte-curse', OfertaCursaController::class)
         ->parameters(['oferte-curse' => 'oferta'])
         ->except(['index', 'show']); // Those routes are unprotected for Ionut from Validsoftware to be able to check on them when he loads the courses through AI

    // Returns ONLY rows changed since a timestamp (tiny response)
    Route::get('/axios/oferte-changes', [\App\Http\Controllers\OfertaCursaController::class, 'changes'])
        ->name('oferte.changes');



    // Clear application cache:
    Route::get('/clear-all', function() {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        return 'All caches cleared';
    });



    // 2 routes to easily impersonate other users
    // Route::get('/impersonate/{id}', function ($id) {
    //     // Only allow admins or authorized users to impersonate others.
    //     // if (!auth()->user()->isAdmin()) {
    //     //     abort(403, 'Unauthorized');
    //     // }

    //     $user = App\Models\User::findOrFail($id);
    //     // Optionally, save the admin's ID in the session to allow reverting back
    //     session()->put('impersonated_by', auth()->id());

    //     Auth::login($user);

    //     return redirect('/'); // Redirect to a safe route.
    // });

    // Route::get('/stop-impersonation', function () {
    //     $adminId = session()->pull('impersonated_by');

    //     if ($adminId) {
    //         $admin = App\Models\User::findOrFail($adminId);
    //         Auth::login($admin);
    //     }

    //     return redirect('/');
    // });

});
