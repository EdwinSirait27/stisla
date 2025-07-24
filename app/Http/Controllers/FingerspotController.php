<?php

namespace App\Http\Controllers;

use App\Models\Attendances;
use App\Models\User;
use Illuminate\Http\Request;
    use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SinkronPinFingerspotImport;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class FingerspotController extends Controller
{

public function getPins()
{
    $isHeadHR = auth()->user()->hasRole('HeadHR');

    // $employees = User::with([
    //     'Employee.store',
    //     'Employee.position',
    //     'Employee.department',
    // ])
    // ->whereHas('Employee', function ($query) {
    //     $query->whereIn('status', ['Active', 'Pending','Mutation']);
    // })
    // ->select(['id', 'employee_id'])
    // ->get()
    // ->map(function ($employee) use ($isHeadHR) {
    //     $employee->id_hashed = substr(hash('sha256', $employee->id . env('APP_KEY')), 0, 8);
    //     $employeeName = optional($employee->Employee)->employee_name;

    //     $employee->action = $isHeadHR
    //         ? '<a href="' . route('Employee.edit', $employee->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" title="Edit Employee: ' . e($employeeName) . '">
    //                 <i class="fas fa-user-edit text-secondary"></i>
    //            </a>'
    //         : '';

    //     return $employee;
    // });
  $employees = User::with([
        'Employee.store',
        'Employee.position',
        'Employee.department',
    ])
    ->whereHas('Employee', function ($query) {
        $query->whereIn('status', ['Active', 'Pending', 'Mutation']);
    })
    ->select(['id', 'employee_id'])
    ->get()
    ->sortBy(function ($employee) {
        $pin = optional($employee->Employee)->pin;
        return is_null($pin) ? -INF : (int) $pin;
    })
    ->values()
    ->map(function ($employee) use ($isHeadHR) {
        $employee->id_hashed = substr(hash('sha256', $employee->id . env('APP_KEY')), 0, 8);
        $employeeName = optional($employee->Employee)->employee_name;

        $employee->action = $isHeadHR
            ? '<a href="' . route('Employee.edit', $employee->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" title="Edit Employee: ' . e($employeeName) . '">
                    <i class="fas fa-user-edit text-secondary"></i>
               </a>'
            : '';

        return $employee;
    });





    return DataTables::of($employees)
        ->addColumn('name_store', fn($e) => optional(optional($e->Employee)->store)->name ?? 'Empty')
        ->addColumn('position_name', fn($e) => optional(optional($e->Employee)->position)->name ?? 'Empty')
        ->addColumn('department_name', fn($e) => optional(optional($e->Employee)->department)->department_name ?? 'Empty')
        ->addColumn('employee_name', fn($e) => optional($e->Employee)->employee_name ?? 'Empty')
        ->addColumn('created_at', fn($e) => optional($e->Employee)->created_at ?? 'Empty')
        ->addColumn('length_of_service', fn($e) => optional($e->Employee)->length_of_service ?? 'Empty')
        ->addColumn('status', fn($e) => optional($e->Employee)->status ?? 'Empty')
        ->addColumn('pin', fn($e) => optional($e->Employee)->pin ?? 'Empty')
        ->rawColumns(['position_name','pin', 'status', 'department_name', 'created_at', 'employee_name', 'name_store', 'action'])
        ->make(true);
}

public function indexfingerspot()
    {
        $files = Storage::disk('public')->files('templatefingerspot');
        return view('pages.Importfingerspot.Importfingerspot', compact('files'));
    }
public function index()
    {
        return view('pages.Fingerspot.Fingerspot');
    }
public function sinkronkanPIN(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xls,xlsx,csv',
    ]);

    $import = new SinkronPinFingerspotImport();
    Excel::import($import, $request->file('file'));

    if (!empty($import->failures())) {
        return back()->withErrors($import->failures());
    }
    return back()->with('success', 'PIN sync successfully.');
}

 public function downloadfingerspot($filename)
    {
        $path = 'templatefingerspot/' . $filename;

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->download($path);
        }
        abort(404);
    }
//       public function Importfingerspot(Request $request)
// {
//     $request->validate([
//         'file' => 'required|mimes:xlsx,csv,xls'
//     ]);

//     $errors = [];
//     $import = new SinkronPinFingerspotImport($errors);
//     $import->import($request->file('file'));

//     if ($import->failures()->isNotEmpty()) {
//     return back()->with([
//         'failures' => $import->failures(), // INI YANG WAJIB
//         'errors' => $errors, // opsional
//     ]);
// }
//     if (!empty($errors)) {
//         return back()->with('failures', $errors);
//     }

//     return back()->with('success', 'Payrolls import successfully!');
// }


}
