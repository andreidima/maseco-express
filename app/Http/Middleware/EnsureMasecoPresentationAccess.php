<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMasecoPresentationAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('maseco_presentation_mode')) {
            return $next($request);
        }

        if (! $request->user()) {
            $request->session()->forget('maseco_presentation_mode');

            return $next($request);
        }

        if ($this->isAllowed($request)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403);
        }

        return redirect('/comenzi');
    }

    protected function isAllowed(Request $request): bool
    {
        $path = trim($request->path(), '/');

        if ($request->routeIs('logout')) {
            return true;
        }

        if ($request->is('comenzi-maseco/*')) {
            return true;
        }

        if (in_array($path, ['comenzi', 'comenzi/adauga', 'axios/statusuri', 'axios/clienti', 'axios/locuri-operare'], true)) {
            return true;
        }

        $patterns = [
            '#^comenzi/[0-9]+$#',
            '#^comenzi/[0-9]+/modifica$#',
            '#^comenzi/[0-9]+/(export-excel|export-html|export-pdf|export-debit-note-html|export-debit-note-pdf)$#',
            '#^comenzi/[0-9]+/stare/(deschide|inchide|anuleaza)$#',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $path)) {
                return true;
            }
        }

        return false;
    }
}
