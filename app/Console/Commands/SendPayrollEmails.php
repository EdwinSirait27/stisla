<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payrolls;
use App\Models\Employee;
use App\Mail\PayrollMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendPayrollEmails extends Command
// {
//     /**
//      * The name and signature of the console command.
//      *
//      * @var string
//      */
//     protected $signature = 'payroll:send-emails 
//                             {month? : Bulan (1-12)} 
//                             {year? : Tahun (format: YYYY)} 
//                             {--id= : ID karyawan spesifik} 
//                             {--test : Hanya menampilkan info tanpa mengirim email}
//                             {--limit=50 : Batasi jumlah email yang dikirim}';

//     /**
//      * The console command description.
//      *
//      * @var string
//      */
//     protected $description = 'Mengirim email slip gaji ke semua karyawan';

//     /**
//      * Execute the console command.
//      *
//      * @return int
//      */
//     public function handle()
//     {
//         // Ambil parameter
//         $month = $this->argument('month') ?? Carbon::now()->month;
//         $year = $this->argument('year') ?? Carbon::now()->year;
//         $employeeId = $this->option('id');
//         $isTest = $this->option('test');
//         $limit = (int) $this->option('limit');

//         $this->info("Memulai pengiriman email slip gaji untuk periode {$month}/{$year}");

//         // Query payrolls
//         $query = Payrolls::whereYear('month_year', $year)
//                  ->whereMonth('month_year', $month)
//                  ->whereNotNull('attachment_path')
//                  ->with('employee');

//         // Filter berdasarkan employee ID jika ada
//         if ($employeeId) {
//             $query->where('employee_id', $employeeId);
//         }

//         // Batasi jumlah email jika diperlukan
//         if ($limit > 0) {
//             $query->limit($limit);
//         }

//         $payrolls = $query->get();
        
//         $totalPayrolls = $payrolls->count();
//         $this->info("Ditemukan {$totalPayrolls} slip gaji yang akan dikirim");

//         if ($totalPayrolls == 0) {
//             $this->error('Tidak ada data gaji yang ditemukan untuk periode ini.');
//             return 1;
//         }

//         // Konfirmasi jika tidak dalam mode test
//         if (!$isTest && !$this->confirm("Lanjutkan mengirim {$totalPayrolls} email?")) {
//             $this->info('Operasi dibatalkan.');
//             return 0;
//         }

//         $bar = $this->output->createProgressBar($totalPayrolls);
//         $bar->start();

//         $sentCount = 0;
//         $failedCount = 0;
//         $failures = [];

//         foreach ($payrolls as $payroll) {
//             try {
//                 if (!$payroll->employee || !$payroll->employee->email) {
//                     $this->newLine();
//                     $this->warn("Skipping payroll ID {$payroll->id}: Email karyawan tidak ditemukan");
//                     $failedCount++;
//                     $failures[] = "Payroll ID {$payroll->id}: Email karyawan tidak ditemukan";
//                     continue;
//                 }

//                 if ($isTest) {
//                     $this->newLine();
//                     $this->info("TEST MODE - Akan mengirim email ke: {$payroll->employee->email}");
//                 } else {
//                     Mail::to($payroll->employee->email)->send(new PayrollMail($payroll));
//                     $sentCount++;
//                 }
//             } catch (\Exception $e) {
//                 $this->newLine();
//                 $this->error("Gagal mengirim email untuk karyawan {$payroll->employee->employee_name}: {$e->getMessage()}");
//                 $failedCount++;
//                 $failures[] = "Payroll ID {$payroll->id}: {$e->getMessage()}";
//             }

//             $bar->advance();
//         }

//         $bar->finish();
//         $this->newLine(2);

//         // Tampilkan summary
//         $this->info("Pengiriman email selesai!");
//         $this->info("Total slip gaji: {$totalPayrolls}");
        
//         if (!$isTest) {
//             $this->info("Berhasil dikirim: {$sentCount}");
//             $this->info("Gagal dikirim: {$failedCount}");
            
//             if ($failedCount > 0) {
//                 $this->error("Detail kegagalan:");
//                 foreach ($failures as $failure) {
//                     $this->line(" - {$failure}");
//                 }
//             }
//         } else {
//             $this->info("Mode TEST - tidak ada email yang benar-benar dikirim");
//         }

//         return 0;
//     }
// }
{
    protected $signature = 'payroll:send-emails 
                            {month_year? : Periode payroll (format: YYYY-MM)} 
                            {--id= : ID karyawan spesifik} 
                            {--test : Hanya menampilkan info tanpa mengirim email}
                            {--limit=50 : Batasi jumlah email yang dikirim}';

    protected $description = 'Mengirim email slip gaji ke semua karyawan';

    public function handle()
    {
        $monthYearInput = $this->argument('month_year') ?? Carbon::now()->format('Y-m');
        $employeeId = $this->option('id');
        $isTest = $this->option('test');
        $limit = (int) $this->option('limit');

        // Ubah input jadi objek Carbon
        try {
            $dt = Carbon::createFromFormat('Y-m', $monthYearInput);
        } catch (\Exception $e) {
            $this->error("Format bulan tidak valid. Gunakan format YYYY-MM (contoh: 2025-04).");
            return 1;
        }

        $this->info("Memulai pengiriman email slip gaji untuk periode {$monthYearInput}");

        // Query payrolls berdasarkan tahun dan bulan
        $query = Payrolls::whereYear('month_year', $dt->year)
                         ->whereMonth('month_year', $dt->month)
                         ->whereNotNull('attachment_path')
                         ->with('employee');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        $payrolls = $query->get();
        $totalPayrolls = $payrolls->count();

        $this->info("Ditemukan {$totalPayrolls} slip gaji yang akan dikirim");

        if ($totalPayrolls == 0) {
            $this->error('Tidak ada data gaji yang ditemukan untuk periode ini.');
            return 1;
        }

        if (!$isTest && !$this->confirm("Lanjutkan mengirim {$totalPayrolls} email?")) {
            $this->info('Operasi dibatalkan.');
            return 0;
        }

        $bar = $this->output->createProgressBar($totalPayrolls);
        $bar->start();

        $sentCount = 0;
        $failedCount = 0;
        $failures = [];

        foreach ($payrolls as $payroll) {
            try {
                if (!$payroll->employee || !$payroll->employee->email) {
                    $this->newLine();
                    $this->warn("Skipping payroll ID {$payroll->id}: Email karyawan tidak ditemukan");
                    $failedCount++;
                    $failures[] = "Payroll ID {$payroll->id}: Email karyawan tidak ditemukan";
                    continue;
                }

                if ($isTest) {
                    $this->newLine();
                    $this->info("TEST MODE - Akan mengirim email ke: {$payroll->employee->email}");
                } else {
                    Mail::to($payroll->employee->email)->send(new PayrollMail($payroll));
                    $sentCount++;
                }
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Gagal mengirim email untuk karyawan {$payroll->employee->employee_name}: {$e->getMessage()}");
                $failedCount++;
                $failures[] = "Payroll ID {$payroll->id}: {$e->getMessage()}";
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Pengiriman email selesai!");
        $this->info("Total slip gaji: {$totalPayrolls}");

        if (!$isTest) {
            $this->info("Berhasil dikirim: {$sentCount}");
            $this->info("Gagal dikirim: {$failedCount}");
            if ($failedCount > 0) {
                $this->error("Detail kegagalan:");
                foreach ($failures as $failure) {
                    $this->line(" - {$failure}");
                }
            }
        } else {
            $this->info("Mode TEST - tidak ada email yang benar-benar dikirim");
        }

        return 0;
    }
}