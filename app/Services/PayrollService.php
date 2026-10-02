<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Models\PayrollPeriod;
use App\Models\Payrollcomponents;
use App\Models\EmployeeOvertimeRate;
use App\Models\Overtimesubmissions;
use App\Models\Roster;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollService
{
    const WORKING_DAY_TYPES = [
        'Work',
        'Public Holiday',
        'Leave',
        'Cuti Melahirkan',
        'TOIL Off',
        'Sick',
    ];

    public function generateAll(PayrollPeriod $period): array
    {
        $results = [
            'success' => [],
            'warning' => [],
            'skipped' => [],
            'failed'  => [],
        ];

        // Karyawan aktif + karyawan yang resign DI DALAM periode ini
        // (tetap berhak atas gaji terakhir sesuai kehadiran)
        $employees = Employee::whereNotNull('employee_pengenal')
            ->where(function ($q) use ($period) {
                $q->whereIn('status', ['Active', 'Mutation', 'On Leave', 'Pending'])
                    ->orWhere(function ($q) use ($period) {
                        $q->where('status', 'Resign')
                            ->whereDate('end_date', '>=', $period->period_start)
                            ->whereDate('end_date', '<=', $period->period_end);
                    });
            })
            ->get();

        foreach ($employees as $employee) {
            try {
                $result = $this->generateForEmployee($period, $employee);

                if ($result === 'skipped') {
                    $results['skipped'][] = $employee->employee_name;
                } elseif ($result === 'warning') {
                    $results['success'][] = $employee->employee_name;
                    $results['warning'][] = $employee->employee_name;
                } else {
                    $results['success'][] = $employee->employee_name;
                }
            } catch (\Exception $e) {
                Log::error("PayrollService: failed for {$employee->employee_name}", [
                    'error' => $e->getMessage(),
                ]);
                $results['failed'][] = $employee->employee_name;
            }
        }

        return $results;
    }

    public function generateForEmployee(PayrollPeriod $period, Employee $employee): string
    {
        $exists = Payroll::where('employee_id', $employee->id)
            ->where('payroll_period_id', $period->id)
            ->exists();

        if ($exists) {
            Log::info("PayrollService: skip {$employee->employee_name} — sudah di-generate");
            return 'skipped';
        }

        $statusEmp = strtoupper($employee->status_employee);

        // ════════════════════════════════════════
        // STEP 1 — Ambil salary
        // ════════════════════════════════════════
        // Pakai salary terakhir yang berlaku s/d AKHIR periode, supaya
        // karyawan baru / kenaikan gaji di tengah periode ikut terhitung
        $salary = EmployeeSalary::where('employee_id', $employee->id)
            ->whereDate('effective_date', '<=', $period->period_end)
            ->latest('effective_date')
            ->first();

        if (!$salary) {
            Log::warning("PayrollService: no salary for {$employee->employee_name}");
            return 'skipped';
        }

        // ════════════════════════════════════════
        // STEP 2 — Hitung working_days dari kalender
        // Total hari periode - jumlah hari Minggu
        // Fleksibel untuk semua bulan termasuk Februari
        // DW tidak butuh working_days
        // ════════════════════════════════════════
        $workingDays = 0;

        if ($statusEmp !== 'DW') {
            $workingDays = $this->getDefaultWorkingDays(
                $period->period_start,
                $period->period_end
            );
        }

        // ════════════════════════════════════════
        // STEP 3 — attendance_days = 0 dulu
        // diisi via import Excel
        // ════════════════════════════════════════
        // $attendanceDays = 0;
        // $absentDays     = 0;
        $attendanceDays = 0;
        $absentDays     = 0;
        $warnings       = [];

        $archive = \App\Models\Fingerprintrecaparchive::where('employee_id', $employee->id)
            ->whereDate('period_start', $period->period_start)
            ->whereDate('period_end', $period->period_end)
            ->first();

        if ($archive) {
            $attendanceDays = (int) ($archive->total_hari_kerja ?? 0);
            Log::info("PayrollService: attendance from archive for {$employee->employee_name} = {$attendanceDays}");
        } else {
            Log::warning("PayrollService: no archive for {$employee->employee_name}, attendance_days = 0");
            $warnings[] = 'Arsip absensi belum ada, attendance_days = 0 (isi via import/edit).';
        }

        // Kehadiran tidak boleh melebihi hari kerja (non-DW) → cegah gaji > 100%
        if ($statusEmp !== 'DW' && $attendanceDays > $workingDays) {
            $warnings[] = "Attendance {$attendanceDays} melebihi working days {$workingDays}, dibatasi ke {$workingDays}.";
            $attendanceDays = $workingDays;
        }

// ════════════════════════════════════════
// STEP 3.5 — Hitung overtime_amount dari Overtimesubmissions
// Cash + Approved + date dalam periode payroll
// Setelah generate → update ToilBalance status = paid
// ════════════════════════════════════════
$overtimeAmount      = 0;
$overtimeSubmissions = collect();

$overtimeRate = \App\Models\EmployeeOvertimeRate::where('employee_id', $employee->id)
    ->latest()
    ->first();

if ($overtimeRate) {
    $ratePerHour = (float) $overtimeRate->rate_per_hour;

    $overtimeSubmissions = \App\Models\Overtimesubmissions::where('employee_id', $employee->id)
        ->where('compensation_type', 'Cash')
        ->where('status', 'Approved')
        ->whereDate('date', '>=', $period->period_start) // ← hapus whereHas balance
        ->whereDate('date', '<=', $period->period_end)
        ->with('balance')
        ->get();

    $totalOvertimeHours = (float) $overtimeSubmissions->sum('total_hours');
    $overtimeAmount     = round($ratePerHour * $totalOvertimeHours, 2);

    Log::info("PayrollService: overtime for {$employee->employee_name} = {$overtimeAmount} ({$totalOvertimeHours} hours × {$ratePerHour})");
} else {
    Log::warning("PayrollService: no overtime rate for {$employee->employee_name}");
}

        // ════════════════════════════════════════
        // STEP 4 — Ambil nilai salary
        // ════════════════════════════════════════
        $basicSalary        = (float) $salary->basic_salary;
        $positionAllowance  = (float) $salary->position_allowance;
        $mealAllowance      = (float) $salary->meal_allowance;
        $houseAllowance     = (float) $salary->house_allowance;
        $transportAllowance = (float) $salary->transport_allowance;
        $dailyRate          = (float) $salary->daily_rate;

        // ════════════════════════════════════════
        // STEP 5 — Hitung Gross Salary
        // PKWT/OJT: basic + position saja
        //   meal/house/transport masuk PayrollDetail (income)
        //   recalculateNet: (gross/working_days) × attendance
        // DW: 0 dulu → daily_rate × attendance setelah import
        // ════════════════════════════════════════
        // if ($statusEmp === 'DW') {
        //     $grossSalary = 0;
        // } else {
        //     $grossSalary = $basicSalary + $positionAllowance;
        // }
        if ($statusEmp === 'DW') {
    // Kalau archive sudah ada → langsung hitung gross
    $grossSalary = $attendanceDays > 0
        ? round((float) $salary->daily_rate * $attendanceDays, 2)
        : 0;
} else {
    $grossSalary = $basicSalary + $positionAllowance;
}

        // ════════════════════════════════════════
        // STEP 6 — Simpan ke DB
        // ════════════════════════════════════════
        DB::transaction(function () use (
            $period,
            $employee,
            $salary,
            $workingDays,
            $attendanceDays,
            $absentDays,
            $basicSalary,
            $positionAllowance,
            $mealAllowance,
            $houseAllowance,
            $transportAllowance,
            $dailyRate,
            $grossSalary,
            $statusEmp,
            $overtimeAmount,
            $overtimeSubmissions,
            $warnings
        ) {
            $payroll = Payroll::create([
                'employee_id'         => $employee->id,
                'payroll_period_id'   => $period->id,
                'period_month'        => $period->period_month,
                'period_year'         => $period->period_year,
                'period_start'        => $period->period_start,
                'period_end'          => $period->period_end,

                'working_days'        => $workingDays,    // ← dari kalender
                'attendance_days'     => $attendanceDays, // ← 0 dulu
                'absent_days'         => $absentDays,
                'overtime_amount' => $overtimeAmount,

                'basic_salary'        => $basicSalary,
                'position_allowance'  => $positionAllowance,
                'meal_allowance'      => $mealAllowance,
                'house_allowance'     => $houseAllowance,
                'transport_allowance' => $transportAllowance,
                'daily_rate'          => $dailyRate,

                'gross_salary'        => $grossSalary,
                'total_income'        => 0,
                'total_deduction'     => 0,
                'net_salary'          => 0,
                'status'              => 'draft',
                'note'                => $warnings ? implode(' ', $warnings) : null,
            ]);
     // ← Update ToilBalance dan Overtimesubmissions
     // (dikembalikan lagi oleh releaseOvertime() kalau draft dihapus)
foreach ($overtimeSubmissions as $submission) {
    // Update ToilBalance → paid
    if ($submission->balance) {
        $submission->balance->update([
            'status'      => 'paid',
            'paid_at'     => now(),
            'paid_period' => $period->period_label,
        ]);
    }

    // Update Overtimesubmissions → locked (tidak bisa edit/delete)
    $submission->update([
        'status' => 'Approved HR',
    ]);
}


            // Generate PayrollDetail dari komponen is_fixed = true:
            // BPJS KESEHATAN, BPJS KETENAGAKERJAAN (Deduction)
            // MEAL ALLOWANCE, HOUSE ALLOWANCE, TRANSPORT ALLOWANCE (Income)
            $fixedComponents = Payrollcomponents::where('is_fixed', true)->get();

            foreach ($fixedComponents as $component) {
                $amount = $this->calculateComponentAmount(
                    $component,
                    $grossSalary,
                    $basicSalary,
                    $statusEmp,
                    $salary
                );

                PayrollDetail::create([
                    'payroll_id'           => $payroll->id,
                    'payroll_component_id' => $component->id,
                    'type'                 => $component->type,
                    'amount'               => $amount,
                    'note'                 => 'Auto generated',
                ]);
            }

            $this->recalculateNet($payroll);
        });

        return $warnings ? 'warning' : 'success';
    }

    // ════════════════════════════════════════
    // Kembalikan Overtimesubmissions & ToilBalance yang dikunci saat generate,
    // supaya overtime ikut terhitung lagi kalau payroll draft di-generate ulang.
    // Panggil SEBELUM payroll draft dihapus.
    // ════════════════════════════════════════
    public function releaseOvertime(Payroll $payroll): void
    {
        $periodLabel = $payroll->period?->period_label;

        $submissions = Overtimesubmissions::where('employee_id', $payroll->employee_id)
            ->where('compensation_type', 'Cash')
            ->where('status', 'Approved HR')
            ->whereDate('date', '>=', $payroll->period_start)
            ->whereDate('date', '<=', $payroll->period_end)
            ->with('balance')
            ->get();

        foreach ($submissions as $submission) {
            $balance = $submission->balance;
            if ($balance && $balance->status === 'paid'
                && (!$periodLabel || $balance->paid_period === $periodLabel)) {
                $balance->update([
                    'status'      => 'active',
                    'paid_at'     => null,
                    'paid_period' => null,
                ]);
            }

            $submission->update(['status' => 'Approved']);
        }
    }

    // ════════════════════════════════════════
    // Helper — Hitung working days dari kalender
    // Total hari periode - jumlah hari Minggu
    // Contoh: Juni 2026 (26 Mei - 25 Jun) = 26 hari
    //         Feb 2027  (26 Jan - 25 Feb)  = 22 hari
    // ════════════════════════════════════════
    private function getDefaultWorkingDays(string $periodStart, string $periodEnd): int
    {
        $start   = Carbon::parse($periodStart);
        $end     = Carbon::parse($periodEnd);
        $working = 0;

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($date->dayOfWeek !== Carbon::SUNDAY) {
                $working++;
            }
        }

        return $working;
    }

    // ════════════════════════════════════════
    // Helper — Hitung amount komponen is_fixed
    // BPJS → nominal dari EmployeeSalary
    // MEAL/HOUSE/TRANSPORT → nominal dari EmployeeSalary
    // ════════════════════════════════════════
    private function calculateComponentAmount(
        Payrollcomponents $component,
        float $grossSalary,
        float $basicSalary,
        string $statusEmp,
        EmployeeSalary $salary
    ): float {
        return match ($component->component_name) {
            'BPJS KESEHATAN'       => (float) ($salary->bpjs_kesehatan       ?? 0),
            'BPJS KETENAGAKERJAAN' => (float) ($salary->bpjs_ketenagakerjaan ?? 0),
            'MEAL ALLOWANCE'       => (float) ($salary->meal_allowance       ?? 0),
            'HOUSE ALLOWANCE'      => (float) ($salary->house_allowance      ?? 0),
            'TRANSPORT ALLOWANCE'  => (float) ($salary->transport_allowance  ?? 0),
            default                => 0,
        };
    }
    // ════════════════════════════════════════
    // Helper — Recalculate net salary
    //
    // PKWT/OJT:
    //   gross_actual = floor((basic+position) / working_days × attendance)
    //   net = gross_actual
    //       + meal + house + transport (dari PayrollDetail)
    //       + overtime + reimburse
    //       - bpjs_kesehatan - bpjs_ketenagakerjaan (dari PayrollDetail)
    //       - punishment - punishment_so - debt - tax
    //
    // DW:
    //   gross_actual = daily_rate × attendance
    //   net = gross_actual
    //       + meal + house + transport (dari PayrollDetail)
    //       + overtime + reimburse
    //       - bpjs_kesehatan - bpjs_ketenagakerjaan (dari PayrollDetail)
    //       - punishment - punishment_so - debt - tax
    // ════════════════════════════════════════
    public function recalculateNet(Payroll $payroll): void
    {
        $statusEmp = strtoupper($payroll->employee->status_employee);

        // Income dari PayrollDetail (MEAL, HOUSE, TRANSPORT)
        $incomeFromDetails = (float) $payroll->details()
            ->whereHas(
                'component',
                fn($q) =>
                $q->where('type', 'Income')
                    ->where('is_employer_burden', false)
            )->sum('amount');

        // Deduction dari PayrollDetail (BPJS)
        $deductionFromDetails = (float) $payroll->details()
            ->whereHas(
                'component',
                fn($q) =>
                $q->where('type', 'Deduction')
                    ->where('is_employer_burden', false)
            )->sum('amount');

        // Manual income dari kolom payrolls
        $manualIncome = (float) $payroll->overtime_amount
            + (float) $payroll->reimburse_amount;

        // Manual deduction dari kolom payrolls
        $manualDeduction = (float) $payroll->punishment
            + (float) $payroll->punishment_so
            + (float) $payroll->debt
            + (float) $payroll->tax;

        if ($statusEmp === 'DW') {
            // DW: daily_rate × attendance_days
            $grossActual = (float) $payroll->daily_rate
                * (int) $payroll->attendance_days;
        } else {
            // PKWT/OJT: floor((basic+position) / working_days × attendance)
            $workingDays = $payroll->working_days > 0
                ? $payroll->working_days
                : $this->getDefaultWorkingDays(
                    $payroll->period_start,
                    $payroll->period_end
                );

            // Attendance dibatasi max working_days → gaji pokok maksimal 100%
            $attendance = min((int) $payroll->attendance_days, (int) $workingDays);

            $grossActual = $attendance > 0
                ? floor(
                    ((float) $payroll->gross_salary / $workingDays)
                        * $attendance
                )
                : 0;
        }
        $totalIncome    = $incomeFromDetails + $manualIncome;
        $totalDeduction = $deductionFromDetails + $manualDeduction;

        $netSalary = round(
            $grossActual + $totalIncome - $totalDeduction,
            2
        );
        $payroll->update([
            'total_income'    => $totalIncome,
            'total_deduction' => $totalDeduction,
            'net_salary'      => $netSalary,
        ]);
    }
}
// class PayrollService
// {
//     const WORKING_DAY_TYPES = [
//         'Work',
//         'Public Holiday',
//         'Leave',
//         'Cuti Melahirkan',
//         'TOIL Off',
//         'Sick',
//     ];

