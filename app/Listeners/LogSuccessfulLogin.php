<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

/**
 * Catat login ke Spatie activity log (log_name "auth"), supaya muncul di halaman Activity Log.
 * Dulu listener ini menjalankan perintah shell untuk membaca MAC address, tapi yang terbaca
 * adalah kartu jaringan SERVER, bukan perangkat user, jadi dihapus.
 */
class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        try {
            activity('auth')
                ->causedBy($event->user)
                ->performedOn($event->user)
                ->event('login')
                ->withProperties([
                    'channel'    => 'web',
                    'ip'         => Request::ip(),
                    'user_agent' => substr((string) Request::userAgent(), 0, 255),
                    'remember'   => $event->remember,
                ])
                ->log('Login');
        } catch (\Throwable $e) {
            Log::error('Failed to record login activity: ' . $e->getMessage());
        }
    }
}
