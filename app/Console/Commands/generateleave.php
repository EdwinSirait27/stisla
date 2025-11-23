<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Leavebalance;
class generateleave extends Command
{
    protected $signature = 'app:generate-leave';
    protected $description = 'Generate prorated annual leave for all employees';
    public function handle()
    {
        $today = now();
        $currentYear = $today->year;
        $employees = Employee::whereDate('join_date', '<=', now()->subYear())->get();
        foreach ($employees as $emp) {
            LeaveBalance::updateOrCreate(
                [
                    'employee_id' => $emp->id,
                    'leave_type_id' => '019ab050-da2c-722f-8423-2c3b9fb531ad',
                    'year' => $currentYear,
                ],
                [
                    'balance_days' => 12,
                ]
            );
        }
        $this->info("Leave generated for employees with ≥1 year tenure.");
    }
}