//     public function generateAll(PayrollPeriod $period): array
//     {
//         $results = [
//             'success' => [],
//             'skipped' => [],
//             'failed'  => [],
//         ];

//         $employees = Employee::whereIn('status', ['Active', 'Mutation', 'On Leave', 'Pending'])
//             ->whereNotNull('employee_pengenal')
//             ->get();

//         foreach ($employees as $employee) {
//             try {
//                 $result = $this->generateForEmployee($period, $employee);

//                 if ($result === 'skipped') {
//                     $results['skipped'][] = $employee->employee_name;
//                 } else {
//                     $results['success'][] = $employee->employee_name;
//                 }
//             } catch (\Exception $e) {
//                 Log::error("PayrollService: failed for {$employee->employee_name}", [
//                     'error' => $e->getMessage(),
//                 ]);
//                 $results['failed'][] = $employee->employee_name;
//             }
//         }

//         return $results;
//     }

//     public function generateForEmployee(PayrollPeriod $period, Employee $employee): string
//     {
//         $exists = Payroll::where('employee_id', $employee->id)
//             ->where('payroll_period_id', $period->id)
//             ->exists();

//         if ($exists) {
//             Log::info("PayrollService: skip {$employee->employee_name} — sudah di-generate");
//             return 'skipped';
//         }

