<?php

namespace App\Http\Controllers;

use App\Models\Banks;
use App\Models\Company;
use App\Models\Departments;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Grading;
use App\Models\Payrolls;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\DataTables;
use App\Models\Stores;
use App\Models\Structuresnew;
use Illuminate\Support\Facades\Hash;
use App\Rules\NoXSSInput;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Mail\WelcomeEmployeeMail;
use App\Models\Groups;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Log;
class EmployeeController extends Controller
{
    public function indexall()
    {
        $storeList = Stores::select('name')->distinct()->pluck('name');
        $statusList = Employee::select('status')->distinct()->pluck('status');
        return view('pages.Employeeall.Employeeall', compact('storeList', 'statusList'));
    }
    public function index()
    {
        return view('pages.Employee.Employee');
    }
    public function getActivities(Request $request)
    {
        if ($request->ajax()) {
            $query = Activity::where('log_name', 'employee')
                ->with(['causer.employee'])
                ->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('description', function ($row) {
                    return $row->description ?? '-';
                })
                ->addColumn('causer', function ($row) {
                    return $row->causer->employee->employee_name;
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at->format('d M Y H:i');
                })
                ->addColumn('changes', function ($row) {
                    return json_encode($row->properties['attributes'] ?? []);
                })

                // 🔍 Tambahkan bagian filter untuk search
                ->filter(function ($instance) use ($request) {
                    if ($request->has('search') && $request->get('search')['value'] != '') {
                        $search = $request->get('search')['value'];

                        $instance->where(function ($q) use ($search) {
                            $q->where('description', 'like', "%{$search}%")
                                ->orWhereHas('causer.employee', function ($q2) use ($search) {
                                    $q2->where('employee_name', 'like', "%{$search}%");
                                })
                                ->orWhereHas('causer.employee', function ($q3) use ($search) {
                                    $q3->where('employee_name', 'like', "%{$search}%");
                                });
                        });
                    }
                })
                ->rawColumns(['description'])
                ->make(true);
        }
    }
    // public function getEmployees(Request $request, DataTables $dataTables)
    // {
    //     $isHeadHR = auth()->user()->hasAnyRole(['HeadHR', 'HR']);

    //     $employees = User::with([
    //         'Employee.structuresnew.company',
    //         'Employee.structuresnew',
    //         'Employee.structuresnew.store',
    //         'Employee.structuresnew.position',
    //         'Employee.structuresnew.department',
    //         'Employee.grading',
    //         'Employee.employees'
    //     ])
    //         ->select(['id', 'employee_id'])
    //         ->get()
    //         ->map(function ($employee) use ($isHeadHR) {
    //             $employee->id_hashed = substr(hash('sha256', $employee->id . env('APP_KEY')), 0, 8);
    //             $employeeName = optional($employee->Employee)->employee_name;

    //             $employee->action = $isHeadHR
    //                 ? '<a href="' . route('Employee.edit', $employee->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" title="Edit Employee: ' . e($employeeName) . '">
    //                 <i class="fas fa-user-edit text-secondary"></i>
    //            </a>
    //            <a href="' . route('Employee.show', $employee->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" title="show Employee: ' . e($employeeName) . '">
    //                 <i class="fas fa-eye text-secondary"></i>
    //            </a>'
    //                 : '';

    //             return $employee;
    //         });
    //     return DataTables::of($employees)
    //         ->addColumn('company_name', fn($e) => optional(optional($e->Employee)->structuresnew->company)->name ?? 'Empty')
    //         ->addColumn('grading_name', fn($e) => optional(optional($e->Employee)->structuresnew->grading)->grading_name ?? 'Empty')
    //         ->addColumn('location_name', fn($e) => optional(optional($e->Employee)->structuresnew->store)->name ?? 'Empty')
    //         ->addColumn('position_name', fn($e) => optional(optional($e->Employee)->structuresnew->position)->name ?? 'Empty')
    //         ->addColumn('department_name', fn($e) => optional(optional($e->Employee)->structuresnew->department)->department_name ?? 'Empty')
    //         ->addColumn('status_employee', fn($e) => optional($e->Employee)->status_employee ?? 'Empty')

    //         // ->addColumn('foto', function ($e) {
    //         //     $foto = optional($e->Employee)->foto;
    //         //     return $foto
    //         //         ? asset('storage/employeefoto' . $foto)
    //         //         : 'Empty';
    //         // })

