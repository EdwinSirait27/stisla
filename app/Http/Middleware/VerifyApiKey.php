<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Untuk pemanggilan antar aplikasi (server ke server).
 * Pemanggil wajib mengirim header X-Api-Key yang cocok dengan salah satu key di config/services.php.
 */
class VerifyApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $given = (string) $request->header('X-Api-Key', '');

        if ($given !== '') {
            foreach (config('services.api_keys', []) as $client => $key) {
                // hash_equals: perbandingan waktu-konstan, key tidak bisa ditebak per karakter
                if (!empty($key) && hash_equals((string) $key, $given)) {
                    $request->attributes->set('api_client', $client);
                    return $next($request);
                }
            }
        }

        Log::warning('API key ditolak', [
            'ip'     => $request->ip(),
            'path'   => $request->path(),
            'reason' => $given === '' ? 'header kosong' : 'key salah',
        ]);

        return response()->json(['message' => 'API key tidak valid'], 401);
    }
}
