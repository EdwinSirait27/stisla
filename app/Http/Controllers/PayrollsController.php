<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payrolls;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Rules\NoXSSInput;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use App\Imports\PayrollsImport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use App\Exports\PayrollExport;
class PayrollsController extends Controller
{
     public function export(Request $request)
    {
        $monthYear = $request->month_year; // contoh: 2025-01

        return Excel::download(
            new PayrollExport($monthYear),
            'payroll_' . ($monthYear ?? 'all') . '.xlsx'
        );
    }
    public function index()
    {
        return view('pages.Payrolls.Payrolls');
    }
    public function generateAll()
    {
        ini_set('max_execution_time', 420);
        $currentCarbon = Carbon::now();

        $payrolls = Payrolls::with('employee')
            ->whereMonth('created_at', $currentCarbon->month)
            ->whereYear('created_at', $currentCarbon->year)
            ->get();
        foreach ($payrolls as $payroll) {

            try {
                $carbonMonthYear = Carbon::parse($payroll->month_year);
            } catch (\Exception $e) {
                Log::warning("Gagal parse month_year untuk payroll ID {$payroll->id}: " . $e->getMessage());
                $carbonMonthYear = now(); // fallback jika gagal
            }
            // $allowance = $payroll->allowance ? ($payroll->allowance) : null;
            // $basic_salary = $payroll->basic_salary ? ($payroll->basic_salary) : null;
            $reamburse = $payroll->reamburse ? ($payroll->reamburse) : null;
            $bonus = $payroll->bonus ? ($payroll->bonus) : null;
            $tax = $payroll->tax ? ($payroll->tax) : null;
            $house_allowance = $payroll->house_allowance ? ($payroll->house_allowance) : null;
            $meal_allowance = $payroll->meal_allowance ? ($payroll->meal_allowance) : null;
            $transport_allowance = $payroll->transport_allowance ? ($payroll->transport_allowance) : null;
            $deductions = $payroll->deductions ? ($payroll->deductions) : null;
            $salary = $payroll->salary ? ($payroll->salary) : null;
            $overtime = $payroll->overtime ? ($payroll->overtime) : null;
            $overtime_deduction = $payroll->overtime_deduction ? ($payroll->overtime_deduction) : null;
            $late_fine = $payroll->late_fine ? ($payroll->late_fine) : null;
            $bpjs_ket = $payroll->bpjs_ket ? ($payroll->bpjs_ket) : null;
            $bpjs_kes = $payroll->bpjs_kes ? ($payroll->bpjs_kes) : null;
            $debt = $payroll->debt ? ($payroll->debt) : null;
            $daily_allowance = $payroll->daily_allowance ? ($payroll->daily_allowance) : null;
            $punishment = $payroll->punishment ? ($payroll->punishment) : null;
            $period = $payroll->period ?? '-';
            $created_at = $payroll->created_at ? $payroll->created_at->format('Y-m-d') : '-';
            $attendance = $payroll->attendance ?? 0;
            $period = $payroll->period ?? '-';
            $month_year = $payroll->month_year ?? '-';
            $deductions = $payroll->deductions ?? '-';
            $salary = $payroll->salary ?? '-';
            // $gross_salary = $payroll->gross_salary ?? '-';
            $take_home = $payroll->take_home ?? '-';
            $data = [
                'payroll' => $payroll,
                'attendance' => $attendance,
                // 'allowance' => $allowance,
                // 'basic_salary' => $basic_salary,
                // 'gross_salary' => $gross_salary,
                'reamburse' => $reamburse,
                'overtime' => $overtime,
                'overtime_deduction' => $overtime_deduction,
                'period' => $period,
                'month_year' => $month_year,
                'bonus' => $bonus,
                'house_allowance' => $house_allowance,
                'daily_allowance' => $daily_allowance,
                'meal_allowance' => $meal_allowance,
                'transport_allowance' => $transport_allowance,
                'tax' => $tax,
                'created_at' => $created_at,
                'late_fine' => $late_fine,
                'punishment' => $punishment,
                'salary' => $salary,
                'deductions' => $deductions,
                'bpjs_ket' => $bpjs_ket,
                'bpjs_kes' => $bpjs_kes,
                'debt' => $debt,
                'take_home' => $take_home,
                'monthYearHuman' => $carbonMonthYear->diffForHumans(),
                'formattedMonthYear' => $carbonMonthYear->format('M Y'),
            ];

            // Render PDF
            $htmlView = view('pages.Payrolls.show', $data)->render();
            $pdf = Pdf::loadHtml($htmlView)->setPaper('A4', 'portrait');

            // Enkripsi PDF berdasarkan tanggal lahir jika tersedia
            if ($payroll->employee && $payroll->employee->date_of_birth) {
                try {
                    $dobCarbon = Carbon::parse($payroll->employee->date_of_birth);
                    $password = $dobCarbon->format('Ymd');
                    $pdf->setEncryption($password);
                } catch (\Exception $e) {
                    Log::warning("Gagal set password PDF untuk payroll ID {$payroll->id}: " . $e->getMessage());
                }
            }

            // Simpan file PDF ke storage
            $fileDate = $carbonMonthYear->format('Y_m');
            $filename = 'payroll_' . str_replace(' ', '_', strtolower($payroll->employee->employee_name)) . '_' . $fileDate . '.pdf';
            $path = 'payrolls/' . $filename;
            Storage::disk('public')->put($path, $pdf->output());

            // Simpan path PDF ke database
            $payroll->attachment_path = $path;
            $payroll->save();
        }

        return redirect()->back()->with('success', 'All payslip generate succesfully!');
    }


