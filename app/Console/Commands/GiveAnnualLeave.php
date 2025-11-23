<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Employee;
use App\Models\Leavebalance;

class GiveAnnualLeave extends Command
{
  protected $signature = 'leave:anniversary';
    protected $description = 'Give annual leave on employee join date anniversary';

    // public function handle()
    // {
    //     $today = now();

    //     // Cari employee yang join_date nya hari ini
    //     $employees = Employee::whereMonth('join_date', $today->month)
    //         ->whereDay('join_date', $today->day)
    //         ->get();

    //     foreach ($employees as $employee) {

    //         // Hitung berapa tahun masa kerja
    //         $years = $today->diffInYears($employee->join_date);

    //         // Jika minimal 1 tahun
    //         if ($years >= 1) {

    //             LeaveBalance::create([
    //                 'employee_id' => $employee->id,
    //                 'year'        => $today->year,
    //                 'leave_type_id'        => '019ab050-da2c-722f-8423-2c3b9fb531ad',
    //                 'balance_days'     => 12,
    //             ]);

    //             $this->info("Leave added for {$employee->employee_name}");
    //         }
    //     }
    // }
  public function handle()
{
    $this->info("Running anniversary leave command...");

    // Ambil semua employee yang sudah kerja >= 1 tahun
    $employees = Employee::whereDate('join_date', '<=', now()->subYear())->get();

    $this->info("Eligible employees: " . $employees->count());

    foreach ($employees as $emp) {

        // Cek apakah tahun ini sudah pernah diberi (anti double award)
        $existing = LeaveBalance::where('employee_id', $emp->id)
            ->where('leave_type_id', '019ab134-12f3-73ab-a003-d096b76b25f0') // annual
            ->where('year', now()->year)
            ->first();

        if ($existing) {
            $this->info("Already given this year: {$emp->employee_name}");
            continue;
        }

        // Berikan jatah cuti tahunan 12 hari
        LeaveBalance::create([
            'employee_id' => $emp->id,
            'leave_type_id' => '019ab134-12f3-73ab-a003-d096b76b25f0',
            'balance_days' => 12,
            'year' => now()->year,
        ]);

        $this->info("Leave awarded: {$emp->employee_name}");
    }

    $this->info("Anniversary leave processing completed.");
}

}
