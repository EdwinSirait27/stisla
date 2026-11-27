<?php

namespace App\Jobs;

use App\Mail\PayrollMobileSlipMail;
use App\Models\PayrollMobile;
use App\Services\PayrollMobileSlipService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPayrollMobileSlipJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;
    public array $backoff = [30, 90, 180];

    public function __construct(public string $payrollId)
    {
        $this->onQueue('payrollslip');
    }

   
    public function handle(PayrollMobileSlipService $service): void
{
    $payroll = PayrollMobile::with(['employee.company', 'details.component'])
        ->find($this->payrollId);

    if (!$payroll) {
        Log::warning("SendPayrollMobileSlipJob: payroll {$this->payrollId} not found");
        return;
    }

    if (!in_array($payroll->status, ['approved', 'paid'], true)) {
        Log::warning("SendPayrollMobileSlipJob: payroll {$this->payrollId} masih {$payroll->status}, slip tidak dikirim");
        return;
    }
    if (!$payroll->employee || empty(trim($payroll->employee->email ?? ''))) {
        Log::warning("SendPayrollMobileSlipJob: no email for payroll {$this->payrollId}");
        return;
    }

    if (!filter_var($payroll->employee->email, FILTER_VALIDATE_EMAIL)) {
        Log::warning("SendPayrollMobileSlipJob: invalid email format", [
            'payroll_id' => $this->payrollId,
            'email'      => $payroll->employee->email,
        ]);
        return;
    }

    $tempPath = null;

    try {
        ['pdf' => $pdf, 'password' => $password] = $service->generateSingle($payroll);

        $tempPath = storage_path(
            'app/temp-slips-mobile/Slip_' . $payroll->employee->employee_pengenal
            . '_' . $payroll->id . '_' . uniqid('', true) . '.pdf'
        );

        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $pdf->save($tempPath);

        // Mail::to($payroll->employee->email)
        //     ->send(new PayrollMobileSlipMail($payroll, $tempPath, $password));
        Mail::mailer('payroll')
    ->to($payroll->employee->email)
    ->send(new PayrollMobileSlipMail($payroll, $tempPath, $password));

        Log::info("SendPayrollMobileSlipJob: slip sent successfully", [
            'payroll_id' => $this->payrollId,
            'email'      => $payroll->employee->email,
        ]);
    } catch (\Throwable $e) {
        Log::error("SendPayrollMobileSlipJob: failed to send slip", [
            'payroll_id' => $this->payrollId,
            'error'      => $e->getMessage(),
        ]);
        throw $e;
    } finally {
        if ($tempPath && file_exists($tempPath)) {
            @unlink($tempPath);
        }
    }
}

    public function failed(\Throwable $exception): void
    {
        Log::error("SendPayrollMobileSlipJob failed permanently for payroll {$this->payrollId}: " . $exception->getMessage());
    }
}