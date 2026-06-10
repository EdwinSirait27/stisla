<?php
namespace App\Console\Commands;

use App\Jobs\SendProbationReminderEmail;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class SendProbationReminder extends Command
{
    protected $signature   = 'reminder:probation';
    protected $description = 'Send probation reminder email for employees who joined 3 months ago';

    public function handle()
    {
      // Ambil semua HeadHR beserta relasi employee -> company_email
$headHRs = User::role('HeadHR')
    ->with('employee')
    ->whereHas('employee', function ($q) {
        $q->whereNotNull('company_email')
          ->where('company_email', '!=', '');
    })
    ->get();
        if ($headHRs->isEmpty()) {
            Log::warning('No HeadHR found with company_email');
            $this->warn('No HeadHR found.');
            return;
        }

        $employees = Employee::whereDate('join_date', now()->subMonths(2)->toDateString())
    ->whereIn('status', ['Active', 'Pending', 'Mutation', 'On Leave'])
    ->get();
        if ($employees->isEmpty()) {
            Log::info('No employees hit 3-month probation today');
            $this->info('No employees to remind today.');
            return;
        }

        $jobs = [];
        foreach ($employees as $employee) {
            foreach ($headHRs as $headHR) {
                $jobs[] = new SendProbationReminderEmail($employee, $headHR);
            }
        }

        Bus::batch($jobs)
            ->name('Probation Reminder ' . now()->toDateString())
            ->onQueue('probationreminder')
            ->allowFailures()
            ->dispatch();

        Log::info('Probation reminder batch dispatched', [
            'total_employees' => $employees->count(),
            'total_head_hrs'  => $headHRs->count(),
            'total_jobs'      => count($jobs),
        ]);

        $this->info("Dispatched {$employees->count()} reminder(s) to {$headHRs->count()} HeadHR(s).");
    }
}

        // Ambil employee yang join tepat 3 bulan lalu (hari ini)
        // $employees = Employee::whereDate(
        //     'join_date', now()->subMonths(3)->toDateString()
        // )
        // ->whereIn('status', ['Active', 'Pending', 'Mutation', 'On Leave'])
        // ->get();