<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Leavebalance;

class GiveAnnualLeave extends Command
{
  protected $signature = 'leave:anniversary';
    protected $description = 'Give annual leave on employee join date anniversary';
  
//   public function handle()
// {
//     $this->info("Running anniversary leave command...");

//     // Ambil semua employee yang sudah kerja >= 1 tahun
//     $employees = Employee::whereDate('join_date', '<=', now()->subYear())->get();

//     $this->info("Eligible employees: " . $employees->count());

//     foreach ($employees as $emp) {

//         // Cek apakah tahun ini sudah pernah diberi (anti double award)
//         $existing = LeaveBalance::where('employee_id', $emp->id)
//             ->where('leave_type_id', '019ab134-12f3-73ab-a003-d096b76b25f0') // annual
//             ->where('year', now()->year)
//             ->first();

//         if ($existing) {
//             $this->info("Already given this year: {$emp->employee_name}");
//             continue;
//         }

//         // Berikan jatah cuti tahunan 12 hari
//         LeaveBalance::create([
//             'employee_id' => $emp->id,
//             'leave_type_id' => '019ab134-12f3-73ab-a003-d096b76b25f0',
//             'balance_days' => 12,
//             'year' => now()->year,
//         ]);

//         $this->info("Leave awarded: {$emp->employee_name}");
//     }

//     $this->info("Anniversary leave processing completed.");
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

        // HITUNG BALANCE DENGAN KONDISI MANAGER
        $balance = $emp->is_manager == 1 ? 15 : 12;

        // Simpan
        LeaveBalance::create([
            'employee_id'  => $emp->id,
            'leave_type_id' => '019ab134-12f3-73ab-a003-d096b76b25f0',
            'balance_days' => $balance,
            'year'         => now()->year,
        ]);

        $this->info("Leave awarded ({$balance} days): {$emp->employee_name}");
    }

    $this->info("Anniversary leave processing completed.");
}

}
