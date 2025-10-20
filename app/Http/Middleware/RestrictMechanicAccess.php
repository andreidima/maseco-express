<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RestrictMechanicAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->hasRole('mecanic')) {
            return $next($request);
        }

        $allowedRouteNames = [
            'gestiune-piese.index',
            'service-masini.index',
            'service-masini.store-masina',
            'service-masini.update-masina',
            'service-masini.destroy-masina',
            'service-masini.entries.store',
            'service-masini.entries.update',
            'service-masini.entries.destroy',
            'service-masini.export',
            'logout',
        ];

        $route = $request->route();
        $routeName = $route?->getName();

        if ($routeName && str_starts_with($routeName, 'gestiune-piese.')) {
            if ($routeName !== 'gestiune-piese.index') {
                abort(403);
            }

            return $next($request);
        }

        if ($routeName && in_array($routeName, $allowedRouteNames, true)) {
            return $next($request);
        }

        $allowedPrefixes = [
            'service-masini',
        ];

        $path = trim($request->path(), '/');

        foreach ($allowedPrefixes as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return $next($request);
            }
        }

        abort(403);
    }
}
