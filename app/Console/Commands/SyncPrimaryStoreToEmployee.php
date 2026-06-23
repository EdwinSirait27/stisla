<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncPrimaryStoreToEmployee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:primary-store';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync primary employee store to employees table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::table('employee_stores')
            ->where('is_primary', 1)
            ->orderBy('id')
            ->chunk(100, function ($stores) {

                foreach ($stores as $store) {

                    DB::table('employees_tables')
                        ->where('id', $store->employee_id)
                        ->where(function ($query) use ($store) {
                            $query->whereNull('store_id')
                                ->orWhere('store_id', '!=', $store->store_id);
                        })
                        ->update([
                            'store_id' => $store->store_id,
                            'updated_at' => now(),
                        ]);
                }
            });

        $this->info('Primary store synced.');
    }
}
