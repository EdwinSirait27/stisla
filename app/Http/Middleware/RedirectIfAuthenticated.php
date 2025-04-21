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
        if ($user->can('isHeadHR')) {
            return redirect('/dashboardHR');
        }
        if ($user->can('isHR')) {
            return redirect('/dashboardHR');
        }
        if ($user->can('isManagerStore')) {
            return redirect('/dashboardManager');
        }
        if ($user->can('isSupervisor')) {
            return redirect('/dashboardSupervisor');
        }
        if ($user->can('isHeadBuyer')) {
            return redirect('/dashboardBuyer');
        }
        if ($user->can('isBuyer')) {
            return redirect('/dashboard');
        }

        // Jika user_type tidak valid, logout dan kembali ke login
        Auth::logout();
        return redirect('/')->withErrors(['error' => 'Akses ditolak.']);
    }

    return $next($request);
}

    
}
