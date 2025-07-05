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
use App\Imports\PayrollsImport;
use Illuminate\Support\Facades\Log;

class PayrollsController extends Controller
{
    public function index()
    {
        return view('pages.Payrolls.Payrolls');
    }
    public function generateAll()
    {
        ini_set('max_execution_time', 180);
        $currentCarbon = Carbon::now(); 

        $payrolls = Payrolls::with('employee')
            ->whereMonth('created_at', $currentCarbon->month)
            ->whereYear('created_at', $currentCarbon->year)
            ->get();
        foreach ($payrolls as $payroll) {
          
            try {
                $carbonMonthYear = Carbon::parse($payroll->month_year);
            } catch (\Exception $e) {
                \Log::warning("Gagal parse month_year untuk payroll ID {$payroll->id}: " . $e->getMessage());
                $carbonMonthYear = now(); // fallback jika gagal
            }
            // Decrypt field, cek null jika perlu
            $bonus = $payroll->bonus ? ($payroll->bonus) : 0;
            $tax = $payroll->tax ? ($payroll->tax) : 0;
            $house_allowance = $payroll->house_allowance ? ($payroll->house_allowance) : 0;
            $meal_allowance = $payroll->meal_allowance ? ($payroll->meal_allowance) : 0;
            $transport_allowance = $payroll->transport_allowance ? ($payroll->transport_allowance) : 0;
            $deductions = $payroll->deductions ? ($payroll->deductions) : 0;
            $salary = $payroll->salary ? ($payroll->salary) : 0;
            $overtime = $payroll->overtime ? ($payroll->overtime) : 0;
            $late_fine = $payroll->late_fine ? ($payroll->late_fine) : 0;
            $bpjs_ket = $payroll->bpjs_ket ? ($payroll->bpjs_ket) : 0;
            $bpjs_kes = $payroll->bpjs_kes ? ($payroll->bpjs_kes) : 0;
            $debt = $payroll->debt ? ($payroll->debt) : 0;
            $daily_allowance = $payroll->daily_allowance ? ($payroll->daily_allowance) : 0;
            $punishment = $payroll->punishment ? ($payroll->punishment) : 0;
            $period = $payroll->period ?? '-';
            // $created_at = $payroll->created_at ?? '-';
            $created_at = $payroll->created_at ? $payroll->created_at->format('Y-m-d') : '-';

            $attendance = $payroll->attendance ?? 0;
            $period = $payroll->period ?? '-';
            $month_year = $payroll->month_year ?? '-';
            $deductions = $payroll->deductions ?? '-';
            $salary = $payroll->salary ?? '-';
            $take_home = $payroll->take_home ?? '-';
//                 $salaryincome = (
//     intval($attendance) * intval($daily_allowance)
// ) + intval($overtime)
//   + intval($bonus)
//   + intval($house_allowance)
//   + intval($meal_allowance)
//   + intval($transport_allowance);
//             $salaryoutcome = intval($punishment) + intval($tax)
//                 + intval($late_fine) + intval($bpjs_ket)
//                 + intval($bpjs_kes);
                
//                 $takehome =   intval($salaryincome) - intval($salaryoutcome);

            $data = [
                'payroll' => $payroll,
                'attendance' => $attendance,
                'overtime' => $overtime,
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
                    \Log::warning("Gagal set password PDF untuk payroll ID {$payroll->id}: " . $e->getMessage());
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

        return redirect()->back()->with('success', 'Semua slip gaji berhasil digenerate!');
    }


    public function getPayrolls(Request $request)
    {
        ini_set('max_execution_time', 120);
        $payrollsQuery = Payrolls::with('Employee')
            ->select([
                'id',
                'employee_id',
                'daily_allowance',
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
                'attendance',
                'late_fine',
                'bpjs_ket',
                'bpjs_kes',
                'debt',
                'punishment'
            ]);
        // Filter berdasarkan month_year (Hanya Y-m, bukan Y-m-d)
        if ($request->filled('month_year')) {
            $payrollsQuery->whereRaw("DATE_FORMAT(month_year, '%Y-%m') = ?", [$request->month_year]);
        }
        $payrolls = $payrollsQuery->get()->map(function ($payroll) {
            try {
                $payroll->bonus = $payroll->bonus ? ($payroll->bonus) : null;
                $payroll->house_allowance = $payroll->house_allowance ? ($payroll->house_allowance) : null;
                $payroll->meal_allowance = $payroll->meal_allowance ? ($payroll->meal_allowance) : null;
                $payroll->tax = $payroll->tax ? ($payroll->tax) : null;
                $payroll->transport_allowance = $payroll->transport_allowance ? ($payroll->transport_allowance) : null;
                $payroll->overtime = $payroll->overtime ? ($payroll->overtime) : null;
                $payroll->daily_allowance = $payroll->daily_allowance ? ($payroll->daily_allowance) : null;
                $payroll->late_fine = $payroll->late_fine ? ($payroll->late_fine) : null;
                $payroll->bpjs_ket = $payroll->bpjs_ket ? ($payroll->bpjs_ket) : null;
                $payroll->bpjs_kes = $payroll->bpjs_kes ? ($payroll->bpjs_kes) : null;
                $payroll->debt = $payroll->debt ? ($payroll->debt) : null;
                $payroll->punishment = $payroll->punishment ? ($payroll->punishment) : null;
                $payroll->deductions = $payroll->deductions ? ($payroll->deductions) : null;
                $payroll->salary = $payroll->salary ? ($payroll->salary) : null;
                $payroll->take_home = $payroll->take_home ? ($payroll->take_home) : null;
              
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                \Log::error("Decrypt error: " . $e->getMessage());
            }
            $payroll->id_hashed = substr(hash('sha256', $payroll->id . env('APP_KEY')), 0, 8);
            $payroll->checkbox = '<input type="checkbox" class="payroll_ids[]" value="' . $payroll->id_hashed . '">';
            return $payroll;
        });
        return DataTables::of($payrolls)
            ->addColumn('employee_name', function ($payroll) {
                return $payroll->employee->employee_name ?? 'Empty';
            })
            ->rawColumns(['checkbox', 'employee_name'])
            // ->rawColumns(['action', 'checkbox', 'employee_name'])
            ->make(true);
    }   
    public function generate($hashedId)
    {
        \Log::info('Payroll PDF generation started.', ['hashedId' => $hashedId]);

        $payrolls = Payrolls::with(['employee.department', 'employee.bank', 'employee.position', 'employee.company'])->get();
        \Log::debug('Total payrolls fetched: ' . $payrolls->count());

        $payroll = $payrolls->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$payroll) {
            \Log::warning('Payroll not found.', ['hashedId' => $hashedId]);
            abort(404, 'Payroll not found.');
        }

        \Log::info('Payroll matched.', ['payroll_id' => $payroll->id]);

        // Log employee data
        if (!$payroll->employee) {
            \Log::error('Employee is null for payroll ID: ' . $payroll->id);
            abort(500, 'Employee data is missing.');
        }

        \Log::debug('Employee details', [
            'name' => $payroll->employee->name ?? 'N/A',
            'dob' => $payroll->employee->date_of_birth ?? 'N/A'
        ]);

        try {
            $attendance = $payroll->attendance;
            $month_year = $payroll->month_year;
            $period = $payroll->period;
            $bonus = ($payroll->bonus);
            $house_allowance = ($payroll->house_allowance);
            $meal_allowance = ($payroll->meal_allowance);
            $transport_allowance = ($payroll->transport_allowance);
            $tax = ($payroll->tax);
            $deductions = ($payroll->deductions);
            $salary = ($payroll->salary);
            $overtime = ($payroll->overtime);
            $late_fine = ($payroll->late_fine);
            $bpjs_ket = ($payroll->bpjs_ket);
            $bpjs_kes = ($payroll->bpjs_kes);
            $debt = ($payroll->debt);
            $daily_allowance = ($payroll->daily_allowance);
            $punishment = ($payroll->punishment);
        } catch (\Exception $e) {
            \Log::error('Decryption failed: ' . $e->getMessage());
            abort(500, 'Failed to decrypt payroll data.');
        }

        $salaryincome = intval($attendance) * intval($daily_allowance)
            + intval($overtime) + intval($bonus) + intval($house_allowance)
            + intval($meal_allowance) + intval($transport_allowance);

        // $salaryoutcome = intval($punishment) + intval($tax) + intval($late_fine)
        //     + intval($bpjs_ket) + intval($bpjs_kes);
        $salaryoutcome = $salaryincome - intval($deductions);

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
            'deductions' => $deductions,
            'salary' => $salary,
            'bpjs_ket' => $bpjs_ket,
            'bpjs_kes' => $bpjs_kes,
            'salaryincome' => $salaryincome,
            'salaryoutcome' => $salaryoutcome,
            'hashedId' => $hashedId,
            'monthYearHuman' => $carbonMonthYear->diffForHumans(),
            'formattedMonthYear' => $carbonMonthYear->format('M Y'),
        ];
        try {
            $htmlView = view('pages.Payrolls.show', $data)->render();
            \Log::debug('HTML view rendered successfully.');
            // Optionally save HTML for offline debug
            file_put_contents(storage_path('app/pdf_debug.html'), $htmlView);
        } catch (\Throwable $e) {
            \Log::error('Error rendering view: ' . $e->getMessage());
            abort(500, 'Error rendering PDF view.');
        }

        try {
            $pdf = Pdf::loadHtml($htmlView)->setPaper('A4', 'portrait');
        } catch (\Throwable $e) {
            \Log::error('PDF loadHtml failed: ' . $e->getMessage());
            abort(500, 'PDF generation failed.');
        }

        if ($payroll->employee && $payroll->employee->date_of_birth) {
            try {
                $dob = $payroll->employee->date_of_birth instanceof Carbon
                    ? $payroll->employee->date_of_birth
                    : Carbon::parse($payroll->employee->date_of_birth);
                $pdf->setEncryption($dob->format('Ymd'));
            } catch (\Exception $e) {
                \Log::warning('PDF encryption failed: ' . $e->getMessage());
            }
        }

        $filename = 'Payroll_' . ($payroll->employee->name ?? 'Unknown') . '_' . $carbonMonthYear->format('Y_m') . '.pdf';
        \Log::info('PDF generated successfully.', ['filename' => $filename]);

        return $pdf->download($filename);
    }




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
            $payroll->bonus = $payroll->bonus ? ($payroll->bonus) : null;
            $payroll->daily_allowance = $payroll->daily_allowance ? ($payroll->daily_allowance) : null;
            $payroll->house_allowance = $payroll->house_allowance ? ($payroll->house_allowance) : null;
            $payroll->meal_allowance = $payroll->meal_allowance ? ($payroll->meal_allowance) : null;
            $payroll->tax = $payroll->tax ? ($payroll->tax) : null;
            $payroll->transport_allowance = $payroll->transport_allowance ? ($payroll->transport_allowance) : null;
            $payroll->deductions = $payroll->deductions ? ($payroll->deductions) : null;
            $payroll->salary = $payroll->salary ? ($payroll->salary) : null;
            $payroll->overtime = $payroll->overtime ? ($payroll->overtime) : null;
            $payroll->late_fine = $payroll->late_fine ? ($payroll->late_fine) : null;
            $payroll->bpjs_ket = $payroll->bpjs_ket ? ($payroll->bpjs_ket) : null;
            $payroll->bpjs_kes = $payroll->bpjs_kes ? ($payroll->bpjs_kes) : null;
            $payroll->punishment = $payroll->punishment ? ($payroll->punishment) : null;
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
        return view('pages.Payrolls.create', compact('payrolls'));
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
            'bonus' => [
                'nullable',
                'numeric',
                new NoXSSInput()
            ],
            'house_allowance' => [
                'nullable',
                'numeric',
                new NoXSSInput()
            ],
            'meal_allowance' => [
                'nullable',
                'numeric',
                new NoXSSInput()
            ],
            'transport_allowance' => [
                'nullable',
                'numeric',
                new NoXSSInput()
            ],
            'tax' => [
                'nullable',
                'numeric',
                new NoXSSInput()
            ],
            'attendance' => [
                'required',
                'numeric',
                new NoXSSInput()
            ],
            'daily_allowance' => [
                'required',
                'numeric',
                new NoXSSInput()
            ],
            'overtime' => [
                'nullable',
                'numeric',
                new NoXSSInput()
            ],
            'bpjs_ket' => [
                'nullable',
                'numeric',
                new NoXSSInput()
            ],
            'bpjs_kes' => [
                'nullable',
                'numeric',
                new NoXSSInput()
            ],

