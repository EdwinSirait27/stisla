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
    public array $notes = [];

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

            $statusEmp = strtoupper($employee->status_employee);

            // Ambil angka dari Excel. Sel kosong = pakai nilai payroll yang sekarang.
            // Teks seperti "1.500.000" ditolak: (float) "1.500.000" = 1.5, bukan 1,5 juta.
            $invalid = [];
            $num = function (string $field, $current, bool $integer = false) use ($row, &$invalid) {
                $value = $row[$field] ?? null;
                if ($value === null || (is_string($value) && trim($value) === '')) {
                    return $current;
                }
                if (!is_numeric($value) || $value < 0) {
                    $invalid[] = $field . ' "' . $value . '"';
                    return $current;
                }
                return $integer ? (int) $value : round((float) $value, 2);
            };

            $workingDays    = $num('working_days', (int) $payroll->working_days, true);
            $attendanceDays = $num('attendance_days', (int) $payroll->attendance_days, true);
            $values = [
                'overtime_amount'  => $num('overtime_amount', (float) $payroll->overtime_amount),
                'reimburse_amount' => $num('reimburse_amount', (float) $payroll->reimburse_amount),
                'punishment'       => $num('punishment', (float) $payroll->punishment),
                'punishment_so'    => $num('punishment_so', (float) $payroll->punishment_so),
                'debt'             => $num('debt', (float) $payroll->debt),
                'tax'              => $num('tax', (float) $payroll->tax),
            ];

            if ($invalid) {
                $this->skipped[] = "{$employee->employee_name}: isi angka tanpa titik/koma/simbol, cek kolom " . implode(', ', $invalid) . '.';
                continue;
            }

            // Non-DW: attendance > working days tetap diimpor apa adanya (kolom lain di baris
            // yang sama, mis. overtime, ikut masuk). Gaji pokok tetap maksimal 100% karena
            // Payroll::earnedSalary() memakai min(attendance, working days).
            if ($statusEmp !== 'DW' && $attendanceDays > $workingDays) {
                $this->notes[] = "{$employee->employee_name}: attendance {$attendanceDays} melebihi working days {$workingDays}, gaji pokok dibayar maksimal {$workingDays}/{$workingDays} hari (100%).";
            }

            // DW: gross = daily_rate × attendance (disimpan supaya kolom Gross ikut benar)
            $grossSalary = $payroll->gross_salary;
            if ($statusEmp === 'DW') {
                $grossSalary = round((float) $payroll->daily_rate * $attendanceDays, 2);
            }

            $payroll->update(array_merge([
                'working_days'    => $workingDays,
                'attendance_days' => $attendanceDays,
                'gross_salary'    => $grossSalary,
            ], $values));

            $this->payrollService->recalculateNet($payroll->fresh());

            $this->updated[] = $employee->employee_name;
        }
    }
}