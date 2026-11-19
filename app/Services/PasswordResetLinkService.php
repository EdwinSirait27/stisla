<?php

namespace App\Services;

use App\Mail\PasswordResetLinkMail;
use App\Models\User;
use App\Models\UserPasswordResetLink;
use App\Models\UserSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Link reset password sekali pakai.
 * - Token acak 64 karakter, yang disimpan hanya hash sha256-nya.
 * - Berlaku EXPIRE_MINUTES menit, sekali pakai.
 * - Mengirim link baru membatalkan link lama yang belum dipakai.
 */
class PasswordResetLinkService
{
    public const EXPIRE_MINUTES = 60;
    public const RESEND_COOLDOWN_SECONDS = 60;

    /** Alamat email tujuan: email karyawan yang terhubung ke user. */
    public function emailFor(User $user): ?string
    {
        $email = trim((string) ($user->Employee->email ?? ''));

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    /** Link yang masih aktif (belum dipakai, belum kedaluwarsa), kalau ada. */
    public function activeLink(User $user): ?UserPasswordResetLink
    {
        return UserPasswordResetLink::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();
    }

    /**
     * Buat link baru dan kirim ke email karyawan.
     * @throws RuntimeException kalau email kosong, terlalu sering, atau gagal kirim.
     */
    public function send(User $user, ?User $requestedBy): UserPasswordResetLink
    {
        $email = $this->emailFor($user);
        if (!$email) {
            throw new RuntimeException('Karyawan ini belum punya email yang valid. Isi email di data karyawan dulu.');
        }

        $last = UserPasswordResetLink::where('user_id', $user->id)->latest('id')->first();
        if ($last && $last->created_at->diffInSeconds(now()) < self::RESEND_COOLDOWN_SECONDS) {
            $wait = self::RESEND_COOLDOWN_SECONDS - $last->created_at->diffInSeconds(now());
            throw new RuntimeException("Link baru saja dikirim. Tunggu {$wait} detik sebelum mengirim ulang.");
        }

        $token = Str::random(64);

        $link = DB::transaction(function () use ($user, $requestedBy, $email, $token) {
            // Link lama yang belum dipakai tidak berlaku lagi
            UserPasswordResetLink::where('user_id', $user->id)->whereNull('used_at')->delete();

            return UserPasswordResetLink::create([
                'user_id'      => $user->id,
                'token_hash'   => hash('sha256', $token),
                'sent_to'      => $email,
                'requested_by' => $requestedBy?->id,
                'expires_at'   => now()->addMinutes(self::EXPIRE_MINUTES),
            ]);
        });

        try {
            Mail::to($email)->send(new PasswordResetLinkMail(
                $user,
                route('password.link.show', $token),
                $link->expires_at
            ));
        } catch (\Throwable $e) {
            $link->delete();
            Log::error('PasswordResetLinkService: gagal kirim email', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            throw new RuntimeException('Email reset password gagal dikirim. Cek konfigurasi mail lalu coba lagi.');
        }

        Log::info('PasswordResetLinkService: link dikirim', [
            'user_id'      => $user->id,
            'sent_to'      => $email,
            'requested_by' => $requestedBy?->id,
        ]);

        return $link;
    }

    /** Cari link dari token di URL. Null kalau tidak ada / sudah dipakai / kedaluwarsa. */
    public function findUsable(string $token): ?UserPasswordResetLink
    {
        if (strlen($token) !== 64) {
            return null;
        }

        $link = UserPasswordResetLink::with('user.Employee')
            ->where('token_hash', hash('sha256', $token))
            ->first();

        return $link && $link->isUsable() ? $link : null;
    }

    /** Pakai link: set password baru, tandai link terpakai, keluarkan semua sesi user. */
    public function reset(UserPasswordResetLink $link, string $newPassword): void
    {
        DB::transaction(function () use ($link, $newPassword) {
            // Kunci baris supaya link yang sama tidak bisa dipakai dua kali bersamaan
            $fresh = UserPasswordResetLink::whereKey($link->id)->lockForUpdate()->first();
            if (!$fresh || !$fresh->isUsable()) {
                throw new RuntimeException('Link reset password sudah dipakai atau kedaluwarsa.');
            }

            $user = $fresh->user;
            $user->forceFill([
                'password'       => Hash::make($newPassword),
                'remember_token' => Str::random(60),
            ])->save();

            $fresh->update(['used_at' => now()]);

            // Link lain milik user ini ikut tidak berlaku
            UserPasswordResetLink::where('user_id', $user->id)
                ->whereNull('used_at')
                ->delete();

            $this->logoutEverywhere($user);
        });
    }

    /** Hapus semua sesi aktif user (session Redis + catatan user_sessions). */
    public function logoutEverywhere(User $user): void
    {
        $handler = app('session')->getHandler();

        foreach (UserSession::where('user_id', $user->id)->get() as $session) {
            if ($session->session_id) {
                $handler->destroy($session->session_id);
            }
            $session->forceDelete();
        }
    }

    /** Batalkan link aktif (dipakai saat admin mengganti password manual). */
    public function revokeAll(User $user): void
    {
        UserPasswordResetLink::where('user_id', $user->id)->whereNull('used_at')->delete();
    }
}
