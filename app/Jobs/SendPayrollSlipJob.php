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

// class SendPayrollSlipJob implements ShouldQueue
// {
//     use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

//     public int $tries = 3;
//     public int $timeout = 60;

//     public function __construct(public string $payrollId) {}

//     public function handle(PayrollSlipService $service): void
//     {
//         $payroll = Payroll::with(['employee.company', 'details.component'])
//             ->find($this->payrollId);

//         if (!$payroll) {
//             Log::warning("SendPayrollSlipJob: payroll {$this->payrollId} not found");
//             return;
//         }

//         if (!$payroll->employee->email) {
//             Log::warning("SendPayrollSlipJob: no email for {$payroll->employee->employee_name}");
//             return;
//         }

//         $pdf      = $service->generateSingle($payroll);
//         $tempPath = storage_path('app/temp-slips/Slip_' . $payroll->employee->employee_pengenal . '_' . uniqid() . '.pdf');

//         if (!file_exists(dirname($tempPath))) {
//             mkdir(dirname($tempPath), 0755, true);
//         }

//         $pdf->save($tempPath);

//         Mail::to($payroll->employee->email)->send(new PayrollSlipMail($payroll, $tempPath));

//         if (file_exists($tempPath)) {
//             unlink($tempPath);
//         }

//         Log::info("SendPayrollSlipJob: slip sent to {$payroll->employee->employee_name}");
//     }

//     public function failed(\Throwable $exception): void
//     {
//         Log::error("SendPayrollSlipJob failed for payroll {$this->payrollId}: " . $exception->getMessage());
//     }
// }
class SendPayrollSlipJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;
    public array $backoff = [30, 90, 180];

    public function __construct(public string $payrollId)
    {
        $this->onQueue('payrollslip');
    }

   
    public function handle(PayrollSlipService $service): void
{
    $payroll = Payroll::with(['employee.company', 'details.component'])
        ->find($this->payrollId);

    if (!$payroll) {
        Log::warning("SendPayrollSlipJob: payroll {$this->payrollId} not found");
        return;
    }

    if (!$payroll->employee || empty(trim($payroll->employee->email ?? ''))) {
        Log::warning("SendPayrollSlipJob: no email for payroll {$this->payrollId}");
        return;
    }

    if (!filter_var($payroll->employee->email, FILTER_VALIDATE_EMAIL)) {
        Log::warning("SendPayrollSlipJob: invalid email format", [
            'payroll_id' => $this->payrollId,
            'email'      => $payroll->employee->email,
        ]);
        return;
    }

    $tempPath = null;

    try {
        ['pdf' => $pdf, 'password' => $password] = $service->generateSingle($payroll);

        $tempPath = storage_path(
            'app/temp-slips/Slip_' . $payroll->employee->employee_pengenal
            . '_' . $payroll->id . '_' . uniqid('', true) . '.pdf'
        );

        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $pdf->save($tempPath);

        Mail::to($payroll->employee->email)
            ->send(new PayrollSlipMail($payroll, $tempPath, $password));

        Log::info("SendPayrollSlipJob: slip sent successfully", [
            'payroll_id' => $this->payrollId,
            'email'      => $payroll->employee->email,
        ]);
    } catch (\Throwable $e) {
        Log::error("SendPayrollSlipJob: failed to send slip", [
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
        Log::error("SendPayrollSlipJob failed permanently for payroll {$this->payrollId}: " . $exception->getMessage());
    }
}
 // public function handle(PayrollSlipService $service): void
    // {
    //     $payroll = Payroll::with(['employee.company', 'details.component'])
    //         ->find($this->payrollId);

    //     if (!$payroll) {
    //         Log::warning("SendPayrollSlipJob: payroll {$this->payrollId} not found");
    //         return;
    //     }

    //     if (!$payroll->employee || empty(trim($payroll->employee->email ?? ''))) {
    //         Log::warning("SendPayrollSlipJob: no email for payroll {$this->payrollId}", [
    //             'employee_id' => $payroll->employee->id ?? null,
    //         ]);
    //         return;
    //     }

    //     if (!filter_var($payroll->employee->email, FILTER_VALIDATE_EMAIL)) {
    //         Log::warning("SendPayrollSlipJob: invalid email format", [
    //             'payroll_id'  => $this->payrollId,
    //             'employee_id' => $payroll->employee->id,
    //             'email'       => $payroll->employee->email,
    //         ]);
    //         return;
    //     }

    //     $tempPath = storage_path(
    //         'app/temp-slips/Slip_' . $payroll->employee->employee_pengenal
    //         . '_' . $payroll->id . '_' . uniqid('', true) . '.pdf'
    //     );

    //     if (!file_exists(dirname($tempPath))) {
    //         mkdir(dirname($tempPath), 0755, true);
    //     }

    //     try {
    //         $pdf = $service->generateSingle($payroll);
    //         $pdf->save($tempPath);

    //         Log::info("SendPayrollSlipJob: preparing to send", [
    //             'payroll_id'  => $this->payrollId,
    //             'employee_id' => $payroll->employee->id,
    //             'email'       => $payroll->employee->email,
    //         ]);

    //         Mail::to($payroll->employee->email)
    //             ->send(new PayrollSlipMail($payroll, $tempPath));

    //         Log::info("SendPayrollSlipJob: slip sent successfully", [
    //             'payroll_id'  => $this->payrollId,
    //             'employee_id' => $payroll->employee->id,
    //             'email'       => $payroll->employee->email,
    //         ]);
    //     } catch (\Throwable $e) {
    //         Log::error("SendPayrollSlipJob: failed to send slip", [
    //             'payroll_id'  => $this->payrollId,
    //             'employee_id' => $payroll->employee->id ?? null,
    //             'email'       => $payroll->employee->email ?? null,
    //             'error'       => $e->getMessage(),
    //         ]);
    //         throw $e; // tetap lempar supaya retry mechanism Laravel jalan
    //     } finally {
    //         // WAJIB: selalu hapus file PDF, baik sukses maupun gagal,
    //         // karena ini berisi data gaji sensitif (jangan sampai menumpuk di disk).
    //         if (file_exists($tempPath)) {
    //             @unlink($tempPath);
    //         }
    //     }
    // }