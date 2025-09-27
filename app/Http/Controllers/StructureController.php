<?php

namespace App\Http\Controllers;
use App\Models\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Structure;
use App\Models\User;
use App\Rules\NoXSSInput;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class StructureController extends Controller
{
    public function index()
    {
        return view('pages.Structures.Structures');
    }
    public function getStructures()
    {
        $employees = User::whereHas('Employee', function ($q) {
            $q->whereIn('status', ['Active', 'Pending']);
        })
            ->with([
                'Employee' => function ($q) {
                    $q->whereIn('status', ['Active', 'Pending']);
                },
                'Employee.employees',
                'Employee.company',
            ])
            ->select(['id', 'employee_id'])
            ->get()
            ->map(function ($employee) {
                $employee->id_hashed = substr(hash('sha256', $employee->id . env('APP_KEY')), 0, 8);
                $employee->action = '
                    <a href="' . route('Structures.edit', $employee->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit structure: ' . e($employee->Employee->employee_name) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
                return $employee;
            });
        return DataTables::of($employees)
            ->addColumn('employee_name', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->employee_name)
                    ? $employee->Employee->employee_name
                    : 'Empty';
            })

            ->addColumn('level', function ($employee) {
                return !empty($employee->Employee->employees) && !empty($employee->Employee->employees->employee_name)
                    ? $employee->Employee->employees->employee_name
                    : 'Empty';
            })
            ->addColumn('is_manager', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->is_manager)
                    ? $employee->Employee->is_manager
                    : 'Empty';
            })
            ->rawColumns(['action', 'employee_name', 'level','is_manager'])
            ->make(true);
    }
    public function edit($hashedId)
    {
        $employee = User::with('Employee.employees', 'Employee.company', 'Employee')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$employee) {
            abort(404, 'Structures not found.');
        }

        $employees = Employee::where('status', ['Active','Pending'])->pluck('employee_name', 'id');

        return view('pages.Structures.edit', [
            'employee' => $employee,
            'hashedId' => $hashedId,
            'employees' => $employees,
        ]);
    }
    // public function update(Request $request, $hashedId)
    // {
    //     $employee = User::with('Employee.employees', 'Employee.company')->get()->first(function ($u) use ($hashedId) {
    //         $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
    //         return $expectedHash === $hashedId;
    //     });
    //     if (!$employee) {
    //         return redirect()->route('pages.Structures')->with('error', 'ID tidak valid.');
    //     }
    //     $validatedData = $request->validate([
    //         'level_id' => [
    //             'nullable',
    //             'string',
    //             'max:255',
    //             new NoXSSInput()
    //         ],
    //         'is_manager' => [
    //             'nullable',
    //             'boolean',
    //             new NoXSSInput()
    //         ],
    //     ]);
    //     $employeeData = [
    //         'level_id' => $validatedData['level_id'],
    //         'is_manager'  => $validatedData['is_manager'] ?? 0,
    //     ];
    //     DB::beginTransaction();
    //     $employee->update($employeeData);
    //     DB::commit();
    //     return redirect()->route('pages.Structures')->with('success', 'Structure Updated Successfully.');
    // }
    public function create()
    {
        $employees = Employee::pluck('employee_name', 'id');

        return view('pages.Structures.create', compact('employees'));
    }
    public function store(Request $request)
    {
        // dd($request->all());
        $validatedData = $request->validate([

          

            'level_id' => [
                'nullable',
                'max:255',
                new NoXSSInput()
            ],
            'is_manager' => [
                'nullable',
                'boolean',
                new NoXSSInput()
            ],
        ]);
        try {
            DB::beginTransaction();
            $employee = Structure::create([
                // 'employee_id' => $validatedData['employee_id'],
                'level_id' => $validatedData['level_id'],
                'is_manager' => $validatedData['is_manager'] ?? 0,
            ]);
            DB::commit();
            return redirect()->route('pages.Structures')->with('success', 'Structure created Succesfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Error while creating data: ' . $e->getMessage()])
                ->withInput();
        }
    }
}
