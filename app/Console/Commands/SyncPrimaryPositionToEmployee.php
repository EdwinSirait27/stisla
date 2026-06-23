<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncPrimaryPositionToEmployee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:primary-position';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync primary employee position to employees table';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::table('employee_positions')
            ->where('is_primary', 1)
            ->orderBy('id')
            ->chunk(100, function ($positions) {

                foreach ($positions as $position) {

                    DB::table('employees_tables')
                        ->where('id', $position->employee_id)
                        ->where(function ($query) use ($position) {
                            $query->whereNull('position_id')
                                ->orWhere('position_id', '!=', $position->position_id);
                        })
                        ->update([
                            'position_id' => $position->position_id,
                            'updated_at' => now(),
                        ]);
                }
            });

        $this->info('Primary position synced.');
    }
}
