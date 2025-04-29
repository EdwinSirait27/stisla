<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payrolls;
use App\Jobs\SendPayslipEmail;
use Illuminate\Support\Facades\Log;

class PayrollEmailController extends Controller
{
    public function sendPayslips(Request $request)
    {
        // Validasi input
        $request->validate([
            'month_year' => 'nullable|date_format:Y-m',
        ]);
        
        $month_year = $request->input('month_year', date('Y-m'));
        
        try {
            // Ambil semua slip gaji pada bulan tersebut yang memiliki attachment
            $payrolls = Payrolls::whereNotNull('attachment_file')
                               ->whereYear('month_year', date('Y', strtotime($month_year)))
                               ->whereMonth('month_year', date('m', strtotime($month_year)))
                               ->get();
            
            $count = 0;
            foreach ($payrolls as $payroll) {
                // Dispatch job ke queue
                SendPayslipEmail::dispatch($payroll)->onQueue('emails');
                $count++;
            }
            
            Log::info("Berhasil menjadwalkan pengiriman {$count} slip gaji untuk periode {$month_year}");
            
            return response()->json([
                'success' => true,
                'message' => "{$count} slip gaji telah dijadwalkan untuk dikirim.",
            ]);
        } catch (\Exception $e) {
            Log::error("Gagal menjadwalkan pengiriman slip gaji: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => "Terjadi kesalahan: " . $e->getMessage(),
            ], 500);
        }
    }
    
    // Menambahkan method untuk melihat status pengiriman
    public function status()
    {
        $totalJobs = \DB::table('jobs')->where('queue', 'emails')->count();
        $failedJobs = \DB::table('failed_jobs')->count();
        
        return response()->json([
            'pending_jobs' => $totalJobs,
            'failed_jobs' => $failedJobs,
        ]);
    }
}
