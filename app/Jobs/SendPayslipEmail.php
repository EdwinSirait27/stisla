<?php
namespace App\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\PayslipMail;
use App\Models\Payrolls;
use Illuminate\Support\Facades\Log;
class SendPayslipEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $payroll;
    public $tries = 3; // Jumlah percobaan jika gagal
    public $timeout = 60; // Batas waktu eksekusi dalam detik
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Payrolls $payroll)
    {
        $this->payroll = $payroll;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $employee = $this->payroll->employee;
        
        if ($employee && $employee->email && $this->payroll->attachment_file) {
            $attachment_path = storage_path('app/public/' . $this->payroll->attachment_path);
            
            if (file_exists($attachment_path)) {
                try {
                    Mail::to($employee->email)->send(new PayslipMail($this->payroll, $employee, $attachment_path));
                    Log::info("Email slip gaji berhasil dikirim ke: {$employee->email}");
                } catch (\Exception $e) {
                    Log::error("Gagal mengirim email slip gaji ke {$employee->email}: " . $e->getMessage());
                    throw $e; // Re-throw exception untuk trigger retry
                }
            } else {
                Log::warning("File slip gaji tidak ditemukan: {$attachment_path}");
            }
        } else {
            $missing = [];
            if (!$employee) $missing[] = "employee";
            if ($employee && !$employee->email) $missing[] = "email";
            if (!$this->payroll->attachment_path) $missing[] = "attachment_file";
            
            Log::warning("Data tidak lengkap untuk mengirim slip gaji: " . implode(', ', $missing));
        }
    }
}
