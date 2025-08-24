<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Models\Activity;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Spatie\Backup\Events\BackupHasSucceeded;
use Spatie\Backup\Events\BackupHasFailed;
use App\Services\TelegramNotifier;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Login::class => [
            'App\Listeners\LogSuccessfulLogin',
        ],
        Logout::class => [
            'App\Listeners\LogSuccessfulLogout',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
   public function boot()
    {
        parent::boot();

        Event::listen(BackupHasSucceeded::class, function ($event) {
            TelegramNotifier::send(
                "✅ Backup berhasil dijalankan:\nDisk: {$event->backupDestination->disk}\nPath: {$event->backupDestination->path}"
            );
        });

        Event::listen(BackupHasFailed::class, function ($event) {
            TelegramNotifier::send(
                "❌ Backup gagal:\n{$event->exception->getMessage()}"
            );
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