    //         ->addColumn('employee_name', fn($e) => optional($e->Employee)->employee_name ?? 'Empty')
    //         ->addColumn('status', fn($e) => optional($e->Employee)->status ?? 'Empty')
    //         ->rawColumns(['position_name', 'status', 'department_name', 'created_at', 'employee_name', 'location_name', 'status_employee', 'grading_name', 'action'])
    //         ->make(true);
    // }
    public function getEmployees(Request $request, DataTables $dataTables)
    {
        // $isHeadHR = auth()->user()->hasRole('HeadHR');
        $isHeadHR = auth()->user()->hasAnyRole(['HeadHR', 'HR','Admin']);

        $employees = User::with([
            'Employee.company',
            'Employee.store',
            'Employee.position',
            'Employee.structuresnew.position',
            'Employee.department',
            'Employee.grading',
            'Employee.group',
            'Employee.employees',
            'Employee.structuresnew.company',
            'Employee.structuresnew'
        ])
            ->select(['id', 'employee_id'])
            ->get()
            ->map(function ($employee) use ($isHeadHR) {
                $employee->id_hashed = substr(hash('sha256', $employee->id . env('APP_KEY')), 0, 8);
                $employeeName = optional($employee->Employee)->employee_name;

                $employee->action = $isHeadHR
                    ? '<a href="' . route('Employee.edit', $employee->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" title="Edit Employee: ' . e($employeeName) . '">
                    <i class="fas fa-user-edit text-secondary"></i>
               </a>
               <a href="' . route('Employee.show', $employee->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" title="show Employee: ' . e($employeeName) . '">
                    <i class="fas fa-eye text-secondary"></i>
               </a>'
                    : '';

                return $employee;
            });
        return DataTables::of($employees)
            ->addColumn('name_company', fn($e) => optional(optional($e->Employee)->company)->name ?? 'Empty')
            ->addColumn('grading_name', fn($e) => optional(optional($e->Employee)->grading)->grading_name ?? 'Empty')
            ->addColumn('group_name', fn($e) => optional(optional($e->Employee)->group)->group_name ?? 'Empty')
            ->addColumn('name', fn($e) => optional(optional($e->Employee)->store)->name ?? 'Empty')
            ->addColumn('oldposition_name', fn($e) => optional(optional($e->Employee)->position)->name ?? 'Empty')
            ->addColumn('position_name', fn($e) => optional(optional($e->Employee->structuresnew)->position)->name ?? 'Empty')
            ->addColumn('department_name', fn($e) => optional(optional($e->Employee)->department)->department_name ?? 'Empty')
            ->addColumn('status_employee', fn($e) => optional($e->Employee)->status_employee ?? 'Empty')
            ->addColumn('employee_name', fn($e) => optional($e->Employee)->employee_name ?? 'Empty')
            ->addColumn('nip', fn($e) => optional($e->Employee)->employee_pengenal ?? 'Empty')
            ->addColumn('created_at', fn($e) => optional($e->Employee)->created_at ?? 'Empty')
            ->addColumn('length_of_service', fn($e) => optional($e->Employee)->length_of_service ?? 'Empty')
            ->addColumn('status', fn($e) => optional($e->Employee)->status ?? 'Empty')
            ->rawColumns(['nip','group_name','position_name','oldposition_name', 'status', 'department_name', 'company_name','created_at', 'employee_name', 'name', 'status_employee', 'grading_name', 'action'])
            ->make(true);
    }
    public function getEmployeesall()
    {
        $storeFilter = request()->get('name');
        $statusFilter = request()->get('status');
        $query = User::with([
            'Employee',
            'Employee.company',
            'Employee.store',
            'Employee.position',
            'Employee.department',
            'Employee.bank',
            'Employee.grading',
            'Employee.group'
        ])->select(['id', 'username', 'employee_id']);

        if (!empty($storeFilter)) {
            $query->whereHas('Employee.store', function ($q) use ($storeFilter) {
                $q->where('name', $storeFilter);
            });
        }

        if (!empty($statusFilter)) {
            $query->whereHas('Employee', function ($q) use ($statusFilter) {
                $q->whereIn('status', $statusFilter);
            });
        }

        $employees = $query->get()->map(function ($employee) {
            $employee->id_hashed = substr(hash('sha256', $employee->id . env('APP_KEY')), 0, 8);
            if (auth()->user()->hasAnyRole(['HeadHR', 'HR'])) {
                $employee->action = '
        <a href="' . route('Employee.edit', $employee->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user" title="Edit Employee: ' . e(optional($employee->Employee)->employee_name) . '">
            <i class="fas fa-user-edit text-secondary"></i>
        </a>';
            } else {
                $employee->action = '';
            }
            return $employee;
        });
        $columns = [
            'name' => 'store.name',
            'name_company' => 'company.name',
            'grading_name' => 'grading.grading_name',
            'grading_code' => 'grading.grading_code',
            'position_name' => 'position.name',
            'employee_pengenal',
            'department_name' => 'department.department_name',
            'employee_name',
            'id' => 'id',
            'status_employee',
            'join_date',
            'marriage',
            'child',
            'telp_number',
            'nik',
            'gender',
            'date_of_birth',
            'place_of_birth',
            'biological_mother_name',
            'religion',
            'current_address',
            'id_card_address',
            'last_education',
            'institution',
            'npwp',
            'bpjs_kes',
            'bpjs_ket',
            'email',
            'emergency_contact_name',
            'notes',
            'created_at',
            'bank_name' => 'bank.name',
            'bank_account_number',
            'pin',
            'status'
        ];
        $dataTable = DataTables::of($employees);
        foreach ($columns as $key => $relationPath) {
            $column = is_string($key) ? $key : $relationPath;
            $dataTable->addColumn($column, function ($employee) use ($relationPath) {
                $value = data_get($employee->Employee, $relationPath);
                return $value ?: 'Empty';
            });
        }
        return $dataTable
            ->addColumn('action', function ($employee) {
                return $employee->action;
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    // public function edit($hashedId)
    // {
    //     $employee = User::with('Employee', 'Employee.store', 'Employee.department', 'Employee.position', 'Employee.bank', 'Employee.grading', 'Employee.employees','Employee.structuresnew')->get()->first(function ($u) use ($hashedId) {
    //         $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
    //         return $expectedHash === $hashedId;
    //     });
    //     if (!$employee) {
    //         abort(404, 'Employee not found.');
    //     }
    //     $positions = Position::get();
    //     $companys = Company::get();
    //     $employees = Employee::where('status', 'Active')->pluck('employee_name', 'id');
    //     $departments = Departments::with('user.Employee')->get();
    //     $stores = Stores::with('user.Employee')->get();
    //     $status_employee = ['PKWT', 'DW', 'PKWTT', 'On Job Training'];
    //     $child = ['0', '1', '2', '3', '4', '5'];
    //     $marriage = ['Yes', 'No'];
    //     $gender = ['Male', 'Female', 'MD'];
    //     $status = ['Pending', 'Inactive', 'On Leave', 'Mutation', 'Active', 'Resign'];
    //     $banks = Banks::get();
    //     $structures = Structuresnew::with('company','department','store','position')->get();
    //     $religion = ['Buddha', 'Catholic Christian', 'Christian', 'Confusian', 'Hindu', 'Islam'];
    //     $last_education = ['Elementary School', 'Junior High School', 'Senior High School', 'Diploma I', 'Diploma II', 'Diploma III', 'Diploma IV', 'Bachelor Degree', 'Masters degree', 'Vocational School', 'Lord'];
    //     return view('pages.Employee.edit', [
    //         'employee' => $employee,
    //         'status_employee' => $status_employee,
    //         'child' => $child,
    //         'structures' => $structures,
    //         'employees' => $employees,
    //         'companys' => $companys,
    //         'stores' => $stores,
    //         'marriage' => $marriage,
    //         'gender' => $gender,
    //         'status' => $status,
    //         'banks' => $banks,
    //         'religion' => $religion,
    //         'last_education' => $last_education,
    //         'positions' => $positions,
    //         'departments' => $departments,
    //         'hashedId' => $hashedId,
    //     ]);
    // }
    public function edit($hashedId)
    {
        $employee = User::with('Employee', 'Employee.store', 'Employee.department', 'Employee.position', 'Employee.bank', 'Employee.grading','Employee.group', 'Employee.employees','Employee.structuresnew')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$employee) {
            abort(404, 'Employee not found.');
        }
        $positions = Position::get();
        $companys = Company::get();
        $gradings = Grading::get();
        $groups = Groups::get();
        $employees = Employee::where('status', 'Active')->pluck('employee_name', 'id');
        $departments = Departments::with('user.Employee')->get();
        $stores = Stores::with('user.Employee')->get();
        $status_employee = ['PKWT', 'DW', 'PKWTT', 'On Job Training'];
        $child = ['0', '1', '2', '3', '4', '5'];
        $marriage = ['Yes', 'No'];
        $gender = ['Male', 'Female', 'MD'];
        $status = ['Pending','On Leave', 'Mutation', 'Active', 'Resign'];
        $banks = Banks::get();
      $usedStructureIds = Employee::whereNotNull('structure_id')->pluck('structure_id')->toArray();

    $structures = Structuresnew::with('company', 'department', 'store', 'position','submissionposition')
        ->whereNotIn('id', $usedStructureIds)
        ->orWhere('id', optional($employee->Employee)->structure_id) // biar structure miliknya sendiri tetap muncul
        ->get();
        $religion = ['Buddha', 'Catholic Christian', 'Christian', 'Confucian', 'Hindu', 'Islam'];
        $last_education = ['Elementary School', 'Junior High School', 'Senior High School', 'Diploma I', 'Diploma II', 'Diploma III', 'Diploma IV', 'Bachelor Degree', 'Masters degree', 'Vocational School', 'Lord'];
        return view('pages.Employee.edit', [
            'employee' => $employee,
            'status_employee' => $status_employee,
            'child' => $child,
            'structures' => $structures,
            'employees' => $employees,
            'companys' => $companys,
            'stores' => $stores,
            'marriage' => $marriage,
            'status' => $status,
            'gender' => $gender,
            'gradings' => $gradings,
            'groups' => $groups,
            'banks' => $banks,
            'religion' => $religion,
            'last_education' => $last_education,
            'positions' => $positions,
            'departments' => $departments,
            'hashedId' => $hashedId,
        ]);
    }
    // public function show($hashedId)
    // {
    //     $employee = User::with('Employee', 'Employee.store', 'Employee.grading', 'Employee.department', 'Employee.position', 'Employee.bank', 'Employee.employees','Employee.structuresnew')->get()->first(function ($u) use ($hashedId) {
    //         $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
    //         return $expectedHash === $hashedId;
    //     });
    //     if (!$employee) {
    //         abort(404, 'Employee not found.');
    //     }
    //        $structures = Structuresnew::with('company', 'department', 'store', 'position','submissionposition')->Where('id', optional($employee->Employee)->structure_id) // biar structure miliknya sendiri tetap muncul
    //     ->get();
    //     $positions = Position::get();
    //     $companys = Company::get();
    //     $employees = Employee::where('status', 'Active')
    //         ->pluck('employee_name', 'id');
    //     $departments = Departments::with('user.Employee')->get();
    //     $stores = Stores::with('user.Employee')->get();
    //     $status_employee = ['PKWT', 'DW', 'PKWTT', 'On Job Training'];
    //     $child = ['0', '1', '2', '3', '4', '5'];
    //     $marriage = ['Yes', 'No'];
    //     $gender = ['Male', 'Female', 'MD'];
    //     $status = ['Pending', 'Inactive', 'On Leave', 'Mutation', 'Active', 'Resign'];
    //     $banks = Banks::get();
    //     $gradings = Grading::get();
    //     $religion = ['Buddha', 'Catholic Christian', 'Christian', 'Confusian', 'Hindu', 'Islam'];
    //     $last_education = ['Elementary School', 'Junior High School', 'Senior High School', 'Diploma I', 'Diploma II', 'Diploma III', 'Diploma IV', 'Bachelor Degree', 'Masters degree', 'Vocational School', 'Lord'];
    //     return view('pages.Employee.show', [
    //         'employee' => $employee,
    //         'employees' => $employees,
    //         'status_employee' => $status_employee,
    //         'child' => $child,
    //         'employees' => $employees,
    //         'companys' => $companys,
    //         'stores' => $stores,
    //         'marriage' => $marriage,
    //         'gender' => $gender,
    //         'gradings' => $gradings,
    //         'status' => $status,
    //         'banks' => $banks,
    //         'gradings' => $gradings,
    //         'religion' => $religion,
    //         'structures' => $structures,
    //         'last_education' => $last_education,
    //         'positions' => $positions,
    //         'departments' => $departments,
    //         'hashedId' => $hashedId,
    //     ]);
    // }
    public function show($hashedId)
{
    $employee = User::with(
        'Employee',
        'Employee.store',
        'Employee.grading',
        'Employee.group',
        'Employee.department',
        'Employee.position',
        'Employee.bank',
        'Employee.employees',
        'Employee.structuresnew',
        'Employee.structuresnew.submissionposition'
    )->get()->first(function ($u) use ($hashedId) {
        $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
        return $expectedHash === $hashedId;
    });

    if (!$employee) {
        abort(404, 'Employee not found.');
    }

    // ---------------------------
    // Tambahkan logic aman disini
    // ---------------------------

    $isManager = optional(optional($employee->Employee)->structuresnew)->is_manager;

    // Ambil structure
    $structures = Structuresnew::with('company', 'department', 'store', 'position', 'submissionposition')
        ->where('id', optional($employee->Employee)->structure_id)
        ->get();

    // Data lain
    $positions = Position::get();
    $companys = Company::get();
    $employees = Employee::where('status', 'Active')->pluck('employee_name', 'id');
    $departments = Departments::with('user.Employee')->get();
    $stores = Stores::with('user.Employee')->get();
    $status_employee = ['PKWT', 'DW', 'PKWTT', 'On Job Training'];
    $child = ['0', '1', '2', '3', '4', '5'];
    $marriage = ['Yes', 'No'];
    $gender = ['Male', 'Female', 'MD'];
    $status = ['Pending','On Leave', 'Mutation', 'Active', 'Resign'];
    $banks = Banks::get();
    $gradings = Grading::get();
    $groups = Groups::get();
    $religion = ['Buddha', 'Catholic Christian', 'Christian', 'Confucian', 'Hindu', 'Islam'];
    $last_education = ['Elementary School', 'Junior High School', 'Senior High School', 'Diploma I', 'Diploma II', 'Diploma III', 'Diploma IV', 'Bachelor Degree', 'Masters degree', 'Vocational School', 'Lord'];

    return view('pages.Employee.show', compact(
        'employee',
        'employees',
        'status_employee',
        'child',
        'companys',
        'stores',
        'marriage',
        'gender',
        'gradings',
        'groups',
        'status',
        'banks',
        'religion',
        'structures',
        'last_education',
        'positions',
        'departments',
        'hashedId',
        'isManager' // ← Kirim ke Blade
    ));
}
    public function create()
    {
        $employees = Employee::where('status', 'Active')
            ->pluck('employee_name', 'id');
        $stores = Stores::pluck('name', 'id')->all();
        $positions = Position::pluck('name', 'id')->all();
        $departments = Departments::pluck('department_name', 'id')->all();
        $companys = Company::pluck('name', 'id')->all();
        $banks = Banks::pluck('name', 'id')->all();
        $status_employee = ['PKWT', 'DW', 'PKWTT', 'On Job Training'];
        $status_child = ['0', '1', '2', '3', '4', '5'];
        $status_marriage = ['Yes', 'No'];
        $status_gender = ['Male', 'Female', 'MD'];
        $status = ['Active', 'Pending', 'On Leave', 'Mutation', 'Resign'];

        $status_religion = ['Buddha', 'Catholic Christian', 'Christian', 'Confucian', 'Hindu', 'Islam'];
        $status_last_education = ['Elementary School', 'Junior High School', 'Senior High School', 'Diploma I', 'Diploma II', 'Diploma III', 'Diploma IV', 'Bachelor Degree', 'Masters degree', 'Vocational School', 'Lord'];
        return view('pages.Employee.create', compact('employees', 'companys', 'stores', 'banks', 'status_marriage', 'positions', 'departments', 'status_employee', 'status_child', 'status_gender', 'status_religion', 'status_last_education', 'status'));
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'password' => ['nullable', 'string', 'min:7', 'max:30', new NoXSSInput()],
            'username' => [
                'nullable',
                'string',
                'max:30',
                'min:12',
                'regex:/^[a-zA-Z0-9_-]+$/',
                'unique:users,username',
                new NoXSSInput()
            ],
             'photos' => [
            'nullable', 'mimes:jpg,jpeg,png,webp', 'max:512'

        ],
             'is_manager' => [
                'nullable',
                'boolean',
                new NoXSSInput()
            ],
            'join_date' => ['required', 'date_format:Y-m-d', new NoXSSInput()],
            'date_of_birth' => ['required', 'date_format:Y-m-d', new NoXSSInput()],
            'employee_name' => ['required', 'string', 'max:255', 'unique:employees_tables,employee_name', new NoXSSInput()],
            'bpjs_kes' => ['required', 'string', 'max:255', new NoXSSInput()],
            'bpjs_ket' => ['required', 'string', 'max:255', new NoXSSInput()],
            'email' => ['required', 'string', 'max:255', new NoXSSInput()],
            'emergency_contact_name' => ['required', 'string', 'max:255', new NoXSSInput()],
            'marriage' => ['required', 'string', 'max:255', new NoXSSInput()],
            'child' => ['required', 'string', 'max:255', new NoXSSInput()],
            'gender' => ['required', 'string', 'max:255', new NoXSSInput()],
            'telp_number' => ['required', 'numeric', 'digits_between:10,13', 'unique:employees_tables,telp_number', new NoXSSInput()],
            'status_employee' => ['required', 'string', 'max:255', new NoXSSInput()],
            'nik' => ['required', 'max:20', 'unique:employees_tables,nik', new NoXSSInput()],
            'bank_account_number' => ['required', 'max:20', new NoXSSInput()],
            'employee_pengenal' => ['nullable', 'string', 'max:30', 'unique:employees_tables,employee_id', new NoXSSInput()],
            'last_education' => ['required', 'string', 'max:255', new NoXSSInput()],
            'religion' => ['required', 'string', new NoXSSInput()],
            'place_of_birth' => ['required', 'string', 'max:255', new NoXSSInput()],
            'biological_mother_name' => ['required', 'string', 'max:255', new NoXSSInput()],
            'current_address' => ['required', 'string', 'max:255', new NoXSSInput()],
            'id_card_address' => ['required', 'string', 'max:255', new NoXSSInput()],
            'institution' => ['required', 'string', 'max:255', new NoXSSInput()],
            'npwp' => ['required', 'string', 'max:50', new NoXSSInput()],
            'position_id' => ['required', 'exists:position_tables,id', new NoXSSInput()],
            'store_id' => ['required', 'exists:stores_tables,id', new NoXSSInput()],
            'company_id' => ['required', 'exists:company_tables,id', new NoXSSInput()],
            'department_id' => ['required', 'exists:departments_tables,id', new NoXSSInput()],
            'banks_id' => ['required', 'exists:banks_tables,id', new NoXSSInput()],
            'structure_id' => ['nullable', 'exists:structures_tables,id', new NoXSSInput()],
        ], [
            'password.min' => 'The password must be at least 7 characters.',
            'password.max' => 'The password may not be greater than 30 characters.',
            'username.min' => 'The username must be at least 12 characters.',
            'username.max' => 'The username may not be greater than 30 characters.',
            'username.regex' => 'The username may only contain letters, numbers, underscores, and dashes.',
            'username.unique' => 'This username is already taken.',
            'join_date.required' => 'The join date is required.',
            'join_date.date_format' => 'The join date must be in the format YYYY-MM-DD.',
            'date_of_birth.required' => 'The date of birth is required.',
            'date_of_birth.date_format' => 'The date of birth must be in the format YYYY-MM-DD.',
            'employee_name.required' => 'The employee name is required.',
            'employee_name.max' => 'The employee name may not be greater than 255 characters.',
            'bpjs_kes.required' => 'The BPJS Kesehatan field is required.',
            'bpjs_kes.max' => 'The BPJS Kesehatan may not be greater than 255 characters.',
            'bpjs_ket.required' => 'The BPJS Ketenagakerjaan field is required.',
            'bpjs_ket.max' => 'The BPJS Ketenagakerjaan may not be greater than 255 characters.',
            'email.required' => 'The email is required.',
            'email.max' => 'The email may not be greater than 255 characters.',
            'emergency_contact_name.required' => 'The emergency contact name is required.',
            'marriage.required' => 'The marriage status is required.',
            'child.required' => 'The child information is required.',
            'gender.required' => 'The gender is required.',
            'telp_number.required' => 'The phone number is required.',
            'telp_number.numeric' => 'The phone number must be numeric.',
            'status_employee.required' => 'The employee status is required.',
            'nik.required' => 'The NIK is required.',
            'nik.max' => 'The NIK may not be greater than 20 characters.',
            'bank_account_number.required' => 'The bank account number is required.',
            'bank_account_number.max' => 'The bank account number may not be greater than 20 characters.',
            'employee_pengenal.max' => 'The employee ID may not be greater than 30 characters.',
            'employee_pengenal.unique' => 'This employee ID is already taken.',
            'last_education.required' => 'The last education field is required.',
            'last_education.max' => 'The last education may not be greater than 255 characters.',
            'religion.required' => 'The religion field is required.',
            'place_of_birth.required' => 'The place of birth is required.',
            'biological_mother_name.required' => 'The biological mother\'s name is required.',
            'current_address.required' => 'The current address is required.',
            'id_card_address.required' => 'The ID card address is required.',
            'institution.required' => 'The institution is required.',
            'npwp.required' => 'The NPWP is required.',
            'npwp.max' => 'The NPWP may not be greater than 50 characters.',
            'position_id.exists' => 'The selected position is invalid.',
            'store_id.exists' => 'The selected store is invalid.',
            'company_id.exists' => 'The selected company is invalid.',
            'department_id.exists' => 'The selected department is invalid.',
            'position_id.required' => 'The Position is required.',
            'store_id.required' => 'The Store is required.',
            'company_id.required' => 'The Company is required.',
            'department_id.required' => 'The Department is required.',
            'banks_id.exists' => 'The selected banks is invalid.',
            'banks_id.required' => 'The banks is required.',
            'foto' => [
                'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:512'
            ],
            'photos.mimes' => 'The photo must be a file of type: jpg, jpeg, png, webp.',
            'photos.max' => 'photos must under 512 kb.',
   
        ]);
         $filePath = null;

    if ($request->hasFile('photos')) {
        $file = $request->file('photos');

        if ($file->getSize() > 512 * 1024) { return back()->withErrors(['photos' => 'Photos must be under 512 KB']); }

        $fileName = hash('sha256', $file->getClientOriginalName() . time()) . '.' . $file->getClientOriginalExtension();
        $folderPath = 'employeesphotos/' . date('Y/m'); // rapi per tahun/bulan

        // Storage::putFileAs('public/' . $folderPath, $file, $fileName);
        Storage::disk('public')->putFileAs($folderPath, $file, $fileName);
        $filePath = $folderPath . '/' . $fileName;
    }
        try {
            DB::beginTransaction();
            $lastEmployee = Employee::orderBy('employee_pengenal', 'desc')->first();
            $currentYearMonth = date('Ym');
            if ($lastEmployee) {
                $lastId = $lastEmployee->employee_pengenal;
                $lastSequence = (int) substr($lastId, -5);
                $lastYearMonth = substr($lastId, 0, 6);
                if ($lastYearMonth === $currentYearMonth) {
                    $sequence = $lastSequence + 1;
                } else {
                    $sequence = $lastSequence + 1;
                }
            } else {
                $sequence = 1; 
            }
            $employeeId = $currentYearMonth . str_pad($sequence, 5, '0', STR_PAD_LEFT);
            $employees = Employee::create([
            'photos' => $filePath,

                'employee_pengenal' => $employeeId,
                'employee_name' => $validatedData['employee_name'] ?? '',
                'nik' => $validatedData['nik'] ?? '',
                'bank_account_number' => $validatedData['bank_account_number'] ?? '',
                'position_id' => $validatedData['position_id'] ?? '',
                'company_id' => $validatedData['company_id'] ?? '',
                'banks_id' => $validatedData['banks_id'] ?? '',
                'store_id' => $validatedData['store_id'] ?? '',
                'structure_id' => $validatedData['structure_id'] ?? null,
                'department_id' => $validatedData['department_id'] ?? '',
                'status_employee' => $validatedData['status_employee'] ?? '',
                'join_date' => $validatedData['join_date'] ?? '',
                'marriage' => $validatedData['marriage'] ?? '',
                'child' => $validatedData['child'] ?? '',
                'telp_number' => $validatedData['telp_number'] ?? '',
                'gender' => $validatedData['gender'] ?? '',
                'date_of_birth' => $validatedData['date_of_birth'] ?? '',
                'is_manager' => $validatedData['is_manager'] ?? 0,
                'bpjs_kes' => $validatedData['bpjs_kes'] ?? '',
                'bpjs_ket' => $validatedData['bpjs_ket'] ?? '',
                'email' => $validatedData['email'] ?? '',
                'emergency_contact_name' => $validatedData['emergency_contact_name'] ?? '',
                'status' => $validatedData['status'] ?? 'Pending',
                'religion' => $validatedData['religion'] ?? '',
                'last_education' => $validatedData['last_education'] ?? '',
                'place_of_birth' => $validatedData['place_of_birth'] ?? '',
                'biological_mother_name' => $validatedData['biological_mother_name'] ?? '',
                'current_address' => $validatedData['current_address'] ?? '',
                'id_card_address' => $validatedData['id_card_address'] ?? '',
                'institution' => $validatedData['institution'] ?? '',
                'npwp' => $validatedData['npwp'] ?? '',
            ]);
            if ($employees) {
                Mail::to($employees->email)->send(new WelcomeEmployeeMail($employees));
            }
            $user = User::create([
                'username' => $employeeId,
                'password' => Hash::make($employeeId),
                'employee_id' => $employees->id,
            ]);
            $user->assignRole('Human');
            DB::commit();
            return redirect()->route('pages.Employee')->with('success', 'Done!');
        } catch (\Exception $e) {
            DB::rollBack();
         
            
            if ($filePath && Storage::disk('public')->exists($filePath)) {
    Storage::disk('public')->delete($filePath);
}

        }
            return redirect()->back()
                ->withErrors(['error' => 'Error while creating data: ' . $e->getMessage()])
                ->withInput();
        }
    
    public function update(Request $request, $hashedId)
{
    $user = User::with('Employee')
        ->get()
        ->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

    if (!$user) {
        return redirect()->route('pages.Employee')->with('error', 'ID tidak valid.');
    }

    $validatedData = $request->validate([
        'photos' => ['nullable','mimes:jpg,jpeg,png,webp', 'max:512'],

        'join_date' => ['required', 'date_format:Y-m-d', new NoXSSInput()],
        'end_date' => ['nullable', 'date_format:Y-m-d', new NoXSSInput()],
        'date_of_birth' => ['required', 'date_format:Y-m-d', new NoXSSInput()],

        'employee_name' => [
            'required', 'string', 'max:255',
            Rule::unique('employees_tables', 'employee_name')->ignore($user->Employee->id),
            new NoXSSInput()
        ],

        'structure_id' => ['nullable', 'exists:structures_tables,id', new NoXSSInput()],
        'grading_id' => ['nullable', 'exists:grading,id', new NoXSSInput()],
        'group_id' => ['nullable', 'exists:groups_tables,id', new NoXSSInput()],
        'bpjs_kes' => ['required', 'string', 'max:255'],
        'bpjs_ket' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'max:255'],
        'emergency_contact_name' => ['required', 'string', 'max:255', new NoXSSInput()],
        'marriage' => ['required', 'string', 'max:255', new NoXSSInput()],
        'notes' => ['nullable', 'string', 'max:255', new NoXSSInput()],
        'child' => ['required', 'string', 'max:255', new NoXSSInput()],
        'gender' => ['required', 'string', 'max:255', new NoXSSInput()],
        'telp_number' => [
            'required', 'numeric', 'digits_between:10,13',
            Rule::unique('employees_tables', 'telp_number')->ignore($user->Employee->id),
            new NoXSSInput()
        ],
        'status_employee' => ['required', 'string', 'max:255', new NoXSSInput()],
        'nik' => [
            'required', 'max:20',
            Rule::unique('employees_tables', 'nik')->ignore($user->Employee->id),
            new NoXSSInput()
        ],
        'bank_account_number' => ['required', 'max:20', new NoXSSInput()],
        'last_education' => ['required', 'string', 'max:255', new NoXSSInput()],
        'religion' => ['required', 'string', new NoXSSInput()],
        'status' => ['required', 'string', new NoXSSInput()],
        'place_of_birth' => ['required', 'string', 'max:255', new NoXSSInput()],
        'biological_mother_name' => ['required', 'string', 'max:255', new NoXSSInput()],
        'current_address' => ['required', 'string', 'max:255', new NoXSSInput()],
        'id_card_address' => ['required', 'string', 'max:255', new NoXSSInput()],
        'institution' => ['required', 'string', 'max:255', new NoXSSInput()],
        'npwp' => ['required', 'string', 'max:50'],
        'is_manager' => ['nullable'],
        'pin' => [
            'required', 'string', 'max:50',
            Rule::unique('employees_tables', 'pin')->ignore($user->Employee->id),
            new NoXSSInput()
        ],
        'position_id' => ['nullable', 'exists:position_tables,id', new NoXSSInput()],
        'store_id' => ['nullable', 'exists:stores_tables,id', new NoXSSInput()],
        'company_id' => ['nullable', 'exists:company_tables,id', new NoXSSInput()],
        'department_id' => ['nullable', 'exists:departments_tables,id', new NoXSSInput()],
        'banks_id' => ['required', 'exists:banks_tables,id', new NoXSSInput()],
    ]);
    $filePath = $user->Employee->photos;
    try {
        DB::transaction(function () use ($user, &$validatedData, $request, &$filePath) {
            /** --------------------------
             *  Handle Upload Photo
             * -------------------------*/
            // if ($request->hasFile('photos')) {
            //     $file = $request->file('photos');
            //     $fileName = hash('sha256', $file->getClientOriginalName() . time()) . '.' .
            //         $file->getClientOriginalExtension();

            //     $folderPath = 'employeesphotos/' . date('Y/m');

            //     Storage::putFileAs('public/' . $folderPath, $file, $fileName);
            //     $newFilePath = $folderPath . '/' . $fileName;

            //     if ($filePath && Storage::exists('public/' . $filePath)) {
            //         Storage::delete('public/' . $filePath);
            //     }

            //     $filePath = $validatedData['photos'] = $newFilePath;
            // }
            if ($request->hasFile('photos')) {
    $file = $request->file('photos');
    $fileName = hash('sha256', $file->getClientOriginalName() . time()) . '.' .
        $file->getClientOriginalExtension();

    $folderPath = 'employeesphotos/' . date('Y/m');

    // simpan ke storage/app (PRIVATE)
    Storage::putFileAs($folderPath, $file, $fileName);

    $newFilePath = $folderPath . '/' . $fileName;

    // hapus file lama jika ada
    if ($filePath && Storage::exists($filePath)) {
        Storage::delete($filePath);
    }

    $filePath = $validatedData['photos'] = $newFilePath;
}


            /** --------------------------
             *  Lock employee row
             * -------------------------*/
            $employee = $user->Employee()->lockForUpdate()->first();
            $oldStructureId = $employee->structure_id;

            $statusEmployee = $validatedData['status'];
            $inactiveStatus = ['Resign','On Leave'];

            /** --------------------------
             *  Handle Status Non-Aktif
             * -------------------------*/
            if (in_array($statusEmployee, $inactiveStatus)) {
                $validatedData['structure_id'] = null;

                if ($oldStructureId) {
                    Structuresnew::where('id', $oldStructureId)
                        ->lockForUpdate()
                        ->update(['status' => 'vacant']);
                }
            }

            /** --------------------------
             *  Handle Structure Baru
             * -------------------------*/
            if (!empty($validatedData['structure_id'])) {
                $newStructure = Structuresnew::with('submissionposition')
                    ->where('id', $validatedData['structure_id'])
                    ->lockForUpdate()
                    ->first();

                if ($newStructure && $newStructure->submissionposition) {
                    $submission = $newStructure->submissionposition;

                    $validatedData['company_id'] = $submission->company_id;
                    $validatedData['department_id'] = $submission->department_id;
                    $validatedData['store_id'] = $submission->store_id;
                    $validatedData['position_id'] = $submission->position_id;
                    $validatedData['is_manager'] = $submission->is_manager;

                }

                $newStructure->update(['status' => 'active']);
            }

            /** --------------------------
             *  Update Employee
             * -------------------------*/
            $employee->update($validatedData);

            /** --------------------------
             *  Jika struktur dikosongkan
             * -------------------------*/
            if (empty($validatedData['structure_id']) && $oldStructureId) {
                Structuresnew::where('id', $oldStructureId)
                    ->lockForUpdate()
                    ->update(['status' => 'vacant']);
            }
        });

        return redirect()->route('pages.Employee')->with('success', 'Employee Updated Successfully.');

    } catch (\Throwable $th) {

        Log::error('Employee update failed', [
            'error' => $th->getMessage(),
            'employee_id' => $user->Employee->id ?? null,
        ]);

        return redirect()->route('pages.Employee')
            ->with('error', 'Update failed: ' . $th->getMessage());
    }
}
public function getPhoto($path)
{
    $full = storage_path('app/' . $path);

    if (!file_exists($full)) {
        abort(404);
    }

    return response()->file($full);
}


