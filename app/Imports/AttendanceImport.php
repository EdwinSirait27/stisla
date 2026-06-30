<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Services\PayrollService;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

// class AttendanceImport implements ToCollection, WithHeadingRow
// {
//     public array $skipped = [];
//     public array $updated = [];

//     public function __construct(
//         protected string $periodId,
//         protected PayrollService $payrollService
//     ) {}

//     public function collection(Collection $rows)
// {
//     $period = PayrollPeriod::findOrFail($this->periodId);

//     foreach ($rows as $row) {
//         // ← Acuan tetap employee_pengenal
//         $employee = Employee::where('employee_pengenal', $row['employee_pengenal'])
//             ->first();

//         if (!$employee) {
//             $this->skipped[] = "NIP {$row['employee_pengenal']} tidak ditemukan.";
//             continue;
//         }

//         $payroll = Payroll::where('employee_id', $employee->id)
//             ->where('payroll_period_id', $period->id)
//             ->first();

//         if (!$payroll) {
//             $this->skipped[] = "{$employee->employee_name} belum di-generate untuk periode ini.";
//             continue;
//         }

//         if ($payroll->status !== 'draft') {
//             $this->skipped[] = "{$employee->employee_name} sudah {$payroll->status}, tidak bisa diubah.";
//             continue;
//         }

//         // $attendanceDays = (int) ($row['attendance_days'] ?? 0);
//         // $statusEmp      = strtoupper($employee->status_employee);

//         // // Recalculate gross untuk DW
//         // $grossSalary = $payroll->gross_salary;
//         // if ($statusEmp === 'DW') {
//         //     $grossSalary = round((float) $payroll->daily_rate * $attendanceDays, 2);
//         // }

//         // $payroll->update([
//         //     'attendance_days' => $attendanceDays,
//         //     'gross_salary'    => $grossSalary,
//         //     // ← field baru dari Excel
//         //     'overtime_amount'  => (float) ($row['overtime_amount']  ?? 0),
//         //     'reimburse_amount' => (float) ($row['reimburse_amount'] ?? 0),
//         //     'punishment'       => (float) ($row['punishment']       ?? 0),
//         //     'punishment_so'    => (float) ($row['punishment_so']    ?? 0),
//         //     'debt'             => (float) ($row['debt']             ?? 0),
//         //     'tax'              => (float) ($row['tax']              ?? 0),
//         // ]);

//         // $this->payr¸¸¸¸¸ollService->recalculateNet($payroll);
//         ¸
//         $this->updated[] = $employee->employee_name;
//     }
// }
// }
class AttendanceImport implements ToCollection, WithHeadingRow
{
    public array $skipped = [];
    public array $updated = [];

    public function __construct(
        protected string $periodId,
        protected PayrollService $payrollService
    ) {}

    public function collection(Collection $rows)
    {
        $period = PayrollPeriod::findOrFail($this->periodId);

        foreach ($rows as $row) {
            $employee = Employee::where('employee_pengenal', $row['employee_pengenal'])
                ->first();

            if (!$employee) {
                $this->skipped[] = "NIP {$row['employee_pengenal']} tidak ditemukan.";
                continue;
            }

            $payroll = Payroll::where('employee_id', $employee->id)
                ->where('payroll_period_id', $period->id)
                ->first();
            if (!$payroll) {
                $this->skipped[] = "{$employee->employee_name} belum di-generate untuk periode ini.";
                continue;
            }

            if ($payroll->status !== 'draft') {
                $this->skipped[] = "{$employee->employee_name} sudah {$payroll->status}, tidak bisa diubah.";
                continue;
            }

               $statusEmp      = strtoupper($employee->status_employee);
            $workingDays    = (int) ($row['working_days']    ?? $payroll->working_days);
            $attendanceDays = (int) ($row['attendance_days'] ?? $payroll->attendance_days);

            // Recalculate gross untuk DW (daily_rate × attendance)
            $grossSalary = $payroll->gross_salary;
            if ($statusEmp === 'DW') {
                $grossSalary = round((float) $payroll->daily_rate * $attendanceDays, 2);
            }


            // Update hanya field manual dari Excel
            // working_days, attendance_days, overtime_amount → otomatis dari service
            $payroll->update([
                 'working_days'     => $workingDays,
                'attendance_days'  => $attendanceDays,
                'gross_salary'     => $grossSalary,
                'overtime_amount'  => (float) ($row['overtime_amount']  ?? $payroll->overtime_amount),
                 'reimburse_amount' => (float) ($row['reimburse_amount'] ?? 0),
                'punishment'       => (float) ($row['punishment']       ?? $payroll->punishment),
                'punishment_so'    => (float) ($row['punishment_so']    ?? $payroll->punishment_so),
                'debt'             => (float) ($row['debt']             ?? $payroll->debt),
                'tax'              => (float) ($row['tax']              ?? $payroll->tax),
            ]);

            $this->payrollService->recalculateNet($payroll->fresh());

            $this->updated[] = $employee->employee_name;
        }
    }
}