            'punishment' => [
                'nullable',
                'numeric',
                new NoXSSInput()
            ],
            'late_fine' => [
                'nullable',
                'numeric',
                new NoXSSInput()
            ],
            'salary' => [
                'nullable',
                'numeric',
                new NoXSSInput()
            ],
            'information' => [
                'nullable',
                'string',
                new NoXSSInput()
            ],
            'period' => [
                'nullable',
                'string',
                new NoXSSInput()
            ],
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
            'bonus' => ($validatedData['bonus']),
            'house_allowance' => ($validatedData['house_allowance']),
            'meal_allowance' => ($validatedData['meal_allowance']),
            'tax' => ($validatedData['tax']),
            'daily_allowance' => ($validatedData['daily_allowance']),
            'transport_allowance' => ($validatedData['transport_allowance']),
            'attendance' => ($validatedData['attendance']),
            'overtime' => ($validatedData['overtime']),
            'punishment' => ($validatedData['punishment']),
            'late_fine' => ($validatedData['late_fine']),
            'bpjs_ket' => ($validatedData['bpjs_ket']),
            'bpjs_kes' => ($validatedData['bpjs_kes']),
            'deductions' => ($calculatedDeduction),
            'salary' => ($calculatedSalary),
            'information' => $validatedData['information'] ?? null,
            'period' => $validatedData['period'] ?? null,
        ];
        DB::beginTransaction();
        $payroll->update($payrollData);
        DB::commit();
        return redirect()->route('pages.Payrolls')->with('success', 'Payrolls updated successfully.');
    }
    // public function deletepayroll(Request $request)
    // {
    //     $request->validate([

    //         'ids' => ['required', 'array', 'min:1', new NoXSSInput()],
    //         'ids.*' => ['uuid', new NoXSSInput()],

    //     ]);
    //     Payrolls::whereIn('id', $request->ids)->delete();
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Selected users and their related data deleted successfully.'
    //     ]);
    // }
    public function bulkDelete(Request $request)
{
    $ids = $request->input('payroll_ids', []);

    if (empty($ids)) {
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }

    // Dekripsi manual hashed ID (pastikan hash-nya unik)
    $deleted = 0;
    foreach (Payrolls::all() as $payroll) {
        $expectedHash = substr(hash('sha256', $payroll->id . env('APP_KEY')), 0, 8);
        if (in_array($expectedHash, $ids)) {
            $payroll->delete();
            $deleted++;
        }
    }

    return back()->with('success', "$deleted data berhasil dihapus.");
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
    //     public function Importpayrolls(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|mimes:xlsx,csv,xls'
    //     ]);

    //     $import = new Payrolls();
    //     $import->import($request->file('file'));
    //     if ($import->failures()->isNotEmpty()) {
    //         return back()->with('failures', $import->failures());
    //     }

    //     return back()->with('success', 'Payrolls import successfully!');
    // }
    public function Importpayrolls(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,csv,xls'
    ]);

    $errors = [];
    $import = new PayrollsImport($errors);
    $import->import($request->file('file'));

    if ($import->failures()->isNotEmpty()) {
    return back()->with([
        'failures' => $import->failures(), // INI YANG WAJIB
        'errors' => $errors, // opsional
    ]);
}
    if (!empty($errors)) {
        return back()->with('failures', $errors);
    }

    return back()->with('success', 'Payrolls import successfully!');
}
}
