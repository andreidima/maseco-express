<?php

namespace App\Support\Auth;

use App\Models\User;
use App\Support\Navigation\MainNavigation;

class UserRedirector
{
    public static function redirectPathFor(?User $user, string $fallback = '/'): string
    {
        if (! $user) {
            return $fallback;
        }

        if ($user->hasRole('sofer')) {
            return route('sofer.dashboard');
        }

        if ($user->hasRole('mecanic')) {
            return route('service-masini.index');
        }

        if ($user->hasPermission('dashboard')) {
            return route('dashboard');
        }

        $menuUrl = MainNavigation::firstAccessibleUrlFor($user);

        if ($menuUrl) {
            return $menuUrl;
        }

        return $fallback;
    }
}
