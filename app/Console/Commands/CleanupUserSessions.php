<?php

namespace App\Console\Commands;

use App\Services\UserSessionService;
use Illuminate\Console\Command;

/**
 * Hapus catatan user_sessions yang sudah tidak aktif melewati umur session
 * (SESSION_LIFETIME + jeda). Session Redis-nya sudah habis dengan sendirinya;
 * catatan basi ini dulu menyisakan status "masih login" palsu.
 */
class CleanupUserSessions extends Command
{
    protected $signature = 'sessions:cleanup';

    protected $description = 'Hapus catatan sesi web yang sudah habis';

    public function handle(UserSessionService $sessions): int
    {
        $deleted = $sessions->pruneStale();
        $this->info("Menghapus {$deleted} catatan sesi yang sudah habis.");
        return self::SUCCESS;
    }
}
