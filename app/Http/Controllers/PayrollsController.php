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

use Illuminate\Support\Facades\DB;

class PayrollsController extends Controller
{
    public function index()
    {
        return view('pages.Payrolls.Payrolls');
    }
    // public function getPayrolls()
    // {
    //     $payrolls = Payrolls::with('Employee')->select(['id', 'employee_id','bonus','house_allowance','meal_allowance','transport_allowance','net_salary','deductions','salary','month_year' ])
    //         ->get()
    //         ->map(function ($payroll) {
    //             $payroll->id_hashed = substr(hash('sha256', $payroll->id . env('APP_KEY')), 0, 8);
    //             $payroll->action = '
    //                 <a href="' . route('Payrolls.edit', $payroll->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit Payrolls: ' . e($payroll->employee->employee_name) . '">
    //                     <i class="fas fa-user-edit text-secondary"></i>
    //                 </a>';
    //             return $payroll;
    //         });
    //     return DataTables::of($payrolls)
    //     ->addColumn('employee_name', function ($payroll) {
    //         return !empty($payroll->employee->employee_name) && !empty($payroll->employee->employee_name)
    //             ? $payroll->employee->employee_name
    //             : 'Empty';
    //     })
    //         ->rawColumns(['action','employee_name'])
    //         ->make(true);
    // }
    public function getPayrolls(Request $request)
{
    $payrollsQuery = Payrolls::with('Employee')
        ->select(['id', 'employee_id', 'bonus', 'house_allowance', 'meal_allowance', 'transport_allowance', 'deductions', 'salary', 'month_year','overtime','daily_allowance','attendance','late_fine','bpjs_ket','bpjs_kes','mesh','punishment']);

    // Filter berdasarkan month_year (Hanya Y-m, bukan Y-m-d)
    if ($request->has('month_year') && $request->month_year != '') {
        $payrollsQuery->whereRaw("DATE_FORMAT(month_year, '%Y-%m') = ?", [$request->month_year]);
    }

    $payrolls = $payrollsQuery->get()->map(function ($payroll) {
        $payroll->id_hashed = substr(hash('sha256', $payroll->id . env('APP_KEY')), 0, 8);
//         $payroll->action = '
//             <a href="' . route('Payrolls.edit', $payroll->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit Payrolls: ' . e($payroll->employee->employee_name) . '" title="Edit Payrolls: ' . e($payroll->employee->employee_name) . '">
//                 <i class="fas fa-user-edit text-secondary"></i>
//             </a>
//            <a href="' . route('Payrolls.show', $payroll->id_hashed) . '" target="_blank" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Show user" title="Show Payroll: ' . e($payroll->employee->employee_name) . '">
//     <i class="fas fa-eye text-secondary"></i>
// </a>'
// ;
$payroll->action = '
    <a href="' . route('Payrolls.edit', $payroll->id_hashed) . '" 
        class="mx-2" 
        data-bs-toggle="tooltip" 
        data-bs-original-title="Edit Payrolls: ' . e($payroll->employee->employee_name) . '" 
        title="Edit Payrolls: ' . e($payroll->employee->employee_name) . '">
        <i class="fas fa-user-edit text-secondary"></i>
    </a>
    <a href="' . route('Payrolls.show', $payroll->id_hashed) . '" 
        target="_blank" 
        class="mx-2" 
        data-bs-toggle="tooltip" 
        data-bs-original-title="Show Payroll: ' . e($payroll->employee->employee_name) . '" 
        title="Show Payroll: ' . e($payroll->employee->employee_name) . '">
        <i class="fas fa-eye text-secondary"></i>
    </a>
';

        return $payroll;
    });
    return DataTables::of($payrolls)
        ->addColumn('employee_name', function ($payroll) {
            return !empty($payroll->employee->employee_name) ? $payroll->employee->employee_name : 'Empty';
        })
        ->rawColumns(['action', 'employee_name'])
        ->make(true);
}
// public function show($hashedId)
// {
//     $payroll = Payrolls::with('employee')->get()->first(function ($u) use ($hashedId) {
//         $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
//         return $expectedHash === $hashedId;
//     });
//     if (!$payroll) {
//         abort(404, 'payroll not found.');
//     }
//     $salaryincome = ($payroll->attendance ?? 0) * ($payroll->daily_allowance ?? 0) + ($payroll->overtime ?? 0) + ($payroll->bonus ?? 0) + ($payroll->house_allowance ?? 0) + ($payroll->meal_allowance ?? 0) + ($payroll->transport_allowance ?? 0);
//     $salaryoutcome = ($payroll->mesh ?? 0) + ($payroll->punishment ?? 0) + ($payroll->late_fine ?? 0) + ($payroll->bpjs_ket ?? 0) + ($payroll->bpjs_kes ?? 0);
// // disini belum
//     return view('pages.Payrolls.show', [
//         'payroll' => $payroll,
//         'salaryincome' => $salaryincome,
//         'salaryoutcome' => $salaryoutcome,
//         'hashedId' => $hashedId,
//     ]);
// }

public function show($hashedId)
{
    $payroll = Payrolls::with(['employee.department', 'employee.position'])
        ->get()
        ->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
    if (!$payroll) {
        abort(404, 'Payroll not found.');
    }

    // Hitung total income dan outcome
    $salaryincome = ($payroll->attendance ?? 0) * ($payroll->daily_allowance ?? 0) + 
                   ($payroll->overtime ?? 0) + ($payroll->bonus ?? 0) + 
                   ($payroll->house_allowance ?? 0) + ($payroll->meal_allowance ?? 0) + 
                   ($payroll->transport_allowance ?? 0);
    
    $salaryoutcome = ($payroll->mesh ?? 0) + ($payroll->punishment ?? 0) + 
                    ($payroll->late_fine ?? 0) + ($payroll->bpjs_ket ?? 0) + 
                    ($payroll->bpjs_kes ?? 0);

    // Data untuk view
    $data = [
        'payroll' => $payroll,
        'salaryincome' => $salaryincome,
        'salaryoutcome' => $salaryoutcome,
        'hashedId' => $hashedId,
    ];
    
    // Generate PDF dengan konfigurasi khusus
    $pdf = Pdf::loadView('pages.Payrolls.show', $data);
    $pdf->setPaper('a4');
    $pdf->setOptions([
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true,
        'defaultFont' => 'dejavu sans',
        'dpi' => 100,
        'defaultMediaType' => 'screen',
        'isFontSubsettingEnabled' => true,
        'isPhpEnabled' => true,
        'debugCss' => false,
        'debugLayout' => false,
    ]);
    
    // Buat password dari tanggal lahir karyawan dengan format yyyymmdd
    $password = null;
    if ($payroll->employee && $payroll->employee->date_of_birth) {
        // Convert string date to DateTime object then format it
        $dateOfBirth = $payroll->employee->date_of_birth;
        
        // Periksa format tanggal dan konversi menjadi format yyyymmdd
        if (is_string($dateOfBirth)) {
            // Coba parse string date menjadi objek Carbon/DateTime
            try {
                $dateObj = \Carbon\Carbon::parse($dateOfBirth);
                $password = $dateObj->format('Ymd');
            } catch (\Exception $e) {
                // Jika gagal parsing, gunakan string asli dengan menghapus karakter '-'
                $password = str_replace(['-', '/', ' '], '', $dateOfBirth);
            }
        }
        
        // Set password pada PDF jika berhasil mendapatkan password
        if ($password) {
            $pdf->setEncryption($password);
        }
    }
    // Simpan file PDF
    $filename = 'payroll_' . $payroll->employee->employee_name . '_' . $payroll->month_year->format('Y_m') . '.pdf';
    $path = 'payrolls/' . $filename;
    Storage::disk('public')->put($path, $pdf->output());
    
    // Update database
    $payroll->attachment_path = $path;
    $payroll->save();

    // Return view HTML
    return view('pages.Payrolls.show', $data);
}
// public function show($hashedId)
// {
//     $payroll = Payrolls::with(['employee.department', 'employee.position'])
//         ->get()
//         ->first(function ($u) use ($hashedId) {
//             $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
//             return $expectedHash === $hashedId;
//         });
//     if (!$payroll) {
//         abort(404, 'Payroll not found.');
//     }

//     // Hitung total income dan outcome
//     $salaryincome = ($payroll->attendance ?? 0) * ($payroll->daily_allowance ?? 0) + 
//                    ($payroll->overtime ?? 0) + ($payroll->bonus ?? 0) + 
//                    ($payroll->house_allowance ?? 0) + ($payroll->meal_allowance ?? 0) + 
//                    ($payroll->transport_allowance ?? 0);
    
//     $salaryoutcome = ($payroll->mesh ?? 0) + ($payroll->punishment ?? 0) + 
//                     ($payroll->late_fine ?? 0) + ($payroll->bpjs_ket ?? 0) + 
//                     ($payroll->bpjs_kes ?? 0);

//     // Data untuk view
//     $data = [
//         'payroll' => $payroll,
//         'salaryincome' => $salaryincome,
//         'salaryoutcome' => $salaryoutcome,
//         'hashedId' => $hashedId,
//     ];
//     // Generate PDF dengan konfigurasi khusus
//     $pdf = Pdf::loadView('pages.Payrolls.show', $data);
//     $pdf->setPaper('a4');
//             $pdf->setOptions([
//                 'isHtml5ParserEnabled' => true,
//                 'isRemoteEnabled' => true,
//                 'defaultFont' => 'dejavu sans',
//                 'dpi' => 100,
//                 'defaultMediaType' => 'screen',
//                 'isFontSubsettingEnabled' => true,
//                 'isPhpEnabled' => true,
//                 'debugCss' => false,
//                 'debugLayout' => false,
//             ]);
//     // Simpan file PDF
//     $filename = 'payroll_' . $payroll->employee->employee_name . '_' . $payroll->month_year->format('Y_m') . '.pdf';
//     $path = 'payrolls/' . $filename;
//     Storage::disk('public')->put($path, $pdf->output());
//     // Update database
//     $payroll->attachment_path = $path;
//     $payroll->save();

//     // Return view HTML
//     return view('pages.Payrolls.show', $data);
// }




