<?php

namespace App\Jobs;

use App\Mail\PayrollSlipMail;
use App\Models\Payroll;
use App\Services\PayrollSlipService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPayrollSlipJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(public string $payrollId) {}

    public function handle(PayrollSlipService $service): void
    {
        $payroll = Payroll::with(['employee.company', 'details.component'])
            ->find($this->payrollId);

        if (!$payroll) {
            Log::warning("SendPayrollSlipJob: payroll {$this->payrollId} not found");
            return;
        }

        if (!$payroll->employee->email) {
            Log::warning("SendPayrollSlipJob: no email for {$payroll->employee->employee_name}");
            return;
        }

        $pdf      = $service->generateSingle($payroll);
        $tempPath = storage_path('app/temp-slips/Slip_' . $payroll->employee->employee_pengenal . '_' . uniqid() . '.pdf');

        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $pdf->save($tempPath);

        Mail::to($payroll->employee->email)->send(new PayrollSlipMail($payroll, $tempPath));

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }

        Log::info("SendPayrollSlipJob: slip sent to {$payroll->employee->employee_name}");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SendPayrollSlipJob failed for payroll {$this->payrollId}: " . $exception->getMessage());
    }
}