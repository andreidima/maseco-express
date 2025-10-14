<?php

namespace App\Providers;


use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\DocumentWord::class => \App\Policies\DocumentWordPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('is-admin', function ($user) {
            return $user->hasRole('admin') || $user->hasRole(1);
        });

        Gate::define('access-tech', function ($user) {
            // User #1 retains emergency access even before the role seeder runs so the
            // Tech menu (migration tooling, etc.) stays available during rollouts.
            return $user->id === 1 || $user->hasRole('super-admin');
        });
    }
}