//         $statusEmp = strtoupper($employee->status_employee);

//         // ════════════════════════════════════════
//         // STEP 1 — Ambil salary
//         // ════════════════════════════════════════
//         $salary = EmployeeSalary::where('employee_id', $employee->id)
//             ->where('effective_date', '<=', $period->period_start)
//             ->latest('effective_date')
//             ->first();

//         if (!$salary) {
//             Log::warning("PayrollService: no salary for {$employee->employee_name}");
//             return 'skipped';
//         }

//         // ════════════════════════════════════════
//         // STEP 2 — Hitung working_days dari kalender
//         // Total hari periode - jumlah hari Minggu
//         // Fleksibel untuk semua bulan termasuk Februari
//         // DW tidak butuh working_days
//         // ════════════════════════════════════════
//         $workingDays = 0;

//         if ($statusEmp !== 'DW') {
//             $workingDays = $this->getDefaultWorkingDays(
//                 $period->period_start,
//                 $period->period_end
//             );
//         }

//         // ════════════════════════════════════════
//         // STEP 3 — attendance_days = 0 dulu
//         // diisi via import Excel
//         // ════════════════════════════════════════
//         // $attendanceDays = 0;
//         // $absentDays     = 0;
//         $attendanceDays = 0;
// $absentDays     = 0;

