<?php

namespace App\Providers;

use App\Models\Masini\Masina;
use App\Models\Masini\MasinaDocument;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

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
