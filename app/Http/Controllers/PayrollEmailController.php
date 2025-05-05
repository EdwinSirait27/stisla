<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payrolls;
use App\Models\Employee;
use App\Mail\PayrollMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Bus;

class PayrollEmailController extends Controller
{
    public function index()
    {
        // Ambil data periode payroll yang tersedia
        $periods = Payrolls::selectRaw('DISTINCT YEAR(month_year) as year, MONTH(month_year) as month')
                    ->whereNotNull('attachment_file')
                    ->orderByRaw('YEAR(month_year) DESC, MONTH(month_year) DESC')
                    ->get()
                    ->map(function($item) {
                        $date = Carbon::createFromDate($item->year, $item->month, 1);
                        return [
                            'year' => $item->year,
                            'month' => $item->month,
                            'formatted' => $date->format('F Y'),
                        ];
                    });

        return view('payroll.email', compact('periods'));
    }

    /**
     * Proses pengiriman email
     */
    public function send(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'period' => 'required|string', // format: month-year (contoh: 5-2025)
            'test_mode' => 'nullable|boolean',
            'specific_employees' => 'nullable|array',
            'specific_employees.*' => 'exists:employees,id',
        ]);

        // Parse periode
        list($month, $year) = explode('-', $validated['period']);
        
        // Tentukan apakah ini test mode
        $testMode = isset($validated['test_mode']) && $validated['test_mode'];
        
        // Jika ada karyawan spesifik
        $employeeFilter = isset($validated['specific_employees']) ? $validated['specific_employees'] : null;

        // Konfigurasi query
        $query = Payrolls::whereYear('month_year', $year)
                ->whereMonth('month_year', $month)
                ->whereNotNull('attachment_file')
                ->with('employee');
                
        // Filter karyawan spesifik jika ada
        if ($employeeFilter) {
            $query->whereIn('employee_id', $employeeFilter);
        }
        
        $payrolls = $query->get();
        
        // Jika tidak ada data
        if ($payrolls->isEmpty()) {
            return back()->with('error', 'Tidak ada data slip gaji yang ditemukan untuk periode ini.');
        }
        
        $totalEmails = $payrolls->count();
        
        // Jika dalam test mode
        if ($testMode) {
            // Hanya tampilkan info tentang email yang akan dikirim
            $emailsList = $payrolls->map(function($payroll) {
                return [
                    'employee' => $payroll->employee->name ?? 'Unknown',
                    'email' => $payroll->employee->email ?? 'No Email',
                    'period' => $payroll->month_year->format('F Y'),
                    'has_attachment' => !empty($payroll->attachment_file),
                ];
            });
            
            return view('payroll.email-preview', [
                'emails' => $emailsList,
                'total' => $totalEmails,
                'period' => Carbon::createFromDate($year, $month, 1)->format('F Y'),
                'test_mode' => true
            ]);
        }
        
        // Kirim email dalam queue untuk performa lebih baik
        foreach ($payrolls as $payroll) {
            if ($payroll->employee && $payroll->employee->email) {
                Mail::to($payroll->employee->email)->queue(new PayrollMail($payroll));
            }
        }
        
        return back()->with('success', "Berhasil mengirim {$totalEmails} email slip gaji ke antrian. Email akan dikirim secara bertahap.");
    }
    
    /**
     * Preview contoh email
     */
    public function preview($payrollId)
    {
        $payroll = Payrolls::with('employee')->findOrFail($payrollId);
        
        if (!$payroll->employee) {
            return back()->with('error', 'Data karyawan tidak ditemukan.');
        }
        
        $email = new PayrollMail($payroll);
        
        return $email->render();
    }
}
