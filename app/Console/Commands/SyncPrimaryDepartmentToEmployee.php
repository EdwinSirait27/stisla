<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncPrimaryDepartmentToEmployee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:primary-department';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync primary employee department to employees table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::table('employee_departments')
            ->where('is_primary', 1)
            ->orderBy('id')
            ->chunk(100, function ($departments) {

                foreach ($departments as $department) {

                    DB::table('employees_tables')
                        ->where('id', $department->employee_id)
                        ->where(function ($query) use ($department) {
                            $query->whereNull('department_id')
                                ->orWhere('department_id', '!=', $department->department_id);
                        })
                        ->update([
                            'department_id' => $department->department_id,
                            'updated_at' => now(),
                        ]);
                }
            });
        $this->info('Primary department synced.');
    }
}
