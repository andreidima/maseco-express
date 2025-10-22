<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanStopImpersonation
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('impersonated_by')) {
            return $next($request);
        }

        $user = $request->user();

        if ($user && $user->hasPermission('tech-tools')) {
            return $next($request);
        }

        abort(403);
    }
}