    public function edit($hashedId)
    {
        $payroll = Payrolls::with('employee')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$payroll) {
            abort(404, 'payroll not found.');
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

    // public function store(Request $request)
    // {
    //     // dd($request->all());

    //     $validatedData = $request->validate([
    //         'employee_id' => ['required', 'string','max:255', 'unique:employees_tables,employee_id',
    //             new NoXSSInput()],
    //         'bonus' => ['nullable','numeric',
    //             new NoXSSInput()],
    //         'house_allowance' => ['nullable','numeric',
    //             new NoXSSInput()],
    //         'house_allowance' => ['nullable','numeric',
    //             new NoXSSInput()],
            
    //         'meal_allowance' => ['nullable','numeric',
    //             new NoXSSInput()],
            
    //         'transport_allowance' => ['nullable','numeric',
    //             new NoXSSInput()],
            
    //         'net_salary' => ['nullable','numeric',
    //             new NoXSSInput()],
            
    //         'deductions' => ['nullable','numeric',
    //             new NoXSSInput()],
            
    //         'month_year' => ['nullable','numeric',
    //             new NoXSSInput()],
            
    //         'month_year' => ['nullable','numeric',
    //             new NoXSSInput()],
            
    //     ], [
            
    //         'employee_id.required' => 'Manager is required.',
    //         'manager_id.max' => 'Manager may not be greater than 255 characters.',
    //         'manager_id.string' => 'Manager must be a string.',
    //         'manager_id.unique' => 'Manager must be unique.',

    //         'department_name.required' => 'Department name is required.',
    //         'department_name.string' => 'Department name must be a string.',
    //         'department_name.max' => 'Department name may not be greater than 255 characters.',
    //         'department_name.unique' => 'Department name must be unique or already exists.',
    //     ]);
    //     try {
    //         DB::beginTransaction();
    //         $department = Departments::create([
    //             'department_name' => $validatedData['department_name'], 
    //             'manager_id' => $validatedData['manager_id'], 
    //         ]);
    //         DB::commit();
    //         return redirect()->route('pages.Department')->with('success', 'Department created Succesfully!');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return redirect()->back()
    //             ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
    //             ->withInput();
    //     }
    // }
    // public function store(Request $request)
    // {
    //     // dd($request->all());

    //     $validatedData = $request->validate([
    //         'employee_id' => ['nullable', 'string','max:255', 'unique:employees_tables,employee_id',
    //             new NoXSSInput()],  
    //     ], [
            
    //         'employee_id.required' => 'Manager is required.',
    //         'manager_id.max' => 'Manager may not be greater than 255 characters.',
    //         'manager_id.string' => 'Manager must be a string.',
    //         'manager_id.unique' => 'Manager must be unique.',

    //         'department_name.required' => 'Department name is required.',
    //         'department_name.string' => 'Department name must be a string.',
    //         'department_name.max' => 'Department name may not be greater than 255 characters.',
    //         'department_name.unique' => 'Department name must be unique or already exists.',
    //     ]);
    //     try {
    //         DB::beginTransaction();
    //         $department = Departments::create([
    //             'department_name' => $validatedData['department_name'], 
    //             'manager_id' => $validatedData['manager_id'], 
    //         ]);
    //         DB::commit();
    //         return redirect()->route('pages.Department')->with('success', 'Department created Succesfully!');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return redirect()->back()
    //             ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
    //             ->withInput();
    //     }
    // }
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
            
            'attendance' => ['nullable','numeric',
                new NoXSSInput()],
            
            'daily_allowance' => ['nullable','numeric',
                new NoXSSInput()],
            
            'overtime' => ['nullable','numeric',
                new NoXSSInput()],   
            'bpjs_ket' => ['nullable','numeric',
                new NoXSSInput()],
            'bpjs_kes' => ['nullable','numeric',
                new NoXSSInput()],
            'mesh' => ['nullable','numeric',
                new NoXSSInput()],
            'punishment' => ['nullable','numeric',
                new NoXSSInput()],
            'late_fine' => ['nullable','numeric',
                new NoXSSInput()],
            'salary' => ['nullable','numeric',
                new NoXSSInput()],
            'information' => ['nullable','string',
                new NoXSSInput()],
        ], [
            'bonus.numeric' => 'Bonus must be a number.',
            'house_allowance.numeric' => 'House allowance must be a number.',
            'meal_allowance.numeric' => 'Meal allowance must be a number.',
            'transport_allowance.numeric' => 'Transport allowance must be a number.',
            'overtime.numeric' => 'Net salary must be a number.',
            'daily_allowance.numeric' => 'Net salary must be a number.',
            'attendance.numeric' => 'Net salary must be a number.',
            'bpjs_kes.numeric' => 'bpjs kesehatan must be a number.',
            'bpjs_ket.numeric' => 'bpjs ketenagakerjaan must be a number.',
            'mesh.numeric' => 'mesh salary must be a number.',
            'punishment.numeric' => 'punishment salary must be a number.',
            'late_fine.numeric' => 'late fine salary must be a number.',
            'deductions.numeric' => 'Deductions must be a number.',
          ]);
          $calculatedDeduction = 
          ($validatedData['mesh'] ?? 0) +
          ($validatedData['punishment'] ?? 0) +
          ($validatedData['bpjs_ket'] ?? 0) +
          ($validatedData['bpjs_kes'] ?? 0) +
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
            'bonus' => $validatedData['bonus'],
            'house_allowance' => $validatedData['house_allowance'],
            'meal_allowance' => $validatedData['meal_allowance'],
            'transport_allowance' => $validatedData['transport_allowance'],
            'attendance' => $validatedData['attendance'],
            'daily_allowance' => $validatedData['daily_allowance'],
            'overtime' => $validatedData['overtime'],
            'mesh' => $validatedData['mesh'],
            'punishment' => $validatedData['punishment'],
            'late_fine' => $validatedData['late_fine'],
            'bpjs_ket' => $validatedData['bpjs_ket'],
            'bpjs_kes' => $validatedData['bpjs_kes'],
            'deductions' => $calculatedDeduction,
            'salary' => $calculatedSalary, // Gunakan nilai yang dihitung
            'information' => $validatedData['information'] ?? null,
        ];
        DB::beginTransaction();
        $payroll->update($payrollData);
        DB::commit();
        return redirect()->route('pages.Payrolls')->with('success', 'Payrolls updated successfully.');
    }
}