// $archive = \App\Models\Fingerprintrecaparchive::where('employee_id', $employee->id)
//     ->whereDate('period_start', $period->period_start)
//     ->whereDate('period_end', $period->period_end)
//     ->first();

// if ($archive) {
//     $attendanceDays = (int) ($archive->total_hari_kerja ?? 0);
//     Log::info("PayrollService: attendance from archive for {$employee->employee_name} = {$attendanceDays}");
// } else {
//     Log::warning("PayrollService: no archive for {$employee->employee_name}, attendance_days = 0");
// }

//         // ════════════════════════════════════════
//         // STEP 4 — Ambil nilai salary
//         // ════════════════════════════════════════
//         $basicSalary        = (float) $salary->basic_salary;
//         $positionAllowance  = (float) $salary->position_allowance;
//         $mealAllowance      = (float) $salary->meal_allowance;
//         $houseAllowance     = (float) $salary->house_allowance;
//         $transportAllowance = (float) $salary->transport_allowance;
//         $dailyRate          = (float) $salary->daily_rate;

//         // ════════════════════════════════════════
//         // STEP 5 — Hitung Gross Salary
//         // PKWT/OJT: basic + position saja
//         //   meal/house/transport masuk PayrollDetail (income)
//         //   recalculateNet: (gross/working_days) × attendance
//         // DW: 0 dulu → daily_rate × attendance setelah import
//         // ════════════════════════════════════════
//         // if ($statusEmp === 'DW') {
//         //     $grossSalary = 0;
//         // } else {
//         //     $grossSalary = $basicSalary + $positionAllowance;
//         // }
//         if ($statusEmp === 'DW') {
//     // Kalau archive sudah ada → langsung hitung gross
//     $grossSalary = $attendanceDays > 0
//         ? round((float) $salary->daily_rate * $attendanceDays, 2)
//         : 0;
// } else {
//     $grossSalary = $basicSalary + $positionAllowance;
// }