    // with locking
//     public function update(Request $request, $hashedId)
// {
//     // Ambil user secara efisien tanpa get semua record
//     $user = User::with('Employee')
//         ->get()
//         ->first(function ($u) use ($hashedId) {
//             $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
//             return $expectedHash === $hashedId;
//         });

//     if (!$user) {
//         return redirect()->route('pages.Employee')->with('error', 'ID tidak valid.');
//     }

//     $validatedData = $request->validate([
//           'photos' => [
//             'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:512'
//         ],
//         'join_date' => ['required', 'date_format:Y-m-d', new NoXSSInput()],
//         'end_date' => ['nullable', 'date_format:Y-m-d', new NoXSSInput()],
//         'date_of_birth' => ['required', 'date_format:Y-m-d', new NoXSSInput()],
//         'employee_name' => [
//             'required', 'string', 'max:255',
//             Rule::unique('employees_tables', 'employee_name')->ignore($user->Employee->id),
//             new NoXSSInput()
//         ],
//         'structure_id' => ['nullable', 'exists:structures_tables,id', new NoXSSInput()],
//         'bpjs_kes' => ['required', 'string', 'max:255'],
//         'bpjs_ket' => ['required', 'string', 'max:255'],
//         'email' => ['required', 'string', 'max:255'],
//         'emergency_contact_name' => ['required', 'string', 'max:255', new NoXSSInput()],
//         'marriage' => ['required', 'string', 'max:255', new NoXSSInput()],
//         'notes' => ['nullable', 'string', 'max:255', new NoXSSInput()],
//         'child' => ['required', 'string', 'max:255', new NoXSSInput()],
//         'gender' => ['required', 'string', 'max:255', new NoXSSInput()],
//         'telp_number' => [
//             'required', 'numeric', 'digits_between:10,13',
//             Rule::unique('employees_tables', 'telp_number')->ignore($user->Employee->id),
//             new NoXSSInput()
//         ],
//         'status_employee' => ['required', 'string', 'max:255', new NoXSSInput()],
//         'nik' => [
//             'required', 'max:20',
//             Rule::unique('employees_tables', 'nik')->ignore($user->Employee->id),
//             new NoXSSInput()
//         ],
//         'bank_account_number' => ['required', 'max:20', new NoXSSInput()],
//         'last_education' => ['required', 'string', 'max:255', new NoXSSInput()],
//         'religion' => ['required', 'string', new NoXSSInput()],
//         'status' => ['required', 'string', new NoXSSInput()],
//         'place_of_birth' => ['required', 'string', 'max:255', new NoXSSInput()],
//         'biological_mother_name' => ['required', 'string', 'max:255', new NoXSSInput()],
//         'current_address' => ['required', 'string', 'max:255', new NoXSSInput()],
//         'id_card_address' => ['required', 'string', 'max:255', new NoXSSInput()],
//         'institution' => ['required', 'string', 'max:255', new NoXSSInput()],
//         'npwp' => ['required', 'string', 'max:50'],
//         'is_manager' => ['nullable'],
//         'pin' => [
//             'required', 'string', 'max:50',
//             Rule::unique('employees_tables', 'pin')->ignore($user->Employee->id),
//             new NoXSSInput()
//         ],
//         'position_id' => ['nullable', 'exists:position_tables,id', new NoXSSInput()],
//         'store_id' => ['nullable', 'exists:stores_tables,id', new NoXSSInput()],
//         'company_id' => ['nullable', 'exists:company_tables,id', new NoXSSInput()],
//         'department_id' => ['nullable', 'exists:departments_tables,id', new NoXSSInput()],
//         'banks_id' => ['required', 'exists:banks_tables,id', new NoXSSInput()],
        
//     ]
// );
// $filePath = $user->employee->photos;

//     try {
//         DB::transaction(function () use ($user, $validatedData) {
// if ($request->hasFile('photos')) {
//             $file = $request->file('photos');
//             $fileName = hash('sha256', $file->getClientOriginalName() . time()) . '.' . $file->getClientOriginalExtension();
//             $folderPath = 'employeesphotos/' . date('Y/m');

//             // Simpan file baru
//             Storage::putFileAs('public/' . $folderPath, $file, $fileName);
//             $newFilePath = $folderPath . '/' . $fileName;

//             // Hapus file lama kalau ada
//             if ($filePath && Storage::exists('public/' . $filePath)) {
//                 Storage::delete('public/' . $filePath);
//             }

//             $filePath = $newFilePath;
//         }
//             // Ambil employee dengan kunci baris agar aman dari race
//             $employee = $user->Employee()->lockForUpdate()->first();
//             $oldStructureId = $employee->structure_id;

//             $statusEmployee = $validatedData['status'];
//             $statusChangeTriggers = ['Resign', 'Inactive', 'On Leave'];

//             // Jika status berubah ke non-aktif
//             if (in_array($statusEmployee, $statusChangeTriggers)) {
//                 $validatedData['structure_id'] = null;

//                 if ($oldStructureId) {
//                     $oldStructure = Structuresnew::where('id', $oldStructureId)->lockForUpdate()->first();
//                     if ($oldStructure) {
//                         $oldStructure->update(['status' => 'vacant']);
//                     }
//                 }
//             }

    

//             // Update structure baru jika ada
//             if (!empty($validatedData['structure_id'])) {
//                 $newStructure = Structuresnew::with('submissionposition')
//                     ->where('id', $validatedData['structure_id'])
//                     ->lockForUpdate()
//                     ->first();

//                 if ($newStructure) {
//                     if ($newStructure->submissionposition) {
//                         $submission = $newStructure->submissionposition;

//                         $validatedData['company_id'] = $submission->company_id;
//                         $validatedData['department_id'] = $submission->department_id;
//                         $validatedData['store_id'] = $submission->store_id;
//                         $validatedData['position_id'] = $submission->position_id;
//                         $validatedData['is_manager'] = $submission->is_manager;

//                         Log::info('Structure fields updated from submissionposition relation', [
//                             'structure_id' => $newStructure->id,
//                             'company_id' => $submission->company_id,
//                             'department_id' => $submission->department_id,
//                             'store_id' => $submission->store_id,
//                             'is_manager' => $submission->is_manager,
//                             'position_id' => $submission->position_id,
//                         ]);
//                     } else {
//                         Log::warning('No submissionposition found for structure', [
//                             'structure_id' => $newStructure->id,
//                         ]);
//                     }

//                     $newStructure->update(['status' => 'active']);
//                 }
//             }

//             // Update data employee
//             $employee->update($validatedData);

//             // Jika structure dikosongkan, pastikan status structure lama jadi 'vacant'
//             if (empty($validatedData['structure_id']) && $oldStructureId) {
//                 $oldStructure = Structuresnew::where('id', $oldStructureId)->lockForUpdate()->first();
//                 if ($oldStructure) {
//                     $oldStructure->update(['status' => 'vacant']);
//                 }
//             }

//             Log::info('Employee update successful', [
//                 'employee_id' => $employee->id,
//                 'final_structure_id' => $employee->structure_id,
//             ]);
//         });

//         return redirect()->route('pages.Employee')->with('success', 'Employee Updated Successfully.');

//     } catch (\Throwable $th) {
//         Log::error('Employee update failed', [
//             'error' => $th->getMessage(),
//             'employee_id' => $user->Employee->id ?? null,
//         ]);
//         return redirect()->route('pages.Employee')->with('error', 'Update failed: ' . $th->getMessage());
//     }
// }

// tanpa locking 
// public function update(Request $request, $hashedId)
// {
//     $user = User::with('Employee')->get()->first(function ($u) use ($hashedId) {
//         $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
//         return $expectedHash === $hashedId;
//     });

//     if (!$user) {
//         return redirect()->route('pages.Employee')->with('error', 'ID tidak valid.');
//     }

//     $validatedData = $request->validate([
//         'join_date' => ['required', 'date_format:Y-m-d', new NoXSSInput()],
//         'end_date' => ['nullable', 'date_format:Y-m-d', new NoXSSInput()],
//         'date_of_birth' => ['required', 'date_format:Y-m-d', new NoXSSInput()],
//         'employee_name' => [
//             'required', 'string', 'max:255',
//             Rule::unique('employees_tables', 'employee_name')->ignore($user->Employee->id),
//             new NoXSSInput()
//         ],
//         'structure_id' => ['nullable', 'exists:structures_tables,id', new NoXSSInput()],
//         'bpjs_kes' => ['required', 'string', 'max:255'],
//         'bpjs_ket' => ['required', 'string', 'max:255'],
//         'email' => ['required', 'string', 'max:255'],
//         'emergency_contact_name' => ['required', 'string', 'max:255', new NoXSSInput()],
//         'marriage' => ['required', 'string', 'max:255', new NoXSSInput()],
//         'notes' => ['nullable', 'string', 'max:255', new NoXSSInput()],
//         'child' => ['required', 'string', 'max:255', new NoXSSInput()],
//         'gender' => ['required', 'string', 'max:255', new NoXSSInput()],
//         'telp_number' => [
//             'required', 'numeric', 'digits_between:10,13',
//             Rule::unique('employees_tables', 'telp_number')->ignore($user->Employee->id),
//             new NoXSSInput()
//         ],
//         'status_employee' => ['required', 'string', 'max:255', new NoXSSInput()],
//         'nik' => [
//             'required', 'max:20',
//             Rule::unique('employees_tables', 'nik')->ignore($user->Employee->id),
//             new NoXSSInput()
//         ],
//         'bank_account_number' => ['required', 'max:20', new NoXSSInput()],
//         'last_education' => ['required', 'string', 'max:255', new NoXSSInput()],
//         'religion' => ['required', 'string', new NoXSSInput()],
//         'status' => ['required', 'string', new NoXSSInput()],
//         'place_of_birth' => ['required', 'string', 'max:255', new NoXSSInput()],
//         'biological_mother_name' => ['required', 'string', 'max:255', new NoXSSInput()],
//         'current_address' => ['required', 'string', 'max:255', new NoXSSInput()],
//         'id_card_address' => ['required', 'string', 'max:255', new NoXSSInput()],
//         'institution' => ['required', 'string', 'max:255', new NoXSSInput()],
//         'npwp' => ['required', 'string', 'max:50'],
//         'pin' => [
//             'required', 'string', 'max:50',
//             Rule::unique('employees_tables', 'pin')->ignore($user->Employee->id),
//             new NoXSSInput()
//         ],
//         'position_id' => ['nullable', 'exists:position_tables,id', new NoXSSInput()],
//         'store_id' => ['nullable', 'exists:stores_tables,id', new NoXSSInput()],
//         'company_id' => ['nullable', 'exists:company_tables,id', new NoXSSInput()],
//         'department_id' => ['nullable', 'exists:departments_tables,id', new NoXSSInput()],
//         'banks_id' => ['required', 'exists:banks_tables,id', new NoXSSInput()],
//     ]);

//     DB::beginTransaction();

//     try {
//         $employee = $user->Employee;
//         $oldStructureId = $employee->structure_id;
//         $statusEmployee = $validatedData['status'];
//         $statusChangeTriggers = ['Resign', 'Inactive', 'On Leave'];

//         if (in_array($statusEmployee, $statusChangeTriggers)) {
//             $validatedData['structure_id'] = null;

//             if (!empty($oldStructureId)) {
//                 $oldStructure = Structuresnew::find($oldStructureId);
//                 if ($oldStructure) {
//                     $oldStructure->update(['status' => 'vacant']);
//                 }
//             }
//         }

//         Log::info('Employee update initiated', [
//             'employee_id' => $employee->id,
//             'old_structure_id' => $oldStructureId,
//             'new_structure_id' => $validatedData['structure_id'] ?? null,
//             'performed_by' => auth()->user()->name ?? 'system',
//         ]);

//         // Ambil data dari relasi submissionposition
//         if (!empty($validatedData['structure_id'])) {
//             $newStructure = Structuresnew::with('submissionposition')->find($validatedData['structure_id']);
//             if ($newStructure) {
//                 if ($newStructure->submissionposition) {
//                     $submission = $newStructure->submissionposition;

//                     $validatedData['company_id'] = $submission->company_id;
//                     $validatedData['department_id'] = $submission->department_id;
//                     $validatedData['store_id'] = $submission->store_id;
//                     $validatedData['position_id'] = $submission->position_id;

//                     Log::info('Structure fields updated from submissionposition relation', [
//                         'structure_id' => $newStructure->id,
//                         'company_id' => $submission->company_id,
//                         'department_id' => $submission->department_id,
//                         'store_id' => $submission->store_id,
//                         'position_id' => $submission->position_id,
//                     ]);
//                 } else {
//                     Log::warning('No submissionposition found for structure', [
//                         'structure_id' => $newStructure->id,
//                     ]);
//                 }

//                 $newStructure->update(['status' => 'active']);
//             }
//         }

//         // Update data employee
//         $employee->update([
//             'employee_name' => $validatedData['employee_name'],
//             'nik' => $validatedData['nik'],
//             'bank_account_number' => $validatedData['bank_account_number'],
//             'position_id' => $validatedData['position_id'] ?? null,
//             'company_id' => $validatedData['company_id'] ?? null,
//             'store_id' => $validatedData['store_id'] ?? null,
//             'structure_id' => $validatedData['structure_id'] ?? null,
//             'department_id' => $validatedData['department_id'] ?? null,
//             'banks_id' => $validatedData['banks_id'],
//             'status_employee' => $validatedData['status_employee'],
//             'join_date' => $validatedData['join_date'],
//             'end_date' => $validatedData['end_date'] ?? null,
//             'marriage' => $validatedData['marriage'],
//             'child' => $validatedData['child'],
//             'telp_number' => $validatedData['telp_number'],
//             'gender' => $validatedData['gender'],
//             'date_of_birth' => $validatedData['date_of_birth'],
//             'bpjs_kes' => $validatedData['bpjs_kes'],
//             'bpjs_ket' => $validatedData['bpjs_ket'],
//             'email' => $validatedData['email'],
//             'emergency_contact_name' => $validatedData['emergency_contact_name'],
//             'notes' => $validatedData['notes'] ?? '',
//             'status' => $validatedData['status'],
//             'religion' => $validatedData['religion'],
//             'last_education' => $validatedData['last_education'],
//             'place_of_birth' => $validatedData['place_of_birth'],
//             'biological_mother_name' => $validatedData['biological_mother_name'],
//             'current_address' => $validatedData['current_address'],
//             'id_card_address' => $validatedData['id_card_address'],
//             'institution' => $validatedData['institution'],
//             'npwp' => $validatedData['npwp'],
//             'pin' => $validatedData['pin'],
//         ]);

//         if (empty($validatedData['structure_id']) && !empty($oldStructureId)) {
//             $oldStructure = Structuresnew::find($oldStructureId);
//             if ($oldStructure) {
//                 $oldStructure->update(['status' => 'vacant']);
//             }
//         }

//         DB::commit();

//         Log::info('Employee update successful', [
//             'employee_id' => $employee->id,
//             'final_structure_id' => $employee->structure_id,
//         ]);

//         return redirect()->route('pages.Employee')->with('success', 'Employee Updated Successfully.');
//     } catch (\Throwable $th) {
//         DB::rollBack();
//         Log::error('Employee update failed', [
//             'error' => $th->getMessage(),
//             'employee_id' => $user->Employee->id ?? null,
//         ]);
//         return redirect()->route('pages.Employee')->with('error', 'Update failed: ' . $th->getMessage());
//     }
// }


//     public function update(Request $request, $hashedId)
//     {
//         $user = User::with('Employee')->get()->first(function ($u) use ($hashedId) {
//             $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
//             return $expectedHash === $hashedId;
//         });
//         if (!$user) {
//             return redirect()->route('pages.Employee')->with('error', 'ID tidak valid.');
//         }
//         $validatedData = $request->validate([

//             'join_date' => ['required', 'date_format:Y-m-d', new NoXSSInput()],
//             'end_date' => ['nullable', 'date_format:Y-m-d', new NoXSSInput()],
//             'date_of_birth' => ['required', 'date_format:Y-m-d', new NoXSSInput()],

//             'employee_name' => [
//                 'required',
//                 'string',
//                 'max:255',
//                 Rule::unique('employees_tables', 'employee_name')->ignore($user->Employee->id),
//                 new NoXSSInput()
//             ],
//             'level_id' => [
//                 'nullable',
//                 'exists:employees_tables,id',
//                 new NoXSSInput()
//             ],
//             'structure_id' => [
//                 'nullable',
//                 'exists:structures_tables,id',
//                 new NoXSSInput()
//             ],
//             'is_manager' => [
//                 'nullable',
//                 'boolean',
//                 new NoXSSInput()
//             ],
//             'is_manager_store' => [
//                 'nullable',
//                 'boolean',
//                 new NoXSSInput()
//             ],
//             'bpjs_kes' => ['required', 'string', 'max:255'],
//             'bpjs_ket' => ['required', 'string', 'max:255'],
//             'email' => ['required', 'string', 'max:255',],
//             'emergency_contact_name' => ['required', 'string', 'max:255', new NoXSSInput()],
//             'marriage' => ['required', 'string', 'max:255', new NoXSSInput()],
//             'notes' => ['nullable', 'string', 'max:255', new NoXSSInput()],
//             'child' => ['required', 'string', 'max:255', new NoXSSInput()],
//             'gender' => ['required', 'string', 'max:255', new NoXSSInput()],
//             'telp_number' => ['required', 'numeric', 'digits_between:10,13', Rule::unique('employees_tables', 'telp_number')->ignore($user->Employee->id), new NoXSSInput()],
//             'status_employee' => ['required', 'string', 'max:255', new NoXSSInput()],
//             'nik' => ['required', 'max:20', Rule::unique('employees_tables', 'nik')->ignore($user->Employee->id), new NoXSSInput()],
//             'bank_account_number' => ['required', 'max:20', new NoXSSInput()],
//             'last_education' => ['required', 'string', 'max:255', new NoXSSInput()],
//             'religion' => ['required', 'string', new NoXSSInput()],
//             'status' => ['required', 'string', new NoXSSInput()],
//             'place_of_birth' => ['required', 'string', 'max:255', new NoXSSInput()],
//             'biological_mother_name' => ['required', 'string', 'max:255', new NoXSSInput()],
//             'current_address' => ['required', 'string', 'max:255', new NoXSSInput()],
//             'id_card_address' => ['required', 'string', 'max:255', new NoXSSInput()],
//             'institution' => ['required', 'string', 'max:255', new NoXSSInput()],
//             'npwp' => ['required', 'string', 'max:50'],
//             'pin' => ['required', 'string', 'max:50', Rule::unique('employees_tables', 'pin')->ignore($user->Employee->id), new NoXSSInput()],
//             'position_id' => ['required', 'exists:position_tables,id', new NoXSSInput()],
//             'store_id' => ['required', 'exists:stores_tables,id', new NoXSSInput()],
//             'company_id' => ['required', 'exists:company_tables,id', new NoXSSInput()],
//             'department_id' => ['required', 'exists:departments_tables,id', new NoXSSInput()],
//             'banks_id' => ['required', 'exists:banks_tables,id', new NoXSSInput()],
//         ], [
           
//             'join_date.required' => 'The join date is required.',
//             'join_date.date_format' => 'The join date must be in the format YYYY-MM-DD.',
//             'date_of_birth.required' => 'The date of birth is required.',
//             'date_of_birth.date_format' => 'The date of birth must be in the format YYYY-MM-DD.',
//             'employee_name.required' => 'The employee name is required.',
//             'employee_name.max' => 'The employee name may not be greater than 255 characters.',
//             'bpjs_kes.required' => 'The BPJS Kesehatan field is required.',
//             'bpjs_kes.max' => 'The BPJS Kesehatan may not be greater than 255 characters.',
//             'bpjs_ket.required' => 'The BPJS Ketenagakerjaan field is required.',
//             'bpjs_ket.max' => 'The BPJS Ketenagakerjaan may not be greater than 255 characters.',
//             'email.required' => 'The email is required.',
//             'email.max' => 'The email may not be greater than 255 characters.',
//             'emergency_contact_name.required' => 'The emergency contact name is required.',
//             'marriage.required' => 'The marriage status is required.',
//             'notes.max' => 'The notes may not be greater than 255 characters.',
//             'child.required' => 'The child information is required.',
//             'gender.required' => 'The gender is required.',
//             'telp_number.required' => 'The phone number is required.',
//             'telp_number.numeric' => 'The phone number must be numeric.',
//             'telp_number.max' => 'The phone number may not be greater than 13 digits.',
//             'status_employee.required' => 'The employee status is required.',
//             'nik.required' => 'The NIK is required.',
//             'nik.max' => 'The NIK may not be greater than 20 characters.',
//             'bank_account_number.required' => 'The bank account number is required.',
//             'bank_account_number.max' => 'The bank account number may not be greater than 20 characters.',
//             'last_education.required' => 'The last education field is required.',
//             'last_education.max' => 'The last education may not be greater than 255 characters.',
//             'religion.required' => 'The religion field is required.',
//             'place_of_birth.required' => 'The place of birth is required.',
//             'biological_mother_name.required' => 'The biological mother\'s name is required.',
//             'current_address.required' => 'The current address is required.',
//             'id_card_address.required' => 'The ID card address is required.',
//             'institution.required' => 'The institution is required.',
//             'npwp.required' => 'The NPWP is required.',
//             'npwp.max' => 'The NPWP may not be greater than 50 characters.',
//             'position_id.exists' => 'The selected position is invalid.',
//             'store_id.exists' => 'The selected store is invalid.',
//             'company_id.exists' => 'The selected company is invalid.',
//             'department_id.exists' => 'The selected department is invalid.',
//             'position_id.required' => 'The Position is required.',
//             'store_id.required' => 'The Store is required.',
//             'company_id.required' => 'The Company is required.',
//             'department_id.required' => 'The Department is required.',
//             'banks_id.exists' => 'The selected banks is invalid.',
//             'banks_id.required' => 'The banks is required.',
//         ]);
        
        
//            DB::beginTransaction();
//     try {
//         $employee = $user->Employee;
//         $oldStructureId = $employee->structure_id;
//         $statusEmployee = $validatedData['status'];
//         $statusChangeTriggers = ['Resign', 'Inactive', 'On Leave'];

//         if (in_array($statusEmployee, $statusChangeTriggers)) {
//             $validatedData['structure_id'] = null;

//             if (!empty($oldStructureId)) {
//                 $oldStructure = Structuresnew::find($oldStructureId);
//                 if ($oldStructure) {
//                     $oldStructure->update(['status' => 'vacant']);
//                 }
//             }
//         }
//         $user->Employee->update([
//             'employee_name' => $validatedData['employee_name'] ?? '',
//             'nik' => $validatedData['nik'] ?? '',
//             'bank_account_number' => $validatedData['bank_account_number'] ?? '',
//             'position_id' => $validatedData['position_id'] ?? '',
//             'company_id' => $validatedData['company_id'] ?? '',
//             'store_id' => $validatedData['store_id'] ?? '',
//             'structure_id' => $validatedData['structure_id'] ?? null,
//             'department_id' => $validatedData['department_id'] ?? '',
//             'banks_id' => $validatedData['banks_id'] ?? '',
//             'status_employee' => $validatedData['status_employee'] ?? '',
//             'join_date' => $validatedData['join_date'] ?? '',
//             'end_date' => $validatedData['end_date'] ?? null,
//             'marriage' => $validatedData['marriage'] ?? '',
//             'child' => $validatedData['child'] ?? '',
//             'telp_number' => $validatedData['telp_number'] ?? '',
//             'gender' => $validatedData['gender'] ?? '',
//             'date_of_birth' => $validatedData['date_of_birth'] ?? '',
//             'bpjs_kes' => $validatedData['bpjs_kes'] ?? '',
//             'bpjs_ket' => $validatedData['bpjs_ket'] ?? '',
//             'email' => $validatedData['email'] ?? '',
//             'emergency_contact_name' => $validatedData['emergency_contact_name'] ?? '',
//             'notes' => $validatedData['notes'] ?? '',
//             'status' => $validatedData['status'],
//             'religion' => $validatedData['religion'] ?? '',
//             'last_education' => $validatedData['last_education'] ?? '',
//             'place_of_birth' => $validatedData['place_of_birth'] ?? '',
//             'biological_mother_name' => $validatedData['biological_mother_name'] ?? '',
//             'current_address' => $validatedData['current_address'] ?? '',
//             'id_card_address' => $validatedData['id_card_address'] ?? '',
//             'institution' => $validatedData['institution'] ?? '',
//             'npwp' => $validatedData['npwp'] ?? '',
//             'pin' => $validatedData['pin'] ?? '',
//             'level_id' => $validatedData['level_id'],
//             'is_manager'  => $validatedData['is_manager'] ?? 0,
//             'is_manager_store'  => $validatedData['is_manager_store'] ?? 0,
//         ]);
       
//      if (empty($validatedData['structure_id']) && !empty($oldStructureId)) {
//         $oldStructure = Structuresnew::find($oldStructureId);
//         if ($oldStructure) {
//             $oldStructure->update(['status' => 'vacant']);
//         }
//     }
//      if (!empty($validatedData['structure_id'])) {
//             $newStructure = Structuresnew::find($validatedData['structure_id']);
//             if ($newStructure) {
//                 $newStructure->update(['status' => 'active']);
//             }
//         }
//        DB::commit();
//         return redirect()->route('pages.Employee')->with('success', 'Employee Updated Successfully.');
//     } catch (\Throwable $th) {
//         DB::rollBack();
//         return redirect()->route('pages.Employee')->with('error', 'Update failed: ' . $th->getMessage());
//     }
// }
            // 'foto' => ['nullable', 'image', 'max:512'],

