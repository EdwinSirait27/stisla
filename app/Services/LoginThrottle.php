<?php

namespace App\Services;

use Illuminate\Support\Facades\RateLimiter;

/**
 * Batas percobaan login gagal, dipakai bersama login web dan aplikasi mobile.
 * Kunci per username sengaja sama untuk kedua kanal, jadi menebak password
 * bergantian lewat web dan mobile tetap terhitung sebagai satu.
 *
 * - pair      : username + IP, mengerem tebak cepat dari satu mesin (5 / menit)
 * - user      : per username dari IP mana pun (10 / 15 menit)
 * - user_long : per username, kunci lebih lama (25 / 1 jam)
 * - ip        : per IP lintas username, password spraying (30 / 10 menit)
 */
class LoginThrottle
{
    public const LIMITS = [
        'pair'      => [5, 60],
        'user'      => [10, 900],
        'user_long' => [25, 3600],
        'ip'        => [30, 600],
    ];

    public static function keys(string $username, string $ip): array
    {
        $username = strtolower($username);

        return [
            'pair'      => 'login:pair:' . sha1($username . '|' . $ip),
            'user'      => 'login:user:' . $username,
            'user_long' => 'login:user-long:' . $username,
            'ip'        => 'login:ip:' . $ip,
        ];
    }

    /** Sisa detik terkunci (0 kalau tidak terkunci). */
    public static function lockedFor(array $keys): int
    {
        $wait = 0;
        foreach ($keys as $name => $key) {
            if (RateLimiter::tooManyAttempts($key, self::LIMITS[$name][0])) {
                $wait = max($wait, RateLimiter::availableIn($key));
            }
        }

        return $wait;
    }

    public static function hit(array $keys): void
    {
        foreach ($keys as $name => $key) {
            RateLimiter::hit($key, self::LIMITS[$name][1]);
        }
    }

    /** Login sukses menghapus hitungan username & pasangan; hitungan per IP dibiarkan habis sendiri. */
    public static function clear(array $keys): void
    {
        RateLimiter::clear($keys['pair']);
        RateLimiter::clear($keys['user']);
        RateLimiter::clear($keys['user_long']);
    }
}
