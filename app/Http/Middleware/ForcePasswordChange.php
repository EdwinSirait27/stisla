<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Paksa ganti password kalau password masih default (sama dengan username atau NIP).
 * Hasil Hash::check disimpan di session per hash password, jadi bcrypt hanya
 * dijalankan sekali per sesi (dan dihitung ulang otomatis begitu password berubah).
 */
class ForcePasswordChange
{
    private const ALLOWED = ['pages.change-password', 'change-password.update', 'logout', '2fa.*'];

    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user()->fresh();
        $fingerprint = hash('sha256', (string) $user->password);
        $cached = $request->session()->get('pwd_default');

        if (!is_array($cached) || ($cached['fp'] ?? null) !== $fingerprint) {
            $cached = ['fp' => $fingerprint, 'is' => \App\Services\LoginSessionService::hasDefaultPassword($user)];
            $request->session()->put('pwd_default', $cached);
        }

        if ($cached['is'] && !$request->routeIs(...self::ALLOWED)) {
            return redirect()->route('pages.change-password')
                ->with('warning', 'Anda wajib mengganti password default sebelum melanjutkan.');
        }

        return $next($request);
    }
}