//         // ════════════════════════════════════════
//         // STEP 6 — Simpan ke DB
//         // ════════════════════════════════════════
//         DB::transaction(function () use (
//             $period,
//             $employee,
//             $salary,
//             $workingDays,
//             $attendanceDays,
//             $absentDays,
//             $basicSalary,
//             $positionAllowance,
//             $mealAllowance,
//             $houseAllowance,
//             $transportAllowance,
//             $dailyRate,
//             $grossSalary,
//             $statusEmp
//         ) {
//             $payroll = Payroll::create([
//                 'employee_id'         => $employee->id,
//                 'payroll_period_id'   => $period->id,
//                 'period_month'        => $period->period_month,
//                 'period_year'         => $period->period_year,
//                 'period_start'        => $period->period_start,
//                 'period_end'          => $period->period_end,

//                 'working_days'        => $workingDays,    // ← dari kalender
//                 'attendance_days'     => $attendanceDays, // ← 0 dulu
//                 'absent_days'         => $absentDays,

//                 'basic_salary'        => $basicSalary,
//                 'position_allowance'  => $positionAllowance,
//                 'meal_allowance'      => $mealAllowance,
//                 'house_allowance'     => $houseAllowance,
//                 'transport_allowance' => $transportAllowance,
//                 'daily_rate'          => $dailyRate,

