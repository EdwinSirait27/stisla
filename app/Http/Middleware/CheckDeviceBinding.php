<?php

namespace App\Http\Middleware;

use App\Models\RegisteredDevice;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Token mobile hanya berlaku dari device yang terdaftar. Header X-Device-Id wajib;
 * tanpa header, request ditolak (dulu dilewatkan, sehingga token curian bisa dipakai dari mana saja).
 */
class CheckDeviceBinding
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) return $next($request);

        $deviceId = (string) ($request->header('X-Device-Id') ?? $request->input('device_id') ?? '');

        $registered = RegisteredDevice::where('user_id', $user->id)->where('is_active', true)->first();

        if ($deviceId === '' || !$registered || !hash_equals((string) $registered->device_id, $deviceId)) {
            Log::warning('Mobile device binding rejected', [
                'user_id' => $user->id,
                'reason'  => $deviceId === '' ? 'missing_device_id' : (!$registered ? 'no_registered_device' : 'mismatch'),
                'ip'      => $request->ip(),
            ]);
            $user->currentAccessToken()?->delete();

            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak valid, silakan login ulang',
                'code'    => 'DEVICE_INVALIDATED',
            ], 401);
        }

        return $next($request);
    }
}
