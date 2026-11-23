<?php

namespace App\Services;

use App\Models\PushToken;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Support\Facades\Log;

/**
 * Satu tempat untuk mengakhiri sesi user, dipakai saat password diganti, reset lewat link,
 * dan oleh admin. Sesi web = baris user_sessions + session di Redis; sesi mobile = token
 * Sanctum + push token.
 *
 * Catatan user_sessions.last_activity diperbarui SingleDeviceLogin paling sering tiap
 * TOUCH_EVERY_SECONDS. Baris yang lebih lama dari staleAfterMinutes() dianggap sudah
 * logout (session Redis-nya sudah habis), jadi tidak lagi memicu "login di perangkat lain".
 */
class UserSessionService
{
    /** Seberapa sering last_activity ditulis ke database (supaya tidak satu UPDATE per request). */
    public const TOUCH_EVERY_SECONDS = 300;

    /** Batas sesi dianggap mati: umur session (SESSION_LIFETIME) + jeda karena last_activity tidak ditulis tiap request. */
    public static function staleAfterMinutes(): int
    {
        return (int) config('session.lifetime', 120) + (int) ceil(self::TOUCH_EVERY_SECONDS / 60) + 5;
    }

    /** Hapus sesi web user (Redis + catatan), kecuali sesi $exceptSessionId. Mengembalikan jumlah yang dihapus. */
    public function terminateWebSessions(User $user, ?string $exceptSessionId = null): int
    {
        $handler = app('session')->getHandler();
        $count = 0;

        $query = UserSession::where('user_id', $user->id);
        if ($exceptSessionId) {
            $query->where(fn($q) => $q->whereNull('session_id')->orWhere('session_id', '!=', $exceptSessionId));
        }

        foreach ($query->get() as $session) {
            if ($session->session_id) {
                $handler->destroy($session->session_id);
            }
            $session->delete();
            $count++;
        }

        return $count;
    }

    /** Cabut semua token aplikasi mobile dan matikan push token-nya. */
    public function revokeMobile(User $user): int
    {
        $count = $user->tokens()->delete();
        PushToken::where('user_id', $user->id)->update(['is_active' => false]);
        return $count;
    }

    /**
     * Keluarkan user dari semua perangkat (web dan mobile), kecuali sesi web $exceptSessionId
     * (sesi yang sedang dipakai untuk mengganti password, supaya tidak ikut keluar).
     */
    public function logoutEverywhere(User $user, ?string $exceptSessionId = null, string $reason = ''): void
    {
        $web    = $this->terminateWebSessions($user, $exceptSessionId);
        $mobile = $this->revokeMobile($user);

        Log::info('User sessions terminated', [
            'user_id'        => $user->id,
            'reason'         => $reason,
            'web_sessions'   => $web,
            'mobile_tokens'  => $mobile,
            'kept_current'   => (bool) $exceptSessionId,
        ]);
    }

    /** Hapus catatan sesi yang sudah lama tidak aktif (dipakai command sessions:cleanup). */
    public function pruneStale(): int
    {
        return UserSession::where(fn($q) => $q->whereNull('last_activity')
            ->orWhere('last_activity', '<', now()->subMinutes(self::staleAfterMinutes())))
            ->delete();
    }
}
