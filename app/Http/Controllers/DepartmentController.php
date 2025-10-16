<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Departments;
use App\Rules\NoXSSInput;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    public function index()
    {
        return view('pages.Department.Department');
    }
    // public function getDepartments()
    // {
    //     $departments = Departments::with('user.Employee')->select(['id', 'department_name', 'manager_id'])
    //         ->get()
    //         ->map(function ($department) {
    //             $department->id_hashed = substr(hash('sha256', $department->id . env('APP_KEY')), 0, 8);
    //             $department->action = '
    //                 <a href="' . route('Department.edit', $department->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit Department: ' . e($department->department_name) . '">
    //                     <i class="fas fa-user-edit text-secondary"></i>
    //                 </a>';
    //             return $department;
    //         });
    //     return DataTables::of($departments)
    //         ->addColumn('employee_name', function ($department) {
    //             return !empty($department->user->Employee) && !empty($department->user->Employee->employee_name)
    //                 ? $department->user->Employee->employee_name
    //                 : 'Empty';
    //         })
    //         ->addColumn('company_name', function ($department) {
    //             return !empty($department->user->Employee->company) && !empty($department->user->Employee->company->name)
    //                 ? $department->user->Employee->company->name
    //                 : 'Empty';
    //         })
    //         ->rawColumns(['action', 'employee_name','company_name'])
    //         ->make(true);
    // }
    public function getDepartments()
{
    // Ambil department dengan employee yang is_manager = 1
    $departments = Departments::with(['employees' => function ($query) {
            $query->where('is_manager', 1);
        }])
        ->select(['id', 'department_name'])
        ->get()
        ->map(function ($department) {
            $department->id_hashed = substr(hash('sha256', $department->id . env('APP_KEY')), 0, 8);
            $department->action = '
                <a href="' . route('Department.edit', $department->id_hashed) . '" class="mx-3" 
                   data-bs-toggle="tooltip" 
                   data-bs-original-title="Edit Department"
                   title="Edit Department: ' . e($department->department_name) . '">
                    <i class="fas fa-user-edit text-secondary"></i>
                </a>';
            return $department;
        });

    return DataTables::of($departments)
        ->addColumn('employee_name', function ($department) {
            if (!empty($department->employees) && $department->employees->count() > 0) {
                // Bisa ada lebih dari satu manager, ambil nama-namanya
                return $department->employees->pluck('employee_name')->join(', ');
            }
            return 'Empty';
        })
        ->addColumn('company_name', function ($department) {
            if (!empty($department->employees) && $department->employees->count() > 0) {
                // Ambil company pertama manager
                $manager = $department->employees->first();
                return !empty($manager->company) ? $manager->company->name : 'Empty';
            }
            return 'Empty';
        })
        ->rawColumns(['action', 'employee_name', 'company_name'])
        ->make(true);
}

    public function edit($hashedId)
    {
        $department = Departments::with('user.Employee')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$department) {
            abort(404, 'Department not found.');
        }
        return view('pages.Department.edit', [
            'department' => $department,
            'hashedId' => $hashedId,
        ]);
    }
    public function create()
    {
        return view('pages.Department.create');
    }
    public function store(Request $request)
    {
        // dd($request->all());
        $validatedData = $request->validate([
            'department_name' => [
                'required',
                'string',
                'max:255',
                'unique:departments_tables,department_name',
                new NoXSSInput()
            ],
           
        ], [
            'department_name.required' => 'Department name is required.',
            'department_name.string' => 'Department name must be a string.',
            'department_name.max' => 'Department name may not be greater than 255 characters.',
            'department_name.unique' => 'Department name must be unique or already exists.',
           ]);
        try {
            DB::beginTransaction();
            $department = Departments::create([
                'department_name' => $validatedData['department_name'],
            ]);
            DB::commit();
            return redirect()->route('pages.Department')->with('success', 'Department created Succesfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
                ->withInput();
        }
    }
    public function update(Request $request, $hashedId)
    {
        $department = Departments::with('user.Employee')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$department) {
            return redirect()->route('pages.Department')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'department_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments_tables')->ignore($department->id),
                new NoXSSInput()
            ],
           
        ], [
            'department_name.required' => 'name wajib diisi.',
            'manager_id.required' => 'Manager wajib diisi.',
            'department_name.string' => 'name hanya boleh berupa teks.',
        ]);
        $departmentData = [
            'department_name' => $validatedData['department_name'],
            'manager_id' => $validatedData['manager_id'],
        ];
        DB::beginTransaction();
        $department->update($departmentData);
        DB::commit();
        return redirect()->route('pages.Department')->with('success', 'Department Berhasil Diupdate.');
    }
}
