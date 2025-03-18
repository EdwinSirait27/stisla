<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
{
    if (Auth::check()) {
        $user = auth()->user();
        
        if ($user->can('isAdmin')) {
            return redirect('/dashboardAdmin');
        }
        if ($user->can('isKasir')) {
            return redirect('/dashboardKasir');
        }
        if ($user->can('isManager')) {
            return redirect('/dashboardManager');
        }
        if ($user->can('isSupervisor')) {
            return redirect('/dashboardSupervisor');
        }

        // Jika user_type tidak valid, logout dan kembali ke login
        Auth::logout();
        return redirect('/')->withErrors(['error' => 'Akses ditolak.']);
    }

    return $next($request);
}

    
}
