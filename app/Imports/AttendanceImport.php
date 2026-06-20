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

            $attendanceDays = (int) ($row['attendance_days'] ?? 0);
            $statusEmp      = strtoupper($employee->status_employee);

            // Recalculate gross untuk DW
            $grossSalary = $payroll->gross_salary;
            if ($statusEmp === 'DW') {
                $grossSalary = round((float) $payroll->daily_rate * $attendanceDays, 2);
            }

            $payroll->update([
                'attendance_days' => $attendanceDays,
                // 'absent_days'     => max(0, $payroll->working_days - $attendanceDays),
                'gross_salary'    => $grossSalary,
            ]);

            // Recalculate net
            $this->payrollService->recalculateNet($payroll);

            $this->updated[] = $employee->employee_name;
        }
    }
}