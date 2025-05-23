<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payrolls;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Rules\NoXSSInput;
use Barryvdh\DomPDF\Facade\Pdf; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class PayrollsController extends Controller
{
    public function index()
    {
        
    
        return view('pages.Payrolls.Payrolls');
    }
    public function generateAll()
{
    $carbonMonthYear = Carbon::now(); // atau bisa request('bulan') jika dari form

    $payrolls = Payrolls::with('employee')
        ->whereMonth('created_at', $carbonMonthYear->month)
        ->whereYear('created_at', $carbonMonthYear->year)
        ->get();

    foreach ($payrolls as $payroll) {
        // Buat formatted bulan dan tahun untuk dikirim ke view
        $formattedMonthYear = $carbonMonthYear->isoFormat('MMMM Y'); // Contoh: Mei 2025
        $attendance = $payroll->attendance;
        $period = $payroll->period;
        $month_year = $payroll->month_year;
        $bonus = Crypt::decrypt($payroll->bonus);
        $tax = Crypt::decrypt($payroll->tax);
        $house_allowance = Crypt::decrypt($payroll->house_allowance);
        $meal_allowance = Crypt::decrypt($payroll->meal_allowance);
        $transport_allowance = Crypt::decrypt($payroll->transport_allowance);
        $deductions = Crypt::decrypt($payroll->deductions);
        $salary = Crypt::decrypt($payroll->salary);
        $overtime = Crypt::decrypt($payroll->overtime);
        $late_fine = Crypt::decrypt($payroll->late_fine);
        $bpjs_ket = Crypt::decrypt($payroll->bpjs_ket);
        $bpjs_kes = Crypt::decrypt($payroll->bpjs_kes);
        // $mesh = Crypt::decrypt($payroll->mesh);
        $daily_allowance = Crypt::decrypt($payroll->daily_allowance);
        $punishment = Crypt::decrypt($payroll->punishment);
         // Hitung salary
    $salaryincome = intval($attendance)  
    * intval($daily_allowance) + 
                   intval($overtime) + intval($bonus) + 
                   intval($house_allowance) + intval($meal_allowance) + 
                   intval($transport_allowance);
    
    $salaryoutcome = intval($punishment) + intval($tax) + 
                    intval($late_fine) + intval($bpjs_ket) + 
                    intval($bpjs_kes);

    $carbonMonthYear = $payroll->month_year instanceof Carbon 
        ? $payroll->month_year 
        : Carbon::parse($payroll->month_year);
        $data = [
            'payroll' => $payroll,
            'overtime' => $overtime,
            'period' => $period,
            'month_year' => $month_year,
            'bonus' => $bonus,
            'house_allowance' => $house_allowance,
            'daily_allowance' => $daily_allowance,
            'meal_allowance' => $meal_allowance,
            'transport_allowance' => $transport_allowance,
            'tax' => $tax,
            'late_fine' => $late_fine,
            'punishment' => $punishment,
            'salary' => $salary,
            'bpjs_ket' => $bpjs_ket,
            'bpjs_kes' => $bpjs_kes,
            'salaryincome' => $salaryincome,
            'salaryoutcome' => $salaryoutcome,
         
            'monthYearHuman' => $carbonMonthYear->diffForHumans(),
            'formattedMonthYear' => $carbonMonthYear->format('M Y'),
        ];

        $htmlView = view('pages.Payrolls.show', $data)->render();
        $pdf = Pdf::loadHtml($htmlView);
        $pdf->setPaper('A4', 'portrait');

        if ($payroll->employee && $payroll->employee->date_of_birth) {
            try {
                $dateObj = $payroll->employee->date_of_birth instanceof Carbon 
                    ? $payroll->employee->date_of_birth 
                    : Carbon::parse($payroll->employee->date_of_birth);
                $password = $dateObj->format('Ymd');
                $pdf->setEncryption($password);
            } catch (\Exception $e) {
                \Log::warning("Gagal set password PDF untuk payroll ID {$payroll->id}: " . $e->getMessage());
            }
        }

        $fileDate = $carbonMonthYear->format('Y_m');
        $filename = 'payroll_' . str_replace(' ', '_', strtolower($payroll->employee->employee_name)) . '_' . $fileDate . '.pdf';
        $path = 'payrolls/' . $filename;
        Storage::disk('public')->put($path, $pdf->output());

        $payroll->attachment_path = $path;
        $payroll->save();
    }

    return redirect()->back()->with('success', 'Semua slip gaji berhasil digenerate!');
}

public function getPayrolls(Request $request)
{
    $payrollsQuery = Payrolls::with('Employee')
        ->select([
            'id', 'employee_id', 'daily_allowance','bonus', 'house_allowance', 'meal_allowance', 
            'transport_allowance','period', 'deductions','tax', 'salary', 'month_year',
            'overtime',  'attendance', 'late_fine',
            'bpjs_ket', 'bpjs_kes','punishment'
        ]);
    // Filter berdasarkan month_year (Hanya Y-m, bukan Y-m-d)
    if ($request->filled('month_year')) {
        $payrollsQuery->whereRaw("DATE_FORMAT(month_year, '%Y-%m') = ?", [$request->month_year]);
    }
    $payrolls = $payrollsQuery->get()->map(function ($payroll) {
        try {
            $payroll->bonus = $payroll->bonus ? Crypt::decrypt($payroll->bonus) : null;
            $payroll->house_allowance = $payroll->house_allowance ? Crypt::decrypt($payroll->house_allowance) : null;
            $payroll->meal_allowance = $payroll->meal_allowance ? Crypt::decrypt($payroll->meal_allowance) : null;
            $payroll->tax = $payroll->tax ? Crypt::decrypt($payroll->tax) : null;
            $payroll->transport_allowance = $payroll->transport_allowance ? Crypt::decrypt($payroll->transport_allowance) : null;
            $payroll->deductions = $payroll->deductions ? Crypt::decrypt($payroll->deductions) : null;
            $payroll->salary = $payroll->salary ? Crypt::decrypt($payroll->salary) : null;
            $payroll->overtime = $payroll->overtime ? Crypt::decrypt($payroll->overtime) : null;
            $payroll->daily_allowance = $payroll->daily_allowance ? Crypt::decrypt($payroll->daily_allowance) : null;
            $payroll->late_fine = $payroll->late_fine ? Crypt::decrypt($payroll->late_fine) : null;
            $payroll->bpjs_ket = $payroll->bpjs_ket ? Crypt::decrypt($payroll->bpjs_ket) : null;
            $payroll->bpjs_kes = $payroll->bpjs_kes ? Crypt::decrypt($payroll->bpjs_kes) : null;
            // $payroll->mesh = $payroll->mesh ? Crypt::decrypt($payroll->mesh) : null;
            $payroll->punishment = $payroll->punishment ? Crypt::decrypt($payroll->punishment) : null;
            
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // handle decrypt error (misalnya log atau fallback)
            \Log::error("Decrypt error: " . $e->getMessage());
        }

        // ID hash dan action link
        $payroll->id_hashed = substr(hash('sha256', $payroll->id . env('APP_KEY')), 0, 8);
        $payroll->checkbox = '<input type="checkbox" class="payroll_ids[]" value="' . $payroll->id_hashed . '">';
        $payroll->action = '
            <a href="' . route('Payrolls.edit', $payroll->id_hashed) . '" class="mx-2" data-bs-toggle="tooltip" title="Edit Payrolls: ' . e($payroll->employee->employee_name) . '">
                <i class="fas fa-user-edit text-secondary"></i>
            </a>
            ';
            // <a href="' . route('Payrolls.show', $payroll->id_hashed) . '" target="_blank" class="mx-2" data-bs-toggle="tooltip" title="Show Payroll: ' . e($payroll->employee->employee_name) . '">
            //     <i class="fas fa-eye text-secondary"></i>
            // </a>
        return $payroll;
    });
    return DataTables::of($payrolls)
        ->addColumn('employee_name', function ($payroll) {
            return $payroll->employee->employee_name ?? 'Empty';
        })
      
        ->rawColumns(['action','checkbox', 'employee_name'])
        ->make(true);
}
public function generate($hashedId)
{
    $payrolls = Payrolls::with(['employee.department','employee.bank', 'employee.position','employee.company'])->get();
    $payroll = $payrolls->first(function ($u) use ($hashedId) {
        $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
        return $expectedHash === $hashedId;
    });

    if (!$payroll) {
        abort(404, 'Payroll not found.');
    }

    $attendance = $payroll->attendance;
    $month_year = $payroll->month_year;
    $period = $payroll->period;
    $bonus = Crypt::decrypt($payroll->bonus);
    $house_allowance = Crypt::decrypt($payroll->house_allowance);
    $meal_allowance = Crypt::decrypt($payroll->meal_allowance);
    $transport_allowance = Crypt::decrypt($payroll->transport_allowance);
    $tax = Crypt::decrypt($payroll->tax);
    $deductions = Crypt::decrypt($payroll->deductions);
    $salary = Crypt::decrypt($payroll->salary);
    $overtime = Crypt::decrypt($payroll->overtime);
    $late_fine = Crypt::decrypt($payroll->late_fine);
    $bpjs_ket = Crypt::decrypt($payroll->bpjs_ket);
    $bpjs_kes = Crypt::decrypt($payroll->bpjs_kes);
    $daily_allowance = Crypt::decrypt($payroll->daily_allowance);
    $punishment = Crypt::decrypt($payroll->punishment);

    $salaryincome = intval($attendance) * intval($daily_allowance)
        + intval($overtime) + intval($bonus) + intval($house_allowance)
        + intval($meal_allowance) + intval($transport_allowance);

    $salaryoutcome = intval($punishment) + intval($tax) + intval($late_fine)
        + intval($bpjs_ket) + intval($bpjs_kes);

    $carbonMonthYear = $payroll->month_year instanceof Carbon 
        ? $payroll->month_year 
        : Carbon::parse($payroll->month_year);

    $data = [
        'payroll' => $payroll,
        'overtime' => $overtime,
        'month_year' => $month_year,
        'bonus' => $bonus,
        'period' => $period,
        'house_allowance' => $house_allowance,
        'daily_allowance' => $daily_allowance,
        'meal_allowance' => $meal_allowance,
        'transport_allowance' => $transport_allowance,
        'late_fine' => $late_fine,
        'tax' => $tax,
        'punishment' => $punishment,
        'salary' => $salary,
        'bpjs_ket' => $bpjs_ket,
        'bpjs_kes' => $bpjs_kes,
        'salaryincome' => $salaryincome,
        'salaryoutcome' => $salaryoutcome,
        'hashedId' => $hashedId,
        'monthYearHuman' => $carbonMonthYear->diffForHumans(),
        'formattedMonthYear' => $carbonMonthYear->format('M Y'),
    ];

    $htmlView = view('pages.Payrolls.show', $data)->render();
    $pdf = Pdf::loadHtml($htmlView)->setPaper('A4', 'portrait');

    if ($payroll->employee && $payroll->employee->date_of_birth) {
        try {
            $dob = $payroll->employee->date_of_birth instanceof Carbon 
                ? $payroll->employee->date_of_birth 
                : Carbon::parse($payroll->employee->date_of_birth);
            $pdf->setEncryption($dob->format('Ymd'));
        } catch (\Exception $e) {
            \Log::warning("PDF encryption failed: " . $e->getMessage());
        }
    }

    $filename = 'Payroll_' . $payroll->employee->name . '_' . $carbonMonthYear->format('Y_m') . '.pdf';
    return $pdf->download($filename);
}

// public function show($hashedId)
// {
//     $payrolls = Payrolls::with(['employee.department','employee.bank', 'employee.position','employee.company'])->get();
//     $payroll = $payrolls->first(function ($u) use ($hashedId) {
//         $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
//         return $expectedHash === $hashedId;
//     });
    
//     if (!$payroll) {
//         abort(404, 'Payroll not found.');
//     }
//     // dd($payroll->employee->company->foto);

//     // Dekripsi kolom-kolom yang diperlukan
//     $attendance = $payroll->attendance;
//     $month_year = $payroll->month_year;
//     $period = $payroll->period;
//     $bonus = Crypt::decrypt($payroll->bonus);
//     $house_allowance = Crypt::decrypt($payroll->house_allowance);
//     $tax = Crypt::decrypt($payroll->tax);
//     $meal_allowance = Crypt::decrypt($payroll->meal_allowance);
//     $transport_allowance = Crypt::decrypt($payroll->transport_allowance);
//     $deductions = Crypt::decrypt($payroll->deductions);
//     $salary = Crypt::decrypt($payroll->salary);
//     $overtime = Crypt::decrypt($payroll->overtime);
//     $late_fine = Crypt::decrypt($payroll->late_fine);
//     $bpjs_ket = Crypt::decrypt($payroll->bpjs_ket);
//     $bpjs_kes = Crypt::decrypt($payroll->bpjs_kes);
//     // $mesh = Crypt::decrypt($payroll->mesh);
//     $daily_allowance = Crypt::decrypt($payroll->daily_allowance);
//     $punishment = Crypt::decrypt($payroll->punishment);
//     // Hitung salary
//     $salaryincome = intval($attendance)  
//     * intval($daily_allowance) + 
//                    intval($overtime) + intval($bonus) + 
//                    intval($house_allowance) + intval($meal_allowance) + 
//                    intval($transport_allowance);
    
//     $salaryoutcome = intval($punishment) + intval($tax) +
//                     intval($late_fine) + intval($bpjs_ket) + 
//                     intval($bpjs_kes);

//     $carbonMonthYear = $payroll->month_year instanceof Carbon 
//         ? $payroll->month_year 
//         : Carbon::parse($payroll->month_year);
    
//     $data = [
//         'payroll' => $payroll,
//         'overtime' => $overtime,
//         'month_year' => $month_year,
//         'period' => $period,
//         'bonus' => $bonus,
//         'house_allowance' => $house_allowance,
//         'daily_allowance' => $daily_allowance,
//         'meal_allowance' => $meal_allowance,
//         'tax' => $tax,
//         'transport_allowance' => $transport_allowance,
//         'late_fine' => $late_fine,
//         'punishment' => $punishment,
//         'salary' => $salary,
//         'bpjs_ket' => $bpjs_ket,
//         'bpjs_kes' => $bpjs_kes,
//         'salaryincome' => $salaryincome,
//         'salaryoutcome' => $salaryoutcome,
//         'hashedId' => $hashedId,
//         'monthYearHuman' => $carbonMonthYear->diffForHumans(),
//         'formattedMonthYear' => $carbonMonthYear->format('M Y'),
//     ];
    
//     // Load view yang SAMA untuk HTML dan PDF
//     $htmlView = view('pages.Payrolls.show', $data)->render();
    
//     // Konfigurasi PDF agar mirip dengan HTML
//     $pdf = Pdf::loadHtml($htmlView);
//     $pdf->setPaper('A4', 'portrait'); // Pastikan orientasi dan ukuran sama
//     // Set password jika diperlukan
//     if ($payroll->employee && $payroll->employee->date_of_birth) {
//         try {
//             $dateObj = $payroll->employee->date_of_birth instanceof Carbon 
//                 ? $payroll->employee->date_of_birth 
//                 : Carbon::parse($payroll->employee->date_of_birth);
//             $password = $dateObj->format('Ymd');
//             $pdf->setEncryption($password);
//         } catch (\Exception $e) {
//             \Log::warning("Failed to set PDF password: " . $e->getMessage());
//         }
//     }
    
//     // Simpan PDF ke storage
//     $fileDate = $carbonMonthYear->format('Y_m');
//     $filename = 'payroll_' . $payroll->employee->employee_name . '_' . $fileDate . '.pdf';
//     $path = 'payrolls/' . $filename;
//     Storage::disk('public')->put($path, $pdf->output());
    
//     $payroll->attachment_path = $path;
//     $payroll->save();

//     // Kembalikan view HTML (bisa juga langsung return $pdf->stream() jika ingin PDF)
//     return view('pages.Payrolls.show', $data);
// }


    public function edit($hashedId)
{
    $payroll = Payrolls::with('employee')->get()->first(function ($u) use ($hashedId) {
        $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
        return $expectedHash === $hashedId;
    });
    if (!$payroll) {
        abort(404, 'Payroll not found.');
    }
    try {
        $payroll->bonus = $payroll->bonus ? Crypt::decrypt($payroll->bonus) : null;
        $payroll->daily_allowance = $payroll->daily_allowance ? Crypt::decrypt($payroll->daily_allowance) : null;
        $payroll->house_allowance = $payroll->house_allowance ? Crypt::decrypt($payroll->house_allowance) : null;
        $payroll->meal_allowance = $payroll->meal_allowance ? Crypt::decrypt($payroll->meal_allowance) : null;
        $payroll->tax = $payroll->tax ? Crypt::decrypt($payroll->tax) : null;
        $payroll->transport_allowance = $payroll->transport_allowance ? Crypt::decrypt($payroll->transport_allowance) : null;
        $payroll->deductions = $payroll->deductions ? Crypt::decrypt($payroll->deductions) : null;
        $payroll->salary = $payroll->salary ? Crypt::decrypt($payroll->salary) : null;
        $payroll->overtime = $payroll->overtime ? Crypt::decrypt($payroll->overtime) : null;
        $payroll->late_fine = $payroll->late_fine ? Crypt::decrypt($payroll->late_fine) : null;
        $payroll->bpjs_ket = $payroll->bpjs_ket ? Crypt::decrypt($payroll->bpjs_ket) : null;
        $payroll->bpjs_kes = $payroll->bpjs_kes ? Crypt::decrypt($payroll->bpjs_kes) : null;
        $payroll->punishment = $payroll->punishment ? Crypt::decrypt($payroll->punishment) : null;
    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
        abort(500, 'Failed to decrypt payroll data.');
    }
    

    return view('pages.Payrolls.edit', [
        'payroll' => $payroll,
        'hashedId' => $hashedId,       
    ]);
}
    public function create()
    {
        $payrolls = Payrolls::with('employee')->get();
        return view('pages.Payrolls.create',compact('payrolls'));
    }

    public function update(Request $request, $hashedId)
    {
        $payroll = Payrolls::with('employee')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$payroll) {
            return redirect()->route('pages.Payrolls')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'bonus' => ['nullable','numeric',
                new NoXSSInput()],
            'house_allowance' => ['nullable','numeric',
                new NoXSSInput()],
            'meal_allowance' => ['nullable','numeric',
                new NoXSSInput()],
            'transport_allowance' => ['nullable','numeric',
                new NoXSSInput()],
            'tax' => ['nullable','numeric',
                new NoXSSInput()],
            'attendance' => ['required','numeric',
                new NoXSSInput()],
            'daily_allowance' => ['required','numeric',
                new NoXSSInput()],
            'overtime' => ['nullable','numeric',
                new NoXSSInput()],   
            'bpjs_ket' => ['nullable','numeric',
                new NoXSSInput()],
            'bpjs_kes' => ['nullable','numeric',
                new NoXSSInput()],
           
            'punishment' => ['nullable','numeric',
                new NoXSSInput()],
            'late_fine' => ['nullable','numeric',
                new NoXSSInput()],
            'salary' => ['nullable','numeric',
                new NoXSSInput()],
            'information' => ['nullable','string', 
                new NoXSSInput()],
            'period' => ['nullable','string',
                new NoXSSInput()],
        ], [
            'bonus.numeric' => 'Bonus must be a number.',
            'house_allowance.numeric' => 'House allowance must be a number.',
            'meal_allowance.numeric' => 'Meal allowance must be a number.',
            'transport_taxallowance.numeric' => 'Transport allowance must be a number.',
            'tax.numeric' => 'Transport allowance must be a number.',
            'daily_allowance.numeric' => 'Transport allowance must be a number.',
            'overtime.numeric' => 'Net salary must be a number.',
            'attendance.numeric' => 'Net salary must be a number.',
            'attendance.required' => 'Net salary must be filled.',
            'daily_allowance.required' => 'daily allowance must be filled.',
            'bpjs_kes.numeric' => 'bpjs kesehatan must be a number.',
            'bpjs_ket.numeric' => 'bpjs ketenagakerjaan must be a number.',
            'punishment.numeric' => 'punishment salary must be a number.',
            'late_fine.numeric' => 'late fine salary must be a number.',
            'deductions.numeric' => 'Deductions must be a number.',
          ]);
          $calculatedDeduction = 
          ($validatedData['punishment'] ?? 0) +
          ($validatedData['bpjs_ket'] ?? 0) +
          ($validatedData['bpjs_kes'] ?? 0) +
          ($validatedData['tax'] ?? 0) +
          ($validatedData['late_fine'] ?? 0);
          
          $calculatedSalary = 
          ($validatedData['attendance'] ?? 0) *
          ($validatedData['daily_allowance'] ?? 0) +
          ($validatedData['overtime'] ?? 0) +
          ($validatedData['bonus'] ?? 0) +
          ($validatedData['house_allowance'] ?? 0) +
          ($validatedData['meal_allowance'] ?? 0) +
          ($validatedData['transport_allowance'] ?? 0) -
          ($calculatedDeduction ?? 0);
  
        
        $payrollData = [
            'bonus' => Crypt::encrypt($validatedData['bonus']),
            'house_allowance' => Crypt::encrypt($validatedData['house_allowance']),
            'meal_allowance' => Crypt::encrypt($validatedData['meal_allowance']),
            'tax' => Crypt::encrypt($validatedData['tax']),
            'daily_allowance' => Crypt::encrypt($validatedData['daily_allowance']),
            'transport_allowance' => Crypt::encrypt($validatedData['transport_allowance']),
            'attendance' => ($validatedData['attendance']),
            'overtime' => Crypt::encrypt($validatedData['overtime']),
            'punishment' => Crypt::encrypt($validatedData['punishment']),
            'late_fine' => Crypt::encrypt($validatedData['late_fine']),
            'bpjs_ket' => Crypt::encrypt($validatedData['bpjs_ket']),
            'bpjs_kes' => Crypt::encrypt($validatedData['bpjs_kes']),
            'deductions' => Crypt::encrypt($calculatedDeduction),
            'salary' => Crypt::encrypt($calculatedSalary),
            'information' => $validatedData['information'] ?? null,
            'period' => $validatedData['period'] ?? null,
        ];
        DB::beginTransaction();
        $payroll->update($payrollData);
        DB::commit();
        return redirect()->route('pages.Payrolls')->with('success', 'Payrolls updated successfully.');
    }
    public function deletepayroll(Request $request)
    {
        $request->validate([

            'ids' => ['required', 'array', 'min:1', new NoXSSInput()],
            'ids.*' => ['uuid', new NoXSSInput()],

        ]);
        Payrolls::whereIn('id', $request->ids)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Selected users and their related data deleted successfully.'
        ]);
    }
}