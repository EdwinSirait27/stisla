<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * User yang diwajibkan 2FA (two_factor_required) tapi belum mengaktifkannya
 * hanya boleh membuka halaman setup 2FA, ganti password, dan logout.
 * Terdaftar di group middleware "web" (Kernel), jadi berlaku untuk semua halaman.
 */
class RequireTwoFactor
{
    private const ALLOWED = ['2fa.*', 'logout', 'pages.change-password', 'change-password.update'];

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->requiresTwoFactor() && !$user->hasTwoFactorEnabled() && !$request->routeIs(...self::ALLOWED)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Aktifkan 2FA terlebih dahulu.'], 403);
            }

            return redirect()->route('2fa.setup')
                ->with('warning', 'Anda diwajibkan mengaktifkan 2FA sebelum mengakses sistem.');
        }

        return $next($request);
    }
}
