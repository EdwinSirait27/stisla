<?php

namespace App\Jobs;

use App\Mail\EmployeeProbationReminderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendProbationReminderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $employee;
    public $headHR;
    public $tries = 3;
    public $timeout = 120;
    public $backoff = [60, 180, 300];
    public function __construct($employee, $headHR)
    {
        $this->employee = $employee;
        $this->headHR   = $headHR;
        $this->onQueue('probationreminder');
    }

    public function handle()
    {
        try {
            $companyEmail = $this->headHR->employee->company_email ?? null;

            if (empty($companyEmail)) {
                Log::warning('Skipped: empty company_email', [
                    'head_hr_id' => $this->headHR->id,
                ]);
                return;
            }

            if (!filter_var($companyEmail, FILTER_VALIDATE_EMAIL)) {
                Log::warning('Skipped: invalid company_email', [
                    'head_hr_id'    => $this->headHR->id,
                    'company_email' => $companyEmail,
                ]);
                return;
            }
            Mail::to($companyEmail)
                ->send(new EmployeeProbationReminderMail($this->employee, $this->headHR));
        } catch (\Exception $e) {
            Log::error('Failed to send probation reminder', [
                'employee_id' => $this->employee->id,
                'head_hr_id'  => $this->headHR->id,
                'error'       => $e->getMessage(),
            ]);
            throw $e;
        }
    }
    public function failed(\Throwable $exception)
    {
        Log::error('Probation reminder job failed permanently', [
            'employee_id' => $this->employee->id,
            'head_hr_id'  => $this->headHR->id,
            'error'       => $exception->getMessage(),
        ]);
    }
}
