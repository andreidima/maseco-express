<?php

namespace App\Providers;

use App\Support\MailSettings;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MailSettings::class, function () {
            return new MailSettings();
        });
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

        // Use Bootstrap pagination views across the application
        Paginator::useBootstrapFive();

        app(MailSettings::class)->applyToConfig();
    }
}
