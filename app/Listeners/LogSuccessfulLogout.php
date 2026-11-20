<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

/** Catat logout ke Spatie activity log (log_name "auth"). */
class LogSuccessfulLogout
{
    public function handle(Logout $event): void
    {
        if (!$event->user) {
            return;
        }

        try {
            activity('auth')
                ->causedBy($event->user)
                ->performedOn($event->user)
                ->event('logout')
                ->withProperties([
                    'channel'    => 'web',
                    'ip'         => Request::ip(),
                    'user_agent' => substr((string) Request::userAgent(), 0, 255),
                ])
                ->log('Logout');
        } catch (\Throwable $e) {
            Log::error('Failed to record logout activity: ' . $e->getMessage());
        }
    }
}
