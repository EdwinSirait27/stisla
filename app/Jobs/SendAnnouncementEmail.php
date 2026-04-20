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
            if (empty(trim($this->employee->email))) {
                Log::warning('Skipped sending email: empty email', [
                    'employee_id' => $this->employee->id,
                ]);
                return;
            }
            if (!filter_var($this->employee->email, FILTER_VALIDATE_EMAIL)) {
                Log::warning('Skipped sending email: invalid email', [
                    'employee_id' => $this->employee->id,
                    'email' => $this->employee->email,
                ]);
                return;
            }
            Log::info('Preparing announcement email', [
                'announcement_id' => $this->announcement->id,
                'employee_id'     => $this->employee->id,
                'email'           => $this->employee->email,
                'content_preview' => substr(strip_tags($this->announcement->content ?? ''), 0, 50),
            ]);
            Mail::to($this->employee->email)
                ->send(new AnnouncementMail($this->announcement, $this->employee));
            if (count(Mail::failures()) > 0) {
                Log::error('SMTP rejected email', [
                    'employee_id' => $this->employee->id,
                    'email'       => $this->employee->email,
                    'failures'    => Mail::failures(),
                ]);
                return;
            }
            Log::info('Email sent successfully', [
                'announcement_id' => $this->announcement->id,
                'employee_id'     => $this->employee->id,
                'email'           => $this->employee->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send announcement email', [
                'announcement_id' => $this->announcement->id,
                'employee_id'     => $this->employee->id,
                'email'           => $this->employee->email,
                'error'           => $e->getMessage(),
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
