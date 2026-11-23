<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Tahap akhir login, dipakai bersama oleh LoginController (tanpa 2FA) dan
 * TwoFactorController (setelah kode 2FA valid), supaya urutannya selalu sama:
 *
 *   password valid → status karyawan → 2FA → perangkat lain → simpan sesi
 *   → wajib setup 2FA / password default / dashboard
 *
 * Konfirmasi "login di perangkat lain" memakai token sekali pakai yang terikat
 * ke session browser ini. Password tidak pernah disimpan ke session.
 */
class LoginSessionService
{
    public const PENDING_KEY = 'login.pending';
    public const PENDING_TTL = 120; // detik

    /** Dipanggil saat user SUDAH lolos password (dan 2FA kalau aktif) dan sudah Auth::login. */
    public function finish(Request $request, User $user, bool $force): RedirectResponse
    {
        $currentSessionId = $request->session()->getId();

        // Catatan sesi yang sudah habis bukan "perangkat lain": hapus supaya tidak memicu konfirmasi palsu
        UserSession::where('user_id', $user->id)
            ->where('session_id', '!=', $currentSessionId)
            ->where(fn($q) => $q->whereNull('last_activity')
                ->orWhere('last_activity', '<', now()->subMinutes(UserSessionService::staleAfterMinutes())))
            ->delete();

        $hasOtherSession = UserSession::where('user_id', $user->id)
            ->where('session_id', '!=', $currentSessionId)
            ->exists();

        if ($hasOtherSession && !$force) {
            Log::info('User already logged in elsewhere', ['user_id' => $user->id, 'ip' => $request->ip()]);

            Auth::logout();
            $token = Str::random(40);
            $request->session()->put(self::PENDING_KEY, [
                'token_hash' => hash('sha256', $token),
                'user_id'    => $user->id,
                'expires_at' => now()->addSeconds(self::PENDING_TTL)->timestamp,
            ]);

            return redirect()->route('login')->with('confirm_force_login', [
                'message' => 'Akun Anda sedang login di perangkat lain. Lanjutkan login di sini dan keluarkan perangkat tersebut?',
                'token'   => $token,
            ]);
        }

        if ($hasOtherSession) {
            // Hancurkan juga session Redis-nya, bukan hanya catatannya
            app(UserSessionService::class)->terminateWebSessions($user, $currentSessionId);
            Log::info('Other device sessions terminated by force login', ['user_id' => $user->id, 'ip' => $request->ip()]);
        }

        UserSession::updateOrCreate(
            ['user_id' => $user->id, 'session_id' => $currentSessionId],
            [
                'ip_address'    => $request->ip(),
                'last_activity' => now(),
                'device_type'   => $request->header('User-Agent'),
            ]
        );

        // Wajib 2FA tapi belum setup: login sah, tapi RequireTwoFactor mengunci semua halaman lain.
        if ($user->requiresTwoFactor() && !$user->hasTwoFactorEnabled()) {
            return redirect()->route('2fa.setup')
                ->with('warning', 'Akun Anda diwajibkan mengaktifkan 2FA sebelum bisa mengakses sistem.');
        }

        // Password masih default (username / NIP): ForcePasswordChange mengunci semua halaman lain.
        if (self::hasDefaultPassword($user)) {
            Log::warning('User login with default password', ['user_id' => $user->id, 'ip' => $request->ip()]);

            return redirect()->route('pages.change-password')
                ->with('warning', 'Password Anda masih default (sama dengan username atau NIP). Silakan ganti password terlebih dahulu.');
        }

        $redirect = DashboardRedirectService::redirectForUser($user);
        if ($redirect) {
            Log::info('User logged in', ['user_id' => $user->id, 'ip' => $request->ip()]);

            return $redirect->with('success', 'Success login, Goodluck!!!');
        }

        Log::warning('User has no valid permission', ['user_id' => $user->id, 'ip' => $request->ip()]);
        UserSession::where('user_id', $user->id)->where('session_id', $currentSessionId)->delete();
        Auth::logout();

        return redirect()->route('login')->with('warning', 'Akun Anda belum punya role yang valid. Hubungi HR.');
    }

    /** Ambil & hapus token konfirmasi. Mengembalikan data pending kalau valid, null kalau tidak. */
    public function consumePending(Request $request, ?string $token): ?array
    {
        $pending = $request->session()->pull(self::PENDING_KEY);

        if (!$pending || !$token || ($pending['expires_at'] ?? 0) < now()->timestamp) {
            return null;
        }

        return hash_equals($pending['token_hash'], hash('sha256', $token)) ? $pending : null;
    }

    /**
     * Password masih default? Default = username atau NIP (employee_pengenal).
     * Kalau $plain diberikan (password yang baru saja dicek benar), cukup dibandingkan teks, tanpa bcrypt.
     */
    public static function hasDefaultPassword(User $user, ?string $plain = null): bool
    {
        $candidates = array_values(array_unique(array_filter([
            strtolower((string) $user->username),
            (string) ($user->employee?->employee_pengenal ?? ''),
        ], fn($v) => $v !== '')));

        if ($plain !== null) {
            foreach ($candidates as $c) {
                if (hash_equals(strtolower($c), strtolower($plain))) return true;
            }
            return false;
        }

        foreach ($candidates as $c) {
            if (Hash::check($c, $user->password)) return true;
        }
        return false;
    }

    /** Akun boleh login? (dipakai sebelum 2FA dan saat konfirmasi perangkat lain) */
    public static function canLogin(User $user): bool
    {
        return $user->employee && $user->employee->status === 'Active';
    }
}
