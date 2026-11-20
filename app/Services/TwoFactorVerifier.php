<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use PragmaRX\Google2FA\Google2FA;

/** Verifikasi kode 2FA (TOTP 6 digit dengan anti replay, atau recovery code 10 karakter). */
class TwoFactorVerifier
{
    /** @return string|null 'totp' | 'recovery' kalau valid, null kalau tidak */
    public static function verify(User $user, ?string $code): ?string
    {
        $code = strtoupper(trim((string) $code));
        if (strlen($code) === 10) {
            return $user->useRecoveryCode($code) ? 'recovery' : null;
        }
        if (!preg_match('/^\d{6}$/', $code) || !$user->two_factor_secret) {
            return null;
        }

        $tsKey = "2fa:last-ts:{$user->id}";
        $ts    = (new Google2FA())->verifyKeyNewer(decrypt($user->two_factor_secret), $code, (int) Cache::get($tsKey, 0), 1);
        if ($ts === false) {
            return null;
        }
        Cache::put($tsKey, $ts, now()->addMinutes(10));

        return 'totp';
    }
}
