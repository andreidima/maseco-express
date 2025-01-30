<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Auth;

class CheckBanned
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(auth()->check() && (auth()->user()->activ == 0)){
            Auth::logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            // Prevent infinite redirect loop
            if ($request->routeIs('login')) {
                return $next($request);
            }

            return redirect()->route('login')->with('error', 'Contul tău este suspendat. Contactează administratorul.');

        }

        return $next($request);
    }
}
