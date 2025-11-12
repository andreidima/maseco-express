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
use App\Http\Controllers\Masini\MasinaFisierGeneralController;
use App\Http\Controllers\Masini\MasiniDocumentController;
use App\Http\Controllers\Masini\MasiniDocumentFisierController;
use App\Http\Controllers\Masini\MasiniMementoController;
use App\Http\Controllers\DocumentWordController;
use App\Http\Controllers\KeyPerformanceIndicatorController;
use App\Http\Controllers\OfertaCursaController;
use App\Http\Controllers\FacturiFurnizori\FacturaFurnizorController;
use App\Http\Controllers\FacturiFurnizori\FacturaFurnizorFisierController;
use App\Http\Controllers\FacturiFurnizori\PlataCalupController;
use App\Http\Controllers\Service\GestiunePieseController;
use App\Http\Controllers\Service\ServiceMasiniController;
use App\Http\Controllers\Service\ServiceSheetController;
use App\Http\Controllers\Tech\ImpersonationController;
use App\Http\Controllers\Tech\CronJobLogController;
use App\Http\Controllers\Tech\MigrationCenterController;
use App\Http\Controllers\Tech\SeederCenterController;
use App\Http\Controllers\SoferDashboardController;
use App\Http\Controllers\SoferValabilitateCursaController;
use App\Http\Controllers\ValabilitateController;
use App\Http\Controllers\ValabilitateCursaController;


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
Route::get('/comanda-incarcare-documente-de-catre-transportator/{cheie_unica}/descarca/{numeFisier?}', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'fisierDownload']);
Route::delete('/comanda-incarcare-documente-de-catre-transportator/{cheie_unica}/sterge/{numeFisier?}', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'fisierSterge']);
Route::post('/comanda-incarcare-documente-de-catre-transportator/{cheie_unica}/trimitere-email-transportator-catre-maseco-documente-incarcate/{categorieEmail}', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'trimitereEmailTransportatorCatreMasecoDocumenteIncarcate']);
Route::get('/comanda-incarcare-documente-de-catre-transportator/{cheie_unica}/mesaj-succes-trimitere-notificare', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'trimitereEmailTransportatorCatreMasecoDocumenteIncarcateMesajSucces']);

// Those routes are unprotected for Ionut to be able to check on them when he loads the courses through AI
Route::get('oferte-curse/{oferta}', [OfertaCursaController::class, 'show'])->name('oferte-curse.show')
     ->whereNumber('oferta'); // Restrict the {oferta} route parameter to numeric IDs, preventing wildcard clashes with other routes, like 'create'
Route::get('oferte-curse', [OfertaCursaController::class, 'index'])->name('oferte-curse.index');

Route::redirect('/', '/acasa');

Route::middleware(['auth'])->group(function () {
    Route::middleware('role:sofer')->group(function () {
        Route::get('sofer/dashboard', SoferDashboardController::class)->name('sofer.dashboard');
        Route::get('sofer/valabilitati/{valabilitate}', [SoferValabilitateCursaController::class, 'show'])
            ->name('sofer.valabilitati.show');
        Route::post('sofer/valabilitati/{valabilitate}/curse', [SoferValabilitateCursaController::class, 'store'])
            ->name('sofer.valabilitati.curse.store');
        Route::put('sofer/valabilitati/{valabilitate}/curse/{cursa}', [SoferValabilitateCursaController::class, 'update'])
            ->name('sofer.valabilitati.curse.update');
        Route::delete('sofer/valabilitati/{valabilitate}/curse/{cursa}', [SoferValabilitateCursaController::class, 'destroy'])
            ->name('sofer.valabilitati.curse.destroy');
    });

    Route::middleware('permission:dashboard')->group(function () {
        Route::view('acasa', 'acasa')->name('dashboard');
        Route::view('various-tests', 'variousTests');
        Route::view('ajutor-intern/gestionare-utilizatori', 'useri.help.permissionsMatrix')
            ->name('help.user-management');
    });

    Route::prefix('tech')
        ->name('tech.')
        ->middleware('permission:tech-tools')
        ->group(function () {
            Route::middleware('can:access-tech-impersonation')->group(function () {
                Route::get('impersonation', [ImpersonationController::class, 'index'])->name('impersonation.index');
                Route::post('impersonation', [ImpersonationController::class, 'store'])->name('impersonation.start');
            });

            Route::middleware('role:super-admin')->group(function () {
                Route::get('migrations', [MigrationCenterController::class, 'index'])->name('migrations.index');
                Route::post('migrations/preview', [MigrationCenterController::class, 'preview'])->name('migrations.preview');
                Route::post('migrations/run', [MigrationCenterController::class, 'run'])->name('migrations.run');
                Route::get('seeders', [SeederCenterController::class, 'index'])->name('seeders.index');
                Route::post('seeders/run', [SeederCenterController::class, 'run'])->name('seeders.run');
                Route::get('cron-logs', [CronJobLogController::class, 'index'])->name('cron-logs.index');
                Route::get('cron-logs/{cronJobLog}', [CronJobLogController::class, 'show'])->name('cron-logs.show');
            });
        });

    Route::post('impersonation/stop', [ImpersonationController::class, 'destroy'])
        ->middleware('can-stop-impersonation')
        ->name('impersonation.stop');

    Route::middleware('permission:documente')->group(function () {
        Route::get('/file-manager-personalizat/{cale?}', [FileManagerPersonalizatController::class, 'afisareDirectoareSiFisiere'])->where('cale', '.*');
        Route::get('/file-manager-personalizat-fisier/deschide/{cale?}', [FileManagerPersonalizatController::class, 'fisierDeschide'])->where('cale', '.*');
        Route::get('/file-manager-personalizat-fisier/descarca/{cale?}', [FileManagerPersonalizatController::class, 'fisierDownload'])->where('cale', '.*');

        Route::middleware('permission:documente-manage')->group(function () {
            Route::post('/file-manager-personalizat-director/creaza', [FileManagerPersonalizatController::class, 'directorCreaza']);
            // Route::post('/file-manager-personalizat-director/modifica-cale-nume', [FileManagerPersonalizatController::class, 'directorModificaCaleNume']);
            Route::delete('/file-manager-personalizat-director/sterge/{cale?}', [FileManagerPersonalizatController::class, 'directorSterge'])->where('cale', '.*');
            Route::post('/file-manager-personalizat-fisiere/adauga', [FileManagerPersonalizatController::class, 'fisiereAdauga']);
            Route::delete('/file-manager-personalizat-fisier/sterge/{cale?}', [FileManagerPersonalizatController::class, 'fisierSterge'])->where('cale', '.*');
            Route::post('/file-manager-personalizat-resursa/modifica-cale-nume', [FileManagerPersonalizatController::class, 'ModificaCaleNume']);
            Route::post('/file-manager-personalizat-fisier/copy', [FileManagerPersonalizatController::class, 'copyFile']);
            Route::post('/file-manager-personalizat-fisier/move', [FileManagerPersonalizatController::class, 'moveFile']);
            Route::post('/file-manager-personalizat-director/copy', [FileManagerPersonalizatController::class, 'copyDirectory']);
            Route::post('/file-manager-personalizat-director/move', [FileManagerPersonalizatController::class, 'moveDirectory']);
        });

        // For transporters to upload their documents
        Route::get('/comanda-documente-transportator/{cheie_unica}', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'afisareDocumenteIncarcateDejaSiFormular']);
        Route::post('/comanda-informatii-documente-transportator/{cheie_unica}', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'salvareInformatiiDocumente']);
        Route::post('/comanda-documente-transportator/{cheie_unica}', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'salvareDocumente']);
        Route::get('/comanda-documente-transportator/{cheie_unica}/valideaza-invalideaza/{numeFisier}', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'validareInvalidareDocumente']);
        Route::get('/comanda-documente-transportator/{cheie_unica}/blocare-deblocare-incarcare-documente', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'blocareDeblocareIncarcareDocumente']);
        Route::post('/comanda-documente-transportator/{cheie_unica}/trimitere-email-catre-transportator-privind-documente-incarcate', [ComandaIncarcareDocumenteDeCatreTransportatorController::class, 'trimitereEmailCatreTransportatorPrivindDocumenteIncarcate']);

        Route::resource('/fisiere/{categorieFisier}', FisierController::class)->parameters(['{categorieFisier}' => 'fisier']);
        Route::get('/fisiere/{categorieFisier}/{fisier}/descarca', [FisierController::class, 'descarca']);

    });

    Route::middleware('permission:documente-word')->group(function () {
        Route::post('/documente-word/images', [DocumentWordController::class, 'uploadImage'])
            ->name('documente-word.images');
        Route::get('/documente-word/images/{path}', [DocumentWordController::class, 'showImage'])
            ->where('path', '.*')
            ->name('documente-word.images.show');

        Route::resource('/documente-word', DocumentWordController::class)
            ->only(['index', 'show', 'create', 'store', 'edit', 'update'])
            ->parameters(['documente-word' => 'documentWord']);
    });

    Route::middleware('permission:documente-word-manage')->group(function () {
        Route::get('/documente-word/{documentWord}/unlock', [DocumentWordController::class, 'unlock'])->name('documentWord.unlock');

        Route::resource('/documente-word', DocumentWordController::class)
            ->only(['destroy'])
            ->parameters(['documente-word' => 'documentWord']);
    });

    Route::middleware('permission:firme')->group(function () {
        Route::resource('/firme/{tipPartener}', FirmaController::class)->parameters(['{tipPartener}' => 'firma']);
        Route::get('/firme/{tipPartener}/{firma}/contract/{view_type}', [FirmaController::class, 'contractExportPDF']);
        Route::get('/firme/{tipPartener}/{firma}/contract-cca/trimite-catre-transportator', [FirmaController::class, 'contractCcaTrimiteCatreTransportator']);
        Route::get('/axios/clienti', [AxiosController::class, 'clienti']);
    });

    Route::middleware('permission:camioane')->group(function () {
        Route::resource('/camioane', CamionController::class)->parameters(['camioane' => 'camion']);
    });

    Route::middleware('permission:locuri-operare')->group(function () {
        Route::resource('/locuri-operare', LocOperareController::class)->parameters(['locuri-operare' => 'locOperare']);
        Route::get('/axios/locuri-operare', [AxiosController::class, 'locuriOperare']);
    });

    Route::middleware('permission:comenzi')->group(function () {
        Route::view('comenzi/totaluri-luna-curenta', 'comenzi.export.totaluriLunaCurenta'); // Route to show detailed info about how is calculated the total sum, from the first page, containing the commands from this month
        Route::get('/comenzi/observatii-interne', [ComandaController::class, 'indexObservatiiInterne']); // Page where the user has access to all intern observations, not just to the last 20 that are displayed on the home page
        Route::get('/comenzi/activitate-recenta', [ComandaController::class, 'indexActivitateRecenta']); // Page where the user has access to all "activitate recenta", not just to the last 20 that are displayed on the home page

        // Maseco intern documents
        Route::get('/comenzi/{comanda}/fisiere-interne', [ComandaFisierInternController::class, 'afisareFisiereIncarcateDejaSiFormular']);
        Route::post('/comenzi/{comanda}/fisiere-interne', [ComandaFisierInternController::class, 'salvareDocumente']);
        Route::get('/comenzi/{comanda}/fisiere-interne/deschide/{numeFisier?}', [ComandaFisierInternController::class, 'fisierDeschide']);
        Route::get('/comenzi/{comanda}/fisiere-interne/descarca/{numeFisier?}', [ComandaFisierInternController::class, 'fisierDownload']);
        Route::delete('/comenzi/{comanda}/fisiere-interne/sterge/{numeFisier?}', [ComandaFisierInternController::class, 'fisierSterge']);

        Route::resource('/comenzi', ComandaController::class)->parameters(['comenzi' => 'comanda']);
        Route::get('/comenzi/{comanda}/trimite-catre-transportator', [ComandaController::class, 'comandaTrimiteCatreTransportator']);
        Route::get('/comenzi/{comanda}/trimite-debit-note-catre-transportator', [ComandaController::class, 'DebitNoteComandaTrimiteCatreTransportator']);
        Route::any('/comenzi/{comanda}/adauga-resursa/{resursa}/{tip?}/{ordine?}', [ComandaController::class, 'comandaAdaugaResursa']);
        Route::get('/comenzi/{comanda}/export-excel', [ComandaController::class, 'comandaExportExcel']);
        Route::get('/comenzi/{comanda}/{view_type}', [ComandaController::class, 'comandaExportPDF']);
        Route::get('/comenzi/{comanda}/stare/{stare}', [ComandaController::class, 'stare']);

        Route::get('/axios/statusuri', [AxiosController::class, 'statusuri']);

        Route::post('/intermedieri/schimbaPredatLaContabilitate/{comanda}', [IntermediereController::class, 'schimbaPredatLaContabilitate']);
        Route::resource('/intermedieri', IntermediereController::class)->parameters(['intermedieri' => 'intermediere']);

        Route::resource('/flota-statusuri', FlotaStatusController::class)->parameters(['flota-statusuri' => 'flotaStatus']);
        Route::resource('/flota-statusuri-informatii', FlotaStatusInformatieController::class)->parameters(['flota-statusuri-informatii' => 'flotaStatusInformatie']);

        Route::resource('flota-statusuri-c', FlotaStatusCController::class)->parameters(['flota-statusuri-c' => 'flotaStatusC']);

        Route::get('oferte-curse/citire-automata-emailuri', [OfertaCursaController::class, 'citireAutomataEmailuri']);
        Route::get('/oferte-curse/index-axios', [\App\Http\Controllers\OfertaCursaController::class, 'indexAxios'])->name('oferte-curse.index.axios');
        Route::resource('oferte-curse', OfertaCursaController::class)
             ->parameters(['oferte-curse' => 'oferta'])
             ->except(['index', 'show']); // Those routes are unprotected for Ionut from Validsoftware to be able to check on them when he loads the courses through AI

        // Returns ONLY rows changed since a timestamp (tiny response)
        Route::get('/axios/oferte-changes', [\App\Http\Controllers\OfertaCursaController::class, 'changes'])
            ->name('oferte.changes');
    });

    Route::middleware('permission:mesagerie')->group(function () {
        Route::resource('mesaje-trimise-sms', MesajTrimisSmsController::class,  ['parameters' => ['mesaje-trimise-sms' => 'mesaj_trimis_sms']]);
    });

    Route::middleware('permission:mementouri')->group(function () {
        Route::resource('/mementouri/{tip}/mementouri', MementoController::class)->parameters(['mementouri' => 'memento']);
        Route::resource('masini-mementouri', MasiniMementoController::class)
            ->parameters(['masini-mementouri' => 'masini_mementouri']);

        Route::scopeBindings()->group(function () {
            Route::get('masini-mementouri/{masini_mementouri}/documente/{document}', [MasiniDocumentController::class, 'edit'])
                ->name('masini-mementouri.documente.edit');
            Route::patch('masini-mementouri/{masini_mementouri}/documente/{document}', [MasiniDocumentController::class, 'update'])
                ->name('masini-mementouri.documente.update');
            Route::post('masini-mementouri/{masini_mementouri}/documente/{document}/fisiere', [MasiniDocumentFisierController::class, 'store'])
                ->name('masini-mementouri.documente.fisiere.store');
            Route::delete('masini-mementouri/{masini_mementouri}/documente/{document}/fisiere/{fisier}', [MasiniDocumentFisierController::class, 'destroy'])
                ->name('masini-mementouri.documente.fisiere.destroy');
            Route::get('masini-mementouri/{masini_mementouri}/documente/{document}/fisiere/{fisier}', [MasiniDocumentFisierController::class, 'download'])
                ->name('masini-mementouri.documente.fisiere.download');
            Route::get('masini-mementouri/{masini_mementouri}/documente/{document}/fisiere/{fisier}/preview', [MasiniDocumentFisierController::class, 'preview'])
                ->name('masini-mementouri.documente.fisiere.preview');
            Route::get('masini-mementouri/{masini_mementouri}/fisiere-generale', [MasinaFisierGeneralController::class, 'index'])
                ->name('masini-mementouri.fisiere-generale.index');
            Route::post('masini-mementouri/{masini_mementouri}/fisiere-generale', [MasinaFisierGeneralController::class, 'store'])
                ->name('masini-mementouri.fisiere-generale.store');
            Route::delete('masini-mementouri/{masini_mementouri}/fisiere-generale/{fisier}', [MasinaFisierGeneralController::class, 'destroy'])
                ->name('masini-mementouri.fisiere-generale.destroy');
            Route::get('masini-mementouri/{masini_mementouri}/fisiere-generale/{fisier}', [MasinaFisierGeneralController::class, 'download'])
                ->name('masini-mementouri.fisiere-generale.download');
            Route::get('masini-mementouri/{masini_mementouri}/fisiere-generale/{fisier}/preview', [MasinaFisierGeneralController::class, 'preview'])
                ->name('masini-mementouri.fisiere-generale.preview');
        });
    });

    Route::middleware('permission:facturi')->group(function () {
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
    });

    Route::middleware('permission:gestiune-piese')->group(function () {
        Route::resource('gestiune-piese', GestiunePieseController::class)
            ->except(['show'])
            ->parameters(['gestiune-piese' => 'gestiune_piesa']);
    });

    Route::middleware('permission:service-masini')->group(function () {
        Route::get('/service-masini', [ServiceMasiniController::class, 'index'])
            ->name('service-masini.index');
        Route::post('/service-masini', [ServiceMasiniController::class, 'storeMasina'])
            ->name('service-masini.store-masina');
        Route::put('/service-masini/{masina}', [ServiceMasiniController::class, 'updateMasina'])
            ->name('service-masini.update-masina');
        Route::delete('/service-masini/{masina}', [ServiceMasiniController::class, 'destroyMasina'])
            ->name('service-masini.destroy-masina');
        Route::post('/service-masini/{masina}/entries', [ServiceMasiniController::class, 'storeEntry'])
            ->name('service-masini.entries.store');
        Route::put('/service-masini/{masina}/entries/{entry}', [ServiceMasiniController::class, 'updateEntry'])
            ->name('service-masini.entries.update');
        Route::delete('/service-masini/{masina}/entries/{entry}', [ServiceMasiniController::class, 'destroyEntry'])
            ->name('service-masini.entries.destroy');
        Route::get('/service-masini/export/pdf', [ServiceMasiniController::class, 'export'])
            ->name('service-masini.export');
        Route::get('/service-masini/{masina}/foaie-service', [ServiceSheetController::class, 'create'])
            ->name('service-masini.sheet.create');
        Route::post('/service-masini/{masina}/foaie-service', [ServiceSheetController::class, 'store'])
            ->name('service-masini.sheet.store');
        Route::get('/service-masini/{masina}/foaie-service/{sheet}/edit', [ServiceSheetController::class, 'edit'])
            ->name('service-masini.sheet.edit');
        Route::put('/service-masini/{masina}/foaie-service/{sheet}', [ServiceSheetController::class, 'update'])
            ->name('service-masini.sheet.update');
        Route::delete('/service-masini/{masina}/foaie-service/{sheet}', [ServiceSheetController::class, 'destroy'])
            ->name('service-masini.sheet.destroy');
        Route::get('/service-masini/{masina}/foaie-service/{sheet}/pdf', [ServiceSheetController::class, 'download'])
            ->name('service-masini.sheet.download');
    });

    Route::middleware('permission:masini-valabilitati')->group(function () {
        Route::resource('masini-valabilitati', MasinaValabilitatiController::class)
            ->parameters(['masini-valabilitati' => 'masinaValabilitati']);
    });

    Route::middleware(['permission:valabilitati', 'role:super-admin,admin'])->group(function () {
        Route::get('valabilitati/paginate', [ValabilitateController::class, 'paginate'])
            ->name('valabilitati.paginate');

        Route::resource('valabilitati', ValabilitateController::class)
            ->parameters(['valabilitati' => 'valabilitate']);

        Route::scopeBindings()->group(function () {
            Route::get('valabilitati/{valabilitate}/curse/paginate', [ValabilitateCursaController::class, 'paginate'])
                ->name('valabilitati.curse.paginate');
            Route::get('valabilitati/{valabilitate}/curse', [ValabilitateCursaController::class, 'index'])
                ->name('valabilitati.curse.index');
            Route::post('valabilitati/{valabilitate}/curse', [ValabilitateCursaController::class, 'store'])
                ->name('valabilitati.curse.store');
            Route::put('valabilitati/{valabilitate}/curse/{cursa}', [ValabilitateCursaController::class, 'update'])
                ->name('valabilitati.curse.update');
            Route::delete('valabilitati/{valabilitate}/curse/{cursa}', [ValabilitateCursaController::class, 'destroy'])
                ->name('valabilitati.curse.destroy');
        });
    });

    Route::middleware('permission:rapoarte')->group(function () {
        Route::get('/rapoarte/incasari-utilizatori', [RaportController::class, 'incasariUtilizatori']);
        Route::get('/rapoarte/documente-transportatori', [RaportController::class, 'documenteTransportatori']);

        Route::post('/key-performance-indicators/update-observatii', [KeyPerformanceIndicatorController::class, 'updateObservatii'])->name('kpis.updateObservatii');
        Route::resource('key-performance-indicators', KeyPerformanceIndicatorController::class)->parameters(['key-performance-indicators' => 'keyPerformanceIndicators']);
    });

    Route::middleware(['permission:users', 'role:super-admin,admin'])->group(function () {
        Route::resource('/utilizatori', UserController::class)->parameters(['utilizatori' => 'user']);
    });

    Route::middleware('permission:facturi-furnizori')->prefix('facturi-furnizori')
        ->name('facturi-furnizori.')
        ->group(function () {
            Route::get('facturi/sugestii', [FacturaFurnizorController::class, 'sugestii'])->name('facturi.sugestii');
            Route::get('facturi/ultimul-cont-iban', [FacturaFurnizorController::class, 'ultimulContIban'])->name('facturi.ultimul-cont-iban');

            Route::resource('facturi', FacturaFurnizorController::class)
                ->parameters(['facturi' => 'factura']);

            Route::get('facturi/{factura}/fisiere/{fisier}/vizualizeaza', [FacturaFurnizorFisierController::class, 'vizualizeaza'])
                ->name('facturi.fisiere.vizualizeaza');

            Route::get('facturi/{factura}/fisiere/{fisier}/descarca', [FacturaFurnizorFisierController::class, 'descarca'])
                ->name('facturi.fisiere.descarca');

            Route::delete('facturi/{factura}/fisiere/{fisier}', [FacturaFurnizorFisierController::class, 'destroy'])
                ->name('facturi.fisiere.destroy');

            Route::resource('plati-calupuri', PlataCalupController::class)
                ->parameters(['plati-calupuri' => 'plataCalup']);

            Route::post('plati-calupuri/{plataCalup}/atasare-facturi', [PlataCalupController::class, 'ataseazaFacturi'])
                ->name('plati-calupuri.atasare-facturi');

            Route::delete('plati-calupuri/{plataCalup}/facturi/{factura}', [PlataCalupController::class, 'detaseazaFactura'])
                ->name('plati-calupuri.detaseaza-factura');

            Route::delete('plati-calupuri/{plataCalup}/fisiere/{fisier}', [PlataCalupController::class, 'stergeFisier'])
                ->name('plati-calupuri.fisiere.destroy');

            Route::get('plati-calupuri/{plataCalup}/vizualizeaza-fisier/{fisier}', [PlataCalupController::class, 'vizualizeazaFisier'])
                ->name('plati-calupuri.vizualizeaza-fisier');

            Route::get('plati-calupuri/{plataCalup}/descarca-fisier/{fisier?}', [PlataCalupController::class, 'descarcaFisier'])
                ->name('plati-calupuri.descarca-fisier');
        });

    Route::middleware('permission:statii-peco')->group(function () {
        Route::get('/statii-peco', [StatiePecoController::class, 'index']);
        Route::post('/statii-peco/excel-import', [StatiePecoController::class, 'excelImport']);
    });

    Route::middleware('permission:tech-tools')->get('/clear-all', function () {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        return 'All caches cleared';
    });
});



