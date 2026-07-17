<?php

namespace App\Http\Middleware;

use App\Models\RegisteredDevice;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckDeviceBinding
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $deviceId = $request->header('X-Device-Id') ?? $request->input('device_id');

        Log::info('CheckDeviceBinding - device_id received: ' . ($deviceId ?? 'NULL'));

        if (!$deviceId) {
            Log::info('CheckDeviceBinding - skip, no device_id sent');
            return $next($request);
        }

        $registeredDevice = RegisteredDevice::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        Log::info('CheckDeviceBinding - registered device found: ' . ($registeredDevice ? $registeredDevice->device_id : 'NONE'));

        if (!$registeredDevice || $registeredDevice->device_id !== $deviceId) {
            Log::info('CheckDeviceBinding - MISMATCH, invalidating token');
            $user->currentAccessToken()->delete();

            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak valid, silakan login ulang',
                'code' => 'DEVICE_INVALIDATED',
            ], 401);
        }

        Log::info('CheckDeviceBinding - OK, device matches');
        return $next($request);
    }
}