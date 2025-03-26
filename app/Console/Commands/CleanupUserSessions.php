<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserSession;
use Carbon\Carbon;

class CleanupUserSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove inactive user sessions older than 30 days';
    /**
     * Execute the console command.
     *
     * @return int
     */

    
    public function handle()
    {
        // Remove sessions inactive for more than 30 days
        $deletedSessions = UserSession::where('last_activity', '<', Carbon::now()->subDays(30))->delete();

        $this->info("Cleaned up {$deletedSessions} inactive user sessions.");
        return 0;
    }
}
