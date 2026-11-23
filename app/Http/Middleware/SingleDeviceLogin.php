<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use App\Services\UserSessionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Satu akun, satu sesi web aktif. Sesi dianggap sah hanya kalau tercatat di user_sessions:
 * - catatannya dihapus saat login paksa di perangkat lain, ganti/reset password, atau admin
 *   mengganti password, jadi sesi lama langsung berakhir di request berikutnya;
 * - last_activity diperbarui paling sering tiap 5 menit, supaya sesi yang sudah habis tidak lagi
 *   dianggap "sedang login" (dulu tidak pernah diperbarui sehingga semua catatan terlihat basi).
 */
class SingleDeviceLogin
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user      = Auth::user();
        $sessionId = $request->session()->getId();
        $current   = UserSession::where('user_id', $user->id)->where('session_id', $sessionId)->first();

        if (!$current) {
            Log::info('Web session ended: no session record', ['user_id' => $user->id, 'ip' => $request->ip()]);
            return $this->end($request, 'Sesi Anda sudah berakhir atau Anda login di perangkat lain. Silakan login kembali.');
        }

        $otherLive = UserSession::where('user_id', $user->id)
            ->where('session_id', '!=', $sessionId)
            ->active()
            ->exists();

        if ($otherLive) {
            Log::warning('Web session ended: another live session', ['user_id' => $user->id, 'ip' => $request->ip()]);
            $current->delete();
            return $this->end($request, 'Anda telah logout karena akun ini dipakai di perangkat lain. Silakan login kembali.');
        }

        if (!$current->last_activity || $current->last_activity < now()->subSeconds(UserSessionService::TOUCH_EVERY_SECONDS)) {
            $current->forceFill(['last_activity' => now(), 'ip_address' => $request->ip()])->save();
        }

        return $next($request);
    }

    /** Akhiri sesi ini sepenuhnya: logout, hancurkan isi session, dan ganti token CSRF. */
    private function end(Request $request, string $message)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'code' => 'SESSION_ENDED'], 401);
        }

        return redirect('/')->with('error', $message);
    }
}
