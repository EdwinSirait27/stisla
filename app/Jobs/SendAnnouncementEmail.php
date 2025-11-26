<?php

namespace App\Jobs;

use App\Mail\AnnouncementMail;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
class SendAnnouncementEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public $announcement;
    public $employee;

    public $tries = 3;
    public $timeout = 120;
    public $backoff = [60, 180, 300]; 

    public function __construct($announcement, $employee)
    {
        $this->announcement = $announcement;
        $this->employee = $employee;
    }

    public function handle()
    {
        try {
            Mail::to($this->employee->email)
                ->send(new AnnouncementMail($this->announcement, $this->employee));

            Log::info('Email sent successfully', [
                'announcement_id' => $this->announcement->id,
                'employee_id' => $this->employee->id,
                'email' => $this->employee->email,
            ]);
        } catch (\Exception $e) {

            Log::error('Failed to send email', [
                'announcement_id' => $this->announcement->id,
                'employee_id' => $this->employee->id,
                'email' => $this->employee->email,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
    public function failed(\Throwable $exception)
    {
        Log::error('Email job failed permanently', [
            'announcement_id' => $this->announcement->id,
            'employee_id' => $this->employee->id,
            'email' => $this->employee->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
