<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use App\Models\User;

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
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        $dashboardRoutes = [
            'dashboardAdmin'      => 'pages.dashboardAdmin',
            'dashboardHuman'      => 'pages.dashboardHuman',
            'dashboardSupervisor' => 'pages.dashboardSupervisor',
            'dashboardTeam'       => 'pages.dashboardTeam',
            'dashboardDirector'   => 'pages.dashboardDirector',
            'dashboardHR'         => 'pages.dashboardHR',
        ];

        foreach ($dashboardRoutes as $permission => $route) {
            if ($user->hasPermissionTo($permission)) {
                return redirect()->route($route);
            }
        }

        Auth::logout();
        return redirect('/')->withErrors(['error' => 'Access Denied.']);
    }

    return $next($request);
}
}