     // 'foto.max' => 'under 512 kb.',
            // 'foto.image' => 'must be jpg jpeg or png .',
    // $filePath = $user->employee->foto;
        // if ($request->hasFile('foto')) {
        //     $file = $request->file('foto');
        //     $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
        //     $file->storeAs('public/employeefoto', $fileName);
        //     $filePath = $fileName;
        //     if ($user->employee && $user->employee->foto && Storage::exists('public/employeefoto/' . $user->employee->foto)) {
        //         Storage::delete('public/company/' . $user->employee->foto);
        //     }
        // }
        // if ($request->hasFile('foto')) {
        //     $companyData['foto'] = $filePath;
        // }
    public function transferAllToPayroll(Request $request)
    {
        try {
            $month_year = $request->input('month_year', date('Y-m-d')); 

            $month = date('m', strtotime($month_year));
            $year = date('Y', strtotime($month_year));

            $employeeIds = User::whereNotNull('employee_id')
                ->whereHas('employee', function ($query) {
                    $query->whereIn('status', ['Mutation', 'Active', 'On Leave']);
                })
                ->pluck('employee_id')
                ->toArray();

            $transferred = 0;
            $skipped = 0;

            foreach ($employeeIds as $employeeId) {
                $exists = Payrolls::where('employee_id', $employeeId)
                    ->whereMonth('month_year', $month)
                    ->whereYear('month_year', $year)
                    ->exists();

                if (!$exists) {
                    Payrolls::create([
                        'employee_id' => $employeeId,
                        'month_year' => $month_year,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $transferred++;
                } else {
                    $skipped++;
                }
            }
            $message = "Transfer completed: $transferred employee(s) transferred for period " . date('F Y', strtotime($month_year)) .
                ", $skipped employee(s) skipped (already exist for this month)";

            return response()->json([
                'success' => true,
                'message' => $message,
                'transferred' => $transferred,
                'skipped' => $skipped,
                'period' => date('F Y', strtotime($month_year))
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to transfer: ' . $e->getMessage()]);
        }
    }
}
