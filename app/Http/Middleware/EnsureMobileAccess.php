<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Setiap request API mobile: akun harus masih punya permission mobile.attendance
 * dan karyawannya berstatus Active. Dicek per request (bukan hanya saat login),
 * jadi mencabut permission atau me-resign-kan karyawan langsung memutus akses token yang sudah ada.
 */
class EnsureMobileAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) return $next($request);

        // Dibaca langsung dari database: status bisa berubah selagi token masih hidup
        $employee = $user->employee()->first();
        $reason = match (true) {
            !$employee                                 => 'Data karyawan tidak ditemukan, hubungi HR',
            $employee->status !== 'Active'             => 'Akun tidak aktif, hubungi HR',
            !$user->hasPermissionTo('mobile.attendance', 'web') => 'Akun Anda tidak punya akses aplikasi absensi, hubungi HR',
            default                                    => null,
        };

        if ($reason) {
            $user->currentAccessToken()?->delete();
            return response()->json(['success' => false, 'message' => $reason, 'code' => 'ACCESS_REVOKED'], 403);
        }

        return $next($request);
    }
}