    public function getPayrolls(Request $request)
{
    ini_set('max_execution_time', 120);

    $payrollsQuery = Payrolls::with('Employee')
        ->select([
            'id',
            'employee_id',
            'daily_allowance',
            'basic_salary',
            'allowance',
            'reamburse',
            'gross_salary',
            'bonus',
            'house_allowance',
            'meal_allowance',
            'transport_allowance',
            'period',
            'tax',
            'deductions',
            'salary',
            'take_home',
            'month_year',
            'overtime',
            'overtime_deduction',
            'attendance',
            'late_fine',
            'bpjs_ket',
            'bpjs_kes',
            'debt',
            'punishment'
        ]);
    if ($request->filled('month_year')) {
        $payrollsQuery->whereRaw("DATE_FORMAT(month_year, '%Y-%m') = ?", [$request->month_year]);
    }

    $payrolls = $payrollsQuery->get()->map(function ($payroll) {
        // nggak perlu decrypt manual lagi
        $payroll->id_hashed = substr(hash('sha256', $payroll->id . env('APP_KEY')), 0, 8);
        $payroll->checkbox = '<input type="checkbox" class="payroll-checkbox" name="payroll_ids[]" value="' . $payroll->id_hashed . '">';
        return $payroll;
    });

    return DataTables::of($payrolls)
        ->addColumn('employee_name', function ($payroll) {
            return $payroll->employee->employee_name ?? 'Empty';
        })
        ->addColumn('employee_pengenal', function ($payroll) {
            return $payroll->employee->employee_pengenal ?? 'Empty';
        })
        ->rawColumns(['checkbox', 'employee_name','employee_pengenal'])
        ->make(true);
}



public function bulkDelete(Request $request)
{
    $idsRaw = $request->input('payroll_ids', '');
    $ids = is_array($idsRaw) ? $idsRaw : explode(',', $idsRaw);

    if (empty($ids)) {
        return back()->with('error', 'Select data first.');
    }

    $matchedIds = [];

    Payrolls::chunk(100, function ($payrolls) use (&$matchedIds, $ids) {
        foreach ($payrolls as $payroll) {
            $hash = substr(hash('sha256', $payroll->id . env('APP_KEY')), 0, 8);
            if (in_array($hash, $ids)) {
                $matchedIds[] = $payroll->id;
            }
        }
    });

    $payrollsToDelete = Payrolls::whereIn('id', $matchedIds)->get();
    $deletedFiles = [];
    $checked = [];

    foreach ($payrollsToDelete as $payroll) {
        $attachment = $payroll->getAttributes()['attachment_path'] ?? null;

        if (empty($attachment)) {
            Log::warning("Tidak ada attachment_path di payroll id {$payroll->id}");
            $checked[$payroll->id] = ['attachment' => null, 'status' => 'no_attachment'];
            continue;
        }
        $relative = preg_replace('#^(storage/|public/)#i', '', $attachment);
        $filePath = public_path('storage/' . $relative);
        $checked[$payroll->id] = ['attachment' => $attachment, 'resolved_path' => $filePath];

        
        if (File::exists($filePath)) {
            try {
                File::delete($filePath);
                $deletedFiles[] = $filePath;
                $checked[$payroll->id]['status'] = 'deleted';
            } catch (\Throwable $e) {
                $checked[$payroll->id]['status'] = 'delete_failed';
                $checked[$payroll->id]['error'] = $e->getMessage();
            }
        } else {
            $checked[$payroll->id]['status'] = 'not_found';
        }
    }

    $deleted = Payrolls::whereIn('id', $matchedIds)->delete();      
    return back()->with('success', "$deleted Payrolls data has been deleted).");
}



    public function indexpayrolls()
    {
        $files = Storage::disk('public')->files('templatepayrolls');
        return view('pages.Importpayroll.Importpayroll', compact('files'));
    }
    public function downloadpayrolls($filename)
    {
        $path = 'templatepayrolls/' . $filename;

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->download($path);
        }
        abort(404);
    }

    public function Importpayrolls(Request $request)
{

try {
    Excel::import(new PayrollsImport, $request->file('file'));
    return back()->with('success', 'Payroll has been imported!');
} catch (ValidationException $e) {
    $failures = $e->failures();
    $errors = [];
    foreach ($failures as $failure) {
        $errors[] = "Row {$failure->row()} - Column: {$failure->attribute()} - Message: {$failure->errors()[0]}";
    }
    return back()->with('error_custom', $errors);
}

}
}