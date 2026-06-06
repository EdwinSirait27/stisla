<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user()->fresh();

        // Route yang boleh diakses meski belum ganti password
        $allowedRoutes = [
            'pages.change-password',
            'logout',
             'pages.change-password.update',
        ];
        if (
            Hash::check(strtolower($user->username), $user->password) &&
            !in_array($request->route()->getName(), $allowedRoutes)
        ) {
            return redirect()->route('pages.change-password')
                ->with('warning', 'You must change your default password before continuing.');
        }

        return $next($request);
    }
}