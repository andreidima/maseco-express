<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Route;

use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::resourceVerbs([
            'create' => 'adauga',
            'edit' => 'modifica'
        ]);

        // Set the locale to Romanian globally
        Carbon::setLocale('ro');
    }
}
