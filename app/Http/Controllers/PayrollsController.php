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
                Log::warning("Gagal parse month_year untuk payroll ID {$payroll->id}: " . $e->getMessage());
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
        ->rawColumns(['checkbox', 'employee_name'])
        ->make(true);
}

    // public function getPayrolls(Request $request)
    // {
    //     ini_set('max_execution_time', 120);
    //     $payrollsQuery = Payrolls::with('Employee')
    //         ->select([
    //             'id',
    //             'employee_id',
    //             'daily_allowance',
    //             'bonus',
    //             'house_allowance',
    //             'meal_allowance',
    //             'transport_allowance',
    //             'period',
    //             'tax',
    //             'deductions',
    //             'salary',
    //             'take_home',
    //             'month_year',
    //             'overtime',
    //             'attendance',
    //             'late_fine',
    //             'bpjs_ket',
    //             'bpjs_kes',
    //             'debt',
    //             'punishment'
    //         ]);
    //     if ($request->filled('month_year')) {
    //         $payrollsQuery->whereRaw("DATE_FORMAT(month_year, '%Y-%m') = ?", [$request->month_year]);
    //     }
    //     $payrolls = $payrollsQuery->get()->map(function ($payroll) {
    //         try {
    //             $payroll->bonus = $payroll->bonus ? ($payroll->bonus) : null;
    //             $payroll->house_allowance = $payroll->house_allowance ? ($payroll->house_allowance) : null;
    //             $payroll->meal_allowance = $payroll->meal_allowance ? ($payroll->meal_allowance) : null;
    //             $payroll->tax = $payroll->tax ? ($payroll->tax) : null;
    //             $payroll->transport_allowance = $payroll->transport_allowance ? ($payroll->transport_allowance) : null;
    //             $payroll->overtime = $payroll->overtime ? ($payroll->overtime) : null;
    //             $payroll->daily_allowance = $payroll->daily_allowance ? ($payroll->daily_allowance) : null;
    //             $payroll->late_fine = $payroll->late_fine ? ($payroll->late_fine) : null;
    //             $payroll->bpjs_ket = $payroll->bpjs_ket ? ($payroll->bpjs_ket) : null;
    //             $payroll->bpjs_kes = $payroll->bpjs_kes ? ($payroll->bpjs_kes) : null;
    //             $payroll->debt = $payroll->debt ? ($payroll->debt) : null;
    //             $payroll->punishment = $payroll->punishment ? ($payroll->punishment) : null;
    //             $payroll->deductions = $payroll->deductions ? ($payroll->deductions) : null;
    //             $payroll->salary = $payroll->salary ? ($payroll->salary) : null;
    //             $payroll->take_home = $payroll->take_home ? ($payroll->take_home) : null;
    //         } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
    //             Log::error("Decrypt error: " . $e->getMessage());
    //         }
    //         $payroll->id_hashed = substr(hash('sha256', $payroll->id . env('APP_KEY')), 0, 8);
    //         $payroll->checkbox = '<input type="checkbox" class="payroll-checkbox" name="payroll_ids[]" value="' . $payroll->id_hashed . '">';
    //         return $payroll;
    //     });
    //     return DataTables::of($payrolls)
    //         ->addColumn('employee_name', function ($payroll) {
    //             return $payroll->employee->employee_name ?? 'Empty';
    //         })
    //         ->rawColumns(['checkbox', 'employee_name'])
    //         ->make(true);
    // }
    public function generate($hashedId)
    {
        Log::info('Payroll PDF generation started.', ['hashedId' => $hashedId]);

        $payrolls = Payrolls::with(['employee.department', 'employee.bank', 'employee.position', 'employee.company'])->get();
        Log::debug('Total payrolls fetched: ' . $payrolls->count());

        $payroll = $payrolls->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$payroll) {
            Log::warning('Payroll not found.', ['hashedId' => $hashedId]);
            abort(404, 'Payroll not found.');
        }

        Log::info('Payroll matched.', ['payroll_id' => $payroll->id]);

        // Log employee data
        if (!$payroll->employee) {
            Log::error('Employee is null for payroll ID: ' . $payroll->id);
            abort(500, 'Employee data is missing.');
        }

        Log::debug('Employee details', [
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
            Log::error('Decryption failed: ' . $e->getMessage());
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
            Log::debug('HTML view rendered successfully.');
            // Optionally save HTML for offline debug
            file_put_contents(storage_path('app/pdf_debug.html'), $htmlView);
        } catch (\Throwable $e) {
            Log::error('Error rendering view: ' . $e->getMessage());
            abort(500, 'Error rendering PDF view.');
        }

        try {
            $pdf = Pdf::loadHtml($htmlView)->setPaper('A4', 'portrait');
        } catch (\Throwable $e) {
            Log::error('PDF loadHtml failed: ' . $e->getMessage());
            abort(500, 'PDF generation failed.');
        }

        if ($payroll->employee && $payroll->employee->date_of_birth) {
            try {
                $dob = $payroll->employee->date_of_birth instanceof Carbon
                    ? $payroll->employee->date_of_birth
                    : Carbon::parse($payroll->employee->date_of_birth);
                $pdf->setEncryption($dob->format('Ymd'));
            } catch (\Exception $e) {
                Log::warning('PDF encryption failed: ' . $e->getMessage());
            }
        }

        $filename = 'Payroll_' . ($payroll->employee->name ?? 'Unknown') . '_' . $carbonMonthYear->format('Y_m') . '.pdf';
        Log::info('PDF generated successfully.', ['filename' => $filename]);

        return $pdf->download($filename);
    }  
    // public function bulkDelete(Request $request)
    // {
    //     $idsRaw = $request->input('payroll_ids', '');
    //     $ids = is_array($idsRaw) ? $idsRaw : explode(',', $idsRaw);
    //     if (empty($ids)) {
    //         return back()->with('error', 'Tidak ada data yang dipilih.');
    //     }
    //     $matchedIds = [];
    //     Payrolls::chunk(100, function ($payrolls) use (&$matchedIds, $ids) {
    //         foreach ($payrolls as $payroll) {
    //             $hash = substr(hash('sha256', $payroll->id . env('APP_KEY')), 0, 8);
    //             if (in_array($hash, $ids)) {
    //                 $matchedIds[] = $payroll->id;
    //             }
    //         }
    //     });

    //     $deleted = Payrolls::whereIn('id', $matchedIds)->delete();

    //     return back()->with('success', "$deleted Delete success.");
    // }