//                 'gross_salary'        => $grossSalary,
//                 'total_income'        => 0,
//                 'total_deduction'     => 0,
//                 'net_salary'          => 0,
//                 'status'              => 'draft',
//             ]);

//             // Generate PayrollDetail dari komponen is_fixed = true:
//             // BPJS KESEHATAN, BPJS KETENAGAKERJAAN (Deduction)
//             // MEAL ALLOWANCE, HOUSE ALLOWANCE, TRANSPORT ALLOWANCE (Income)
//             $fixedComponents = PayrollComponents::where('is_fixed', true)->get();

//             foreach ($fixedComponents as $component) {
//                 $amount = $this->calculateComponentAmount(
//                     $component,
//                     $grossSalary,
//                     $basicSalary,
//                     $statusEmp,
//                     $salary
//                 );

//                 PayrollDetail::create([
//                     'payroll_id'           => $payroll->id,
//                     'payroll_component_id' => $component->id,
//                     'type'                 => $component->type,
//                     'amount'               => $amount,
//                     'note'                 => 'Auto generated',
//                 ]);
//             }

//             $this->recalculateNet($payroll);
//         });

//         return 'success';
//     }

//     // ════════════════════════════════════════
//     // Helper — Hitung working days dari kalender
//     // Total hari periode - jumlah hari Minggu
//     // Contoh: Juni 2026 (26 Mei - 25 Jun) = 26 hari
//     //         Feb 2027  (26 Jan - 25 Feb)  = 22 hari
//     // ════════════════════════════════════════
//     private function getDefaultWorkingDays(string $periodStart, string $periodEnd): int
//     {
//         $start   = Carbon::parse($periodStart);
//         $end     = Carbon::parse($periodEnd);
//         $working = 0;

//         for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
//             if ($date->dayOfWeek !== Carbon::SUNDAY) {
//                 $working++;
//             }
//         }

//         return $working;
//     }

