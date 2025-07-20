<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Role
{
    public function handle($request, Closure $next, ... $roles)
    {
        if (!Auth::check()) // I included this check because you have it, but it really should be part of your 'auth' middleware, most likely added as part of a route group.
            return redirect('login');

        $user = Auth::user();
// dd($user);
        foreach($roles as $role) {
            // Check if user has the role This check will depend on how your roles are set up
            if($user->role === intval($role))
                return $next($request);
        }

        return redirect('login');
    }
}