// public function bulkDelete(Request $request)
// {
//     $idsRaw = $request->input('payroll_ids', '');
//     $ids = is_array($idsRaw) ? $idsRaw : explode(',', $idsRaw);

//     if (empty($ids)) {
//         return back()->with('error', 'Tidak ada data yang dipilih.');
//     }

//     $matchedIds = [];

//     Payrolls::chunk(100, function ($payrolls) use (&$matchedIds, $ids) {
//         foreach ($payrolls as $payroll) {
//             $hash = substr(hash('sha256', $payroll->id . env('APP_KEY')), 0, 8);
//             if (in_array($hash, $ids)) {
//                 $matchedIds[] = $payroll->id;
//             }
//         }
//     });

//     $payrollsToDelete = Payrolls::whereIn('id', $matchedIds)->get();

//     foreach ($payrollsToDelete as $payroll) {
//         if (!empty($payroll->attachment_file)) {
//             $filePath = public_path('storage/' . $payroll->attachment_file);

//             if (file_exists($filePath)) {
//                 try {
//                     Log::info('PATH FILE YANG DICARI: ' . $filePath);

//                     unlink($filePath);
//                     Log::info("File berhasil dihapus: $filePath");
//                 } catch (\Throwable $e) {
//                     Log::error("Gagal menghapus file $filePath: " . $e->getMessage());
//                 }
//             } else {
//                 Log::warning("File tidak ditemukan: $filePath");
//             }
//         }
//     }

//     $deleted = Payrolls::whereIn('id', $matchedIds)->delete();

//     // return back()->with('success', "$deleted data payroll berhasil dihapus (cek log untuk status file PDF).");
//     return response()->json(['deleted' => $deleted, 'path' => $filePath]);

// }
// public function bulkDelete(Request $request)
// {
//     $idsRaw = $request->input('payroll_ids', '');
//     $ids = is_array($idsRaw) ? $idsRaw : explode(',', $idsRaw);

//     if (empty($ids)) {
//         return back()->with('error', 'Tidak ada data yang dipilih.');
//     }

//     $matchedIds = [];

//     Payrolls::chunk(100, function ($payrolls) use (&$matchedIds, $ids) {
//         foreach ($payrolls as $payroll) {
//             $hash = substr(hash('sha256', $payroll->id . env('APP_KEY')), 0, 8);
//             if (in_array($hash, $ids)) {
//                 $matchedIds[] = $payroll->id;
//             }
//         }
//     });

//     $payrollsToDelete = Payrolls::whereIn('id', $matchedIds)->get();

//     // Tambahkan debug: tampilkan semua kolom payrolls yang diambil
//     $debugData = $payrollsToDelete->map(function ($p) {
//         return $p->toArray();
//     });

//     $allPaths = [];

//     foreach ($payrollsToDelete as $payroll) {
//         if (!empty($payroll->attachment_file)) {
//             $filePath = public_path('storage/' . $payroll->attachment_file);
//             $allPaths[] = $filePath;

//             if (file_exists($filePath)) {
//                 unlink($filePath);
//             }
//         }
//     }

//     $deleted = Payrolls::whereIn('id', $matchedIds)->delete();

//     return response()->json([
//         'deleted' => $deleted,
//         'checked_paths' => $allPaths,
//         'debug' => $debugData
//     ]);
// }


public function bulkDelete(Request $request)
{
    $idsRaw = $request->input('payroll_ids', '');
    $ids = is_array($idsRaw) ? $idsRaw : explode(',', $idsRaw);

    if (empty($ids)) {
        return back()->with('error', 'Tidak ada data yang dipilih.');
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

    // public function Importpayrolls(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|mimes:xlsx,csv,xls'
    //     ]);

    //     $errors = [];
    //     $import = new PayrollsImport($errors);
    //     $import->import($request->file('file'));

    //     if ($import->failures()->isNotEmpty()) {
    //         return back()->with([
    //             'failures' => $import->failures(), // INI YANG WAJIB
    //             'errors' => $errors, // opsional
    //         ]);
    //     }
    //     if (!empty($errors)) {
    //         return back()->with('failures', $errors);
    //     }
    //     return back()->with('success', 'Payrolls import successfully!');
    // }
    public function Importpayrolls(Request $request)
{
//     try {
//         Excel::import(new PayrollsImport, $request->file('file'));
//         return back()->with('success', 'Data payroll berhasil diimport!');
//     } catch (ValidationException $e) {
//         $failures = $e->failures();

//         $errors = [];
//         foreach ($failures as $failure) {
//             $errors[] = "Row {$failure->row()} - Column: {$failure->attribute()} - Message: {$failure->errors()[0]}";
//         }

//         // kirim ke view biar bisa ditampilkan
//         return back()->with('error_custom', $errors);
//     } catch (\Exception $e) {
//         // error lain yang bukan validasi Excel
//         return back()->with('error_custom', [$e->getMessage()]);
//     }
// }
try {
    Excel::import(new PayrollsImport, $request->file('file'));
    return back()->with('success', 'Data payroll berhasil diimport!');
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