<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RequireTwoFactor
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) return $next($request);

        // Kalau wajib 2FA tapi belum setup — paksa ke setup page
        if (
            $user->requiresTwoFactor()
            && !$user->hasTwoFactorEnabled()
            && !$request->routeIs('2fa.*')
            && !$request->routeIs('logout')
        ) {
            return redirect()->route('2fa.setup')
                ->with('warning', 'Anda diwajibkan mengaktifkan 2FA sebelum mengakses sistem.');
        }
        return $next($request);
    }
}
