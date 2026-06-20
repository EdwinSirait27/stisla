<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\Fingerprintrecap;
use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Models\PayrollPeriod;
use App\Models\PayrollComponents;
use App\Models\Roster;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollService
{
    public function generateAll(PayrollPeriod $period): array
    {
        $results = [
            'success' => [],
            'skipped' => [],
            'failed'  => [],
        ];

        // Ambil semua employee active
        $employees = Employee::whereIn('status', ['Active', 'Mutation','On Leave','Pending'])
            ->whereNotNull('employee_pengenal')
            ->get();

        foreach ($employees as $employee) {
            try {
                $result = $this->generateForEmployee($period, $employee);

                if ($result === 'skipped') {
                    $results['skipped'][] = $employee->employee_name;
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
        // ── Cek apakah sudah pernah di-generate ──
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
        $salary = EmployeeSalary::where('employee_id', $employee->id)
            ->where('effective_date', '<=', $period->period_start)
            ->latest('effective_date')
            ->first();

        if (!$salary) {
            Log::warning("PayrollService: no salary for {$employee->employee_name}");
            return 'skipped';
        }

        // ════════════════════════════════════════
        // STEP 2 — Hitung working_days dari Roster
        // ════════════════════════════════════════
       
        $workingDays = 0;

// if ($statusEmp !== 'DW') {
//     $workingDays = Roster::where('employee_id', $employee->id)
//         ->whereBetween('date', [$period->period_start, $period->period_end])
//         ->whereIn('day_type', ['Work', 'Public Holiday', 'Leave', 'Cuti Melahirkan'])
//         ->count();

//     if ($workingDays === 0) {
//         Log::warning("PayrollService: no roster for {$employee->employee_name}");
//         return 'skipped';
//     }
// }

        // ════════════════════════════════════════
        // STEP 3 — Hitung attendance_days dari Fingerprint
        // ════════════════════════════════════════
       
        $attendanceDays = 0;
$absentDays     = 0;

        // ════════════════════════════════════════
        // STEP 4 — Cek Prorate
        // ════════════════════════════════════════
        $isProrate    = false;
        $prorateDays  = null;
        $prorateRatio = null;

        if ($statusEmp !== 'DW') {
            $joinDate   = $employee->join_date
                ? Carbon::parse($employee->join_date)
                : null;
            $resignDate = $employee->resign_date
                ? Carbon::parse($employee->resign_date)
                : null;

            $periodStart = Carbon::parse($period->period_start);
            $periodEnd   = Carbon::parse($period->period_end);

            // Prorate karena JOIN di tengah periode
            $joinInPeriod = $joinDate
                && $joinDate->between($periodStart, $periodEnd)
                && $joinDate->gt($periodStart);

            // Prorate karena RESIGN di tengah periode
            $resignInPeriod = $resignDate
                && $resignDate->between($periodStart, $periodEnd)
                && $resignDate->lt($periodEnd);

            if ($joinInPeriod || $resignInPeriod) {
                $isProrate = true;

                // Hitung hari kerja aktual dari roster
                // setelah join atau sebelum resign
                $prorateDays = Roster::where('employee_id', $employee->id)
                    ->whereBetween('date', [$period->period_start, $period->period_end])
                    ->whereIn('day_type', ['Work', 'Public Holiday', 'Leave', 'Cuti Melahirkan'])
                    ->when($joinInPeriod, fn($q) =>
                        $q->where('date', '>=', $joinDate->toDateString())
                    )
                    ->when($resignInPeriod, fn($q) =>
                        $q->where('date', '<=', $resignDate->toDateString())
                    )
                    ->count();

                // Ratio = prorate_days / working_days
                $prorateRatio = $workingDays > 0
                    ? round($prorateDays / $workingDays, 4)
                    : 0;
            }
        }

        // ════════════════════════════════════════
        // STEP 5 — Hitung Gross Salary
        // ════════════════════════════════════════
       
        $basicSalary        = (float) $salary->basic_salary;
$positionAllowance  = (float) $salary->position_allowance;
$mealAllowance      = (float) $salary->meal_allowance;
$houseAllowance     = (float) $salary->house_allowance;
$transportAllowance = (float) $salary->transport_allowance;
$dailyRate          = (float) $salary->daily_rate;

if ($statusEmp === 'DW') {
    // DW: 0 dulu, akan diupdate setelah import attendance
    $grossSalary = 0;

} elseif ($isProrate) {
    $base        = $basicSalary + $positionAllowance 
                 + $mealAllowance + $houseAllowance + $transportAllowance;
    $grossSalary = round($base * $prorateRatio, 2);

} else {
    // PKWT/OJT full — tidak peduli attendance
    $grossSalary = $basicSalary + $positionAllowance 
                 + $mealAllowance + $houseAllowance + $transportAllowance;
}

        // ════════════════════════════════════════
        // STEP 6 — Simpan ke DB (dalam transaction)
        // ════════════════════════════════════════
        // DB::transaction(function () use (
        //     $period, $employee, $salary,
        //     $workingDays, $attendanceDays, $absentDays,
        //     $isProrate, $prorateDays, $prorateRatio,
        //     $basicSalary, $positionAllowance, $dailyRate,
        //     $grossSalary, $statusEmp
        DB::transaction(function () use (
    $period, $employee, $salary,                              // ← $salary ada
    $workingDays, $attendanceDays, $absentDays,
    $isProrate, $prorateDays, $prorateRatio,
    $basicSalary, $positionAllowance,
    $mealAllowance, $houseAllowance, $transportAllowance,    // ← tambah
    $dailyRate, $grossSalary, $statusEmp
        ) 
        {
            // Simpan header payroll
            $payroll = Payroll::create([
                'employee_id'        => $employee->id,
                'payroll_period_id'  => $period->id,
                'period_month'       => $period->period_month,
                'period_year'        => $period->period_year,
                'period_start'       => $period->period_start,
                'period_end'         => $period->period_end,

                // 'working_days'       => $workingDays,
                'attendance_days'    => $attendanceDays,
                // 'absent_days'        => $absentDays,

                // 'is_prorate'         => $isProrate,
                // 'prorate_days'       => $prorateDays,
                // 'prorate_ratio'      => $prorateRatio,

                'basic_salary'       => $basicSalary,
                'position_allowance' => $positionAllowance,
                'meal_allowance'      => $mealAllowance,      // ← tambah
    'house_allowance'     => $houseAllowance,     // ← tambah
    'transport_allowance' => $transportAllowance, // ← tambah
                'daily_rate'         => $dailyRate,

                'gross_salary'       => $grossSalary,
                'total_income'       => 0, // akan dihitung setelah PayrollDetail
                'total_deduction'    => 0,
                'net_salary'         => 0,

                'status'             => 'draft',
            ]);

            // Generate PayrollDetail dari komponen is_fixed = true
            $fixedComponents = PayrollComponents::where('is_fixed', true)->get();

            foreach ($fixedComponents as $component) {
                // Hitung amount BPJS otomatis
                $amount = $this->calculateComponentAmount(
                //     $component,
                //     $grossSalary,
                //     $basicSalary,
                //     $statusEmp
                // );
                 $component,
    $grossSalary,
    $basicSalary,
    $statusEmp,
    $salary // ← tambah
);

                PayrollDetail::create([
                    'payroll_id'           => $payroll->id,
                    'payroll_component_id' => $component->id,
                    'type'                 => $component->type,
                    'amount'               => $amount,
                    'note'                 => 'Auto generated',
                ]);
            }

            // Recalculate total_income, total_deduction, net_salary
            $this->recalculateNet($payroll);
        });

        return 'success';
    }

    // ════════════════════════════════════════
    // Helper — Hitung amount komponen BPJS
    // ════════════════════════════════════════
    // private function calculateComponentAmount(
    //     PayrollComponents $component,
    //     float $grossSalary,
    //     float $basicSalary,
    //     string $statusEmp
    // ): float {
    //     // DW tidak kena BPJS
    //     if ($statusEmp === 'DW') return 0;

    //     return match($component->component_name) {
    //         'BPJS KESEHATAN'    => round($grossSalary * 0.01, 2),  // 1%
    //         'BPJS KETENAGAKERJAAN'  => round($grossSalary * 0.04, 2),  // 4%
    //         default                      => 0, // komponen lain input manual
    //     };
    // }
    private function calculateComponentAmount(
    PayrollComponents $component,
    float $grossSalary,
    float $basicSalary,
    string $statusEmp,
    EmployeeSalary $salary // ← tambah parameter
): float {
    if ($statusEmp === 'DW') return 0;

    return match($component->component_name) {
        'BPJS KESEHATAN'       => (float) ($salary->bpjs_kesehatan       ?? 0), // ← dari salary
        'BPJS KETENAGAKERJAAN' => (float) ($salary->bpjs_ketenagakerjaan ?? 0), // ← dari salary
        default                => 0,
    };
}

    // ════════════════════════════════════════
    // Helper — Recalculate net salary
    // ════════════════════════════════════════
    public function recalculateNet(Payroll $payroll): void
    {
        $totalIncome = (float) $payroll->details()
            ->whereHas('component', fn($q) =>
                $q->where('type', 'Income')
                  ->where('is_employer_burden', false)
            )->sum('amount');

        $totalDeduction = (float) $payroll->details()
            ->whereHas('component', fn($q) =>
                $q->where('type', 'Deduction')
                  ->where('is_employer_burden', false)
            )->sum('amount');

        $netSalary = round(
            (float) $payroll->gross_salary
            + $totalIncome
            - $totalDeduction,
            2
        );

        $payroll->update([
            'total_income'    => $totalIncome,
            'total_deduction' => $totalDeduction,
            'net_salary'      => $netSalary,
        ]);
    }
}


// step 2
 // $workingDays = 0;

        // if ($statusEmp !== 'DW') {
        //     $workingDays = Roster::where('employee_id', $employee->id)
        //         ->whereBetween('date', [$period->period_start, $period->period_end])
        //         ->whereIn('day_type', ['Work', 'Public Holiday', 'Leave', 'Cuti Melahirkan'])
        //         ->count();

        //     if ($workingDays === 0) {
        //         Log::warning("PayrollService: no roster for {$employee->employee_name}");
        //         return 'skipped';
        //     }
        // }

        // step3
         // $eligibleForPH   = $statusEmp !== 'DW';
        // $eligibleForCuti = !in_array($statusEmp, ['DW', 'ON JOB TRAINING']);

        // // Ambil semua recap dalam periode
        // $recaps = Fingerprintrecap::where('employee_id', $employee->id)
        //     ->whereBetween('date', [$period->period_start, $period->period_end])
        //     ->get();

        // // Ambil roster dalam periode
        // $rosters = Roster::where('employee_id', $employee->id)
        //     ->whereBetween('date', [$period->period_start, $period->period_end])
        //     ->whereIn('day_type', ['Work', 'Leave', 'Cuti Melahirkan', 'Public Holiday'])
        //     ->get()
        //     ->keyBy(fn($r) => Carbon::parse($r->date)->toDateString());

        // // Tanggal cuti
        // $cutiDates = $eligibleForCuti
        //     ? $rosters->filter(fn($r) => in_array($r->day_type, ['Leave', 'Cuti Melahirkan']))
        //         ->keys()->toArray()
        //     : [];

        // // Tanggal PH
        // $phDates = $eligibleForPH
        //     ? $rosters->filter(fn($r) => $r->day_type === 'Public Holiday')
        //         ->keys()->toArray()
        //     : [];

        // // Tanggal hadir dari fingerprint
        // $countedDates = $recaps
        //     ->where('is_counted', 1)
        //     ->map(fn($r) => Carbon::parse($r->date)->toDateString())
        //     ->filter(fn($date) => isset($rosters[$date]))
        //     ->values()
        //     ->toArray();

        // // Gabung semua
        // $allAttendanceDates = array_unique(array_merge($countedDates, $phDates, $cutiDates));
        // $attendanceDays     = count($allAttendanceDates);

        // // Absent days (catatan)
        // $rosterWorkDates = $rosters
        //     ->filter(fn($r) => $r->day_type === 'Work')
        //     ->keys()->toArray();

        // $absentDays = count(array_diff($rosterWorkDates, $countedDates, $cutiDates));


        // step 5 
         // $basicSalary       = (float) $salary->basic_salary;
        // $positionAllowance = (float) $salary->position_allowance;
        // $dailyRate         = (float) $salary->daily_rate;

        // if ($statusEmp === 'DW') {
        //     // DW: harian × attendance
        //     $grossSalary = round($dailyRate * $attendanceDays, 2);

        // } elseif ($isProrate) {
        //     // PKWT/OJT prorate: base × ratio
        //     $base        = $basicSalary + $positionAllowance;
        //     $grossSalary = round($base * $prorateRatio, 2);

        // } else {
        //     // PKWT/OJT full
        //     $grossSalary = $basicSalary + $positionAllowance;
        // }