//     // ════════════════════════════════════════
//     // Helper — Hitung amount komponen is_fixed
//     // BPJS → nominal dari EmployeeSalary
//     // MEAL/HOUSE/TRANSPORT → nominal dari EmployeeSalary
//     // ════════════════════════════════════════
//     private function calculateComponentAmount(
//         PayrollComponents $component,
//         float $grossSalary,
//         float $basicSalary,
//         string $statusEmp,
//         EmployeeSalary $salary
//     ): float {
//         return match ($component->component_name) {
//             'BPJS KESEHATAN'       => (float) ($salary->bpjs_kesehatan       ?? 0),
//             'BPJS KETENAGAKERJAAN' => (float) ($salary->bpjs_ketenagakerjaan ?? 0),
//             'MEAL ALLOWANCE'       => (float) ($salary->meal_allowance       ?? 0),
//             'HOUSE ALLOWANCE'      => (float) ($salary->house_allowance      ?? 0),
//             'TRANSPORT ALLOWANCE'  => (float) ($salary->transport_allowance  ?? 0),
//             default                => 0,
//         };
//     }
//     // ════════════════════════════════════════
//     // Helper — Recalculate net salary
//     //
//     // PKWT/OJT:
//     //   gross_actual = floor((basic+position) / working_days × attendance)
//     //   net = gross_actual
//     //       + meal + house + transport (dari PayrollDetail)
//     //       + overtime + reimburse
//     //       - bpjs_kesehatan - bpjs_ketenagakerjaan (dari PayrollDetail)
//     //       - punishment - punishment_so - debt - tax
//     //
//     // DW:
//     //   gross_actual = daily_rate × attendance
//     //   net = gross_actual
//     //       + meal + house + transport (dari PayrollDetail)
//     //       + overtime + reimburse
//     //       - bpjs_kesehatan - bpjs_ketenagakerjaan (dari PayrollDetail)
//     //       - punishment - punishment_so - debt - tax
//     // ════════════════════════════════════════
//     public function recalculateNet(Payroll $payroll): void
//     {
//         $statusEmp = strtoupper($payroll->employee->status_employee);

//         // Income dari PayrollDetail (MEAL, HOUSE, TRANSPORT)
//         $incomeFromDetails = (float) $payroll->details()
//             ->whereHas(
//                 'component',
//                 fn($q) =>
//                 $q->where('type', 'Income')
//                     ->where('is_employer_burden', false)
//             )->sum('amount');

//         // Deduction dari PayrollDetail (BPJS)
//         $deductionFromDetails = (float) $payroll->details()
//             ->whereHas(
//                 'component',
//                 fn($q) =>
//                 $q->where('type', 'Deduction')
//                     ->where('is_employer_burden', false)
//             )->sum('amount');

//         // Manual income dari kolom payrolls
//         $manualIncome = (float) $payroll->overtime_amount
//             + (float) $payroll->reimburse_amount;

//         // Manual deduction dari kolom payrolls
//         $manualDeduction = (float) $payroll->punishment
//             + (float) $payroll->punishment_so
//             + (float) $payroll->debt
//             + (float) $payroll->tax;

//         if ($statusEmp === 'DW') {
//             // DW: daily_rate × attendance_days
//             $grossActual = (float) $payroll->daily_rate
//                 * (int) $payroll->attendance_days;
//         } else {
//             // PKWT/OJT: floor((basic+position) / working_days × attendance)
//             $workingDays = $payroll->working_days > 0
//                 ? $payroll->working_days
//                 : $this->getDefaultWorkingDays(
//                     $payroll->period_start,
//                     $payroll->period_end
//                 );

//             $grossActual = $payroll->attendance_days > 0
//                 ? floor(
//                     ((float) $payroll->gross_salary / $workingDays)
//                         * (int) $payroll->attendance_days
//                 )
//                 : 0;
//         }
//         $totalIncome    = $incomeFromDetails + $manualIncome;
//         $totalDeduction = $deductionFromDetails + $manualDeduction;

//         $netSalary = round(
//             $grossActual + $totalIncome - $totalDeduction,
//             2
//         );
//         $payroll->update([
//             'total_income'    => $totalIncome,
//             'total_deduction' => $totalDeduction,
//             'net_salary'      => $netSalary,
//         ]);
//     }
// }
