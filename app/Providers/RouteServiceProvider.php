<?php

namespace App\Providers;

use App\Models\FacturiFurnizori\FacturaFurnizor;
use App\Models\FacturiFurnizori\PlataCalup;
use App\Models\Masini\Masina;
use App\Models\Masini\MasinaDocument;
use App\Models\Masini\MasinaDocumentFisier;
use App\Models\Masini\MasinaFisierGeneral;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    // public const HOME = '/home';
    public const HOME = '/acasa';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        Route::bind('document', function ($value, $route) {
            $masinaParameter = $route?->parameter('masini_mementouri');

            if ($masinaParameter instanceof Masina) {
                return MasinaDocument::resolveForMasina($masinaParameter, $value);
            }

            if ($masinaParameter !== null) {
                $masina = Masina::query()->whereKey($masinaParameter)->first();

                if ($masina instanceof Masina) {
                    return MasinaDocument::resolveForMasina($masina, $value);
                }

                throw (new ModelNotFoundException())->setModel(Masina::class, [$masinaParameter]);
            }

            return MasinaDocument::query()->findOrFail($value);
        });

        Route::bind('fisier', function ($value, $route) {
            $routeName = $route?->getName();

            if (is_string($routeName)) {
                if (Str::startsWith($routeName, 'facturi-furnizori.facturi.fisiere.')) {
                    $facturaParameter = $route?->parameter('factura');

                    if (! $facturaParameter instanceof FacturaFurnizor) {
                        if ($facturaParameter === null) {
                            throw (new ModelNotFoundException())->setModel(FacturaFurnizor::class);
                        }

                        $facturaParameter = FacturaFurnizor::query()->whereKey($facturaParameter)->firstOrFail();
                    }

                    return $facturaParameter->fisiere()->whereKey($value)->firstOrFail();
                }

                if (Str::startsWith($routeName, 'facturi-furnizori.plati-calupuri.')) {
                    $calupParameter = $route?->parameter('plataCalup');

                    if (! $calupParameter instanceof PlataCalup) {
                        if ($calupParameter === null) {
                            throw (new ModelNotFoundException())->setModel(PlataCalup::class);
                        }

                        $calupParameter = PlataCalup::query()->whereKey($calupParameter)->firstOrFail();
                    }

                    return $calupParameter->fisiere()->whereKey($value)->firstOrFail();
                }

                if (Str::startsWith($routeName, 'masini-mementouri.fisiere-generale.')) {
                    return MasinaFisierGeneral::query()->findOrFail($value);
                }
            }

            $documentParameter = $route?->parameter('document');
            $masinaParameter = $route?->parameter('masini_mementouri');

            if ($documentParameter !== null && ! $documentParameter instanceof MasinaDocument) {
                if ($masinaParameter instanceof Masina) {
                    $documentParameter = MasinaDocument::resolveForMasina($masinaParameter, $documentParameter);
                } elseif ($masinaParameter !== null) {
                    $masina = Masina::query()->whereKey($masinaParameter)->first();

                    if ($masina instanceof Masina) {
                        $documentParameter = MasinaDocument::resolveForMasina($masina, $documentParameter);
                    }
                }
            }

            if ($documentParameter instanceof MasinaDocument) {
                return $documentParameter->fisiere()->whereKey($value)->firstOrFail();
            }

            return MasinaDocumentFisier::query()->findOrFail($value);
        });

        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
