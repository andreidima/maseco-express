<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models\DocumentWord;
use App\Observers\DocumentWordObserver;

class ObserverServiceProvider extends ServiceProvider
{
    public function boot()
    {
        DocumentWord::observe(DocumentWordObserver::class);
    }
}
