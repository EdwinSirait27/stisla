<?php
namespace App\Http\Controllers;
use App\Models\Banks;
use App\Models\Company;
use App\Models\Departments;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Payrolls;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\DataTables;
use App\Models\Stores;
use Illuminate\Support\Facades\Hash;
use App\Rules\NoXSSInput;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
class EmployeeController extends Controller
{
    public function indexall()
    {
        // $storeList = Stores::pluck('name')->all();
        $storeList = Stores::select('name')->distinct()->pluck('name');
        $statusList = Employee::select('status')->distinct()->pluck('status');
        return view('pages.Employeeall.Employeeall', compact('storeList', 'statusList'));
    }
    public function index()
    {
        $storeList = Stores::select('name')->distinct()->pluck('name');

        return view('pages.Employee.Employee',compact('storeList'));
    }
   
 public function getEmployees(Request $request, DataTables $dataTables)
{
    $isHeadHR = auth()->user()->hasRole('HeadHR');

    $employees = User::with([
        'Employee.company',
        'Employee.store',
        'Employee.position',
        'Employee.department',
    ])
    ->select(['id', 'employee_id'])
    ->get()
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
        ->addColumn('name_company', fn($e) => optional(optional($e->Employee)->company)->name ?? 'Empty')
        ->addColumn('name', fn($e) => optional(optional($e->Employee)->store)->name ?? 'Empty')
        ->addColumn('position_name', fn($e) => optional(optional($e->Employee)->position)->name ?? 'Empty')
        ->addColumn('department_name', fn($e) => optional(optional($e->Employee)->department)->department_name ?? 'Empty')
        ->addColumn('status_employee', fn($e) => optional($e->Employee)->status_employee ?? 'Empty')

        ->addColumn('employee_name', fn($e) => optional($e->Employee)->employee_name ?? 'Empty')
        ->addColumn('created_at', fn($e) => optional($e->Employee)->created_at ?? 'Empty')
        ->addColumn('length_of_service', fn($e) => optional($e->Employee)->length_of_service ?? 'Empty')
        ->addColumn('status', fn($e) => optional($e->Employee)->status ?? 'Empty')
        ->rawColumns(['position_name', 'status', 'department_name', 'created_at', 'employee_name', 'name', 'status_employee','action'])
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
            'Employee.bank'
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
            if (auth()->user()->hasRole('HeadHR')) {

                $employee->action = '
            <a href="' . route('Employee.edit', $employee->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user" title="Edit Employee: ' . e(optional($employee->Employee)->employee_name) . '">
                <i class="fas fa-user-edit text-secondary"></i>
            </a>';
            } else {
                $employee->action = ''; // Optional: kosongkan jika tidak punya akses
            }

            return $employee;
        });
        // Daftar kolom dari relasi Employee yang ingin ditampilkan
        $columns = [
            'name' => 'store.name',
            'name_company' => 'company.name',
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
                // Mendapatkan nilai dari relasi dengan dot notation
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
// public function getEmployeesall(Request $request, DataTables $dataTables)
// {
//         ini_set('memory_limit', '768M');

//         $statusFilter = $request->input('status');
       
 


//     $query = User::with([
//         'Employee',
//         'Employee.company',
//         'Employee.store',
//         'Employee.position',
//         'Employee.department',
//         'Employee.bank'
//     ])->select(['id', 'username', 'employee_id']);

//     if (!empty($storeFilter)) {
//         $query->whereHas('Employee.store', function ($q) use ($storeFilter) {
//             $q->where('name', $storeFilter);
//         });
//     }
//            $storeFilter = $request->input('name');
// if (!empty($storeFilter)) {
//     $query->whereHas('Employee.store', function ($q) use ($storeFilter) {
//         $q->whereRaw('LOWER(name) like ?', ['%' . strtolower($storeFilter) . '%']);
//     });
// }
//         //    $storeFilter = $request->input('name');
// if (!empty($companyFilter)) {
//     $query->whereHas('Employee.company', function ($q) use ($storeFilter) {
//         $q->whereRaw('LOWER(company_name) like ?', ['%' . strtolower($storeFilter) . '%']);
//     });
// }

//     if (!empty($statusFilter)) {
//         $query->whereHas('Employee', function ($q) use ($statusFilter) {
//             $q->whereIn('status', $statusFilter);
//         });
//     }
// //     if ($storeFilter = $request->input('name')) {
// //     $query->whereHas('employee.store', function ($q) use ($storeFilter) {
// //         $q->whereRaw('LOWER(name) like ?', ['%' . strtolower($storeFilter) . '%']);
// //     });
// // }


//     $columns = [
//         'name' => 'store.name',
//         'name_company' => 'company.name',
//         'position_name' => 'position.name',
//         'employee_pengenal',
//         'department_name' => 'department.department_name',
//         'employee_name',
//         'id' => 'id',
//         'status_employee',
//         'join_date',
//         'marriage',
//         'child',
//         'telp_number',
//         'nik',
//         'gender',
//         'date_of_birth',
//         'place_of_birth',
//         'biological_mother_name',
//         'religion',
//         'current_address',
//         'id_card_address',
//         'last_education',
//         'institution',
//         'npwp',
//         'bpjs_kes',
//         'bpjs_ket',
//         'email',
//         'emergency_contact_name',
//         'notes',
//         'created_at',
//         'bank_name' => 'bank.name',
//         'bank_account_number',
//         'status',
//         'pin'
//     ];

//     $dataTable = $dataTables->eloquent($query);

//     foreach ($columns as $key => $relationPath) {
//         $column = is_string($key) ? $key : $relationPath;

//         $dataTable->addColumn($column, function ($user) use ($relationPath) {
//             return data_get($user->Employee, $relationPath) ?: 'Empty';
//         });
//     }

//     $dataTable->addColumn('action', function ($user) {
//         $employee = $user->Employee;
//         $idHashed = substr(hash('sha256', $user->id . env('APP_KEY')), 0, 8);
//         if (auth()->user()->hasRole('HeadHR')) {
//             return '
//                 <a href="' . route('Employee.edit', $idHashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user" title="Edit Employee: ' . e(optional($employee)->employee_name) . '">
//                     <i class="fas fa-user-edit text-secondary"></i>
//                 </a>';
//         }
//         return ''; 
//     });
//     $dataTable->addColumn('name', function ($user) {
//     return optional($user->Employee->store)->name ?? 'Empty';
// });
//    $dataTable->filterColumn('name', function ($query, $keyword) {
//     $query->whereHas('Employee.store', function ($q) use ($keyword) {
//         $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($keyword) . '%']);
//     });
// });


//     return $dataTable
//         ->rawColumns(['action'])
//         ->make(true);
// }



    public function edit($hashedId)
    {
        $employee = User::with('Employee', 'Employee.store', 'Employee.department', 'Employee.position', 'Employee.bank')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$employee) {
            abort(404, 'Employee not found.');
        }


        $positions = Position::get();
        $companys = Company::get();
        $departments = Departments::with('user.Employee')->get();
        $stores = Stores::with('user.Employee')->get();
        $status_employee = ['PKWT', 'DW', 'PKWTT', 'On Job Training'];
        $child = ['0', '1', '2', '3', '4', '5'];
        $marriage = ['Yes', 'No'];
        $gender = ['Male', 'Female', 'MD'];
        $status = ['Pending', 'Inactive', 'On Leave', 'Mutation','Active'];
        $banks = Banks::get();
        $religion = ['Buddha', 'Catholic Christian', 'Christian', 'Confusian', 'Hindu', 'Islam'];
        $last_education = ['Elementary School', 'Junior High School', 'Senior High School', 'Diploma I', 'Diploma II', 'Diploma III', 'Diploma IV', 'Bachelor Degree', 'Masters degree', 'Vocational School', 'Lord'];
        // dd($employee->Employee->join_date, $employee->Employee->getOriginal('join_date'));

        return view('pages.Employee.edit', [
            'employee' => $employee,
            'status_employee' => $status_employee,
            'child' => $child,
            'companys' => $companys,
            'stores' => $stores,
            'marriage' => $marriage,
            'gender' => $gender,
            'status' => $status,
            'banks' => $banks,
            'religion' => $religion,
            'last_education' => $last_education,
            'positions' => $positions,
            'departments' => $departments,
            'hashedId' => $hashedId,
        ]);
    }
    public function show($hashedId)
    {
        // Tambahkan debug sebelum mengirim ke view
        $employee = User::with('Employee', 'Employee.store', 'Employee.department', 'Employee.position')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$employee) {
            abort(404, 'Employee not found.');
        }


        $positions = Position::get();
        $companys = Company::get();
        $departments = Departments::with('user.Employee')->get();
        $stores = Stores::with('user.Employee')->get();
        $status_employee = ['PKWT', 'DW', 'PKWTT'];
        $child = ['0', '1', '2', '3', '4', '5'];
        $banks = ['OCBC', 'BCA', 'Victoria', 'Mandiri', 'BRI'];

        $marriage = ['Yes', 'No'];
        $gender = ['Male', 'Female', 'MD'];
        $status = ['Active', 'Pending', 'Inactive', 'On Leave'];
        $religion = ['Buddha', 'Catholic Christian', 'Christian', 'Confusian', 'Hindu', 'Islam'];
        $last_education = ['Elementary School', 'Junior High School', 'Senior High School', 'Diploma', 'Bachelor Degree'];
        // dd($employee->Employee->join_date, $employee->Employee->getOriginal('join_date'));

        return view('pages.Employee.show', [
            'employee' => $employee,
            'status_employee' => $status_employee,
            'child' => $child,
            'banks' => $banks,
            'companys' => $companys,
            'stores' => $stores,
            'marriage' => $marriage,
            'gender' => $gender,
            'status' => $status,
            'religion' => $religion,
            'last_education' => $last_education,
            'positions' => $positions,
            'departments' => $departments,
            'hashedId' => $hashedId,
        ]);
    }


    public function create()
    {
        $stores = Stores::pluck('name', 'id')->all();
        $positions = Position::pluck('name', 'id')->all();
        $departments = Departments::pluck('department_name', 'id')->all();
        $companys = Company::pluck('name', 'id')->all();
        $banks = Banks::pluck('name', 'id')->all();
        $status_employee = ['PKWT', 'DW', 'PKWTT', 'On Job Training'];
        $status_child = ['0', '1', '2', '3', '4', '5'];
        $status_marriage = ['Yes', 'No'];
        $status_gender = ['Male', 'Female', 'MD'];
        $status = ['Active', 'Pending', 'Inactive', 'On Leave', 'Mutation'];

        $status_religion = ['Buddha', 'Catholic Christian', 'Christian', 'Confusian', 'Hindu', 'Islam'];
        $status_last_education = ['Elementary School', 'Junior High School', 'Senior High School', 'Diploma I', 'Diploma II', 'Diploma III', 'Diploma IV', 'Bachelor Degree', 'Masters degree', 'Vocational School', 'Lord'];
        return view('pages.Employee.create', compact('companys', 'stores', 'banks', 'status_marriage', 'positions', 'departments', 'status_employee', 'status_child', 'status_gender', 'status_religion', 'status_last_education', 'status'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
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
            'join_date' => ['required', 'date_format:Y-m-d', new NoXSSInput()],
            'date_of_birth' => ['required', 'date_format:Y-m-d', new NoXSSInput()],
            'employee_name' => ['required', 'string', 'max:255', 'unique:employees_tables,employee_name', new NoXSSInput()],
            'bpjs_kes' => ['required', 'string', 'max:255', new NoXSSInput()],
            'bpjs_ket' => ['required', 'string', 'max:255', new NoXSSInput()],
            'email' => ['required', 'string', 'max:255', new NoXSSInput()],
            'emergency_contact_name' => ['required', 'string', 'max:255', new NoXSSInput()],
            'marriage' => ['required', 'string', 'max:255', new NoXSSInput()],
            'notes' => ['nullable', 'string', 'max:255', new NoXSSInput()],
            'child' => ['required', 'string', 'max:255', new NoXSSInput()],
            'gender' => ['required', 'string', 'max:255', new NoXSSInput()],
            'telp_number' => ['required', 'numeric', 'digits_between:10,13', 'unique:employees_tables,telp_number', new NoXSSInput()],
            'status_employee' => ['required', 'string', 'max:255', new NoXSSInput()],
            'nik' => ['required', 'max:20', 'unique:employees_tables,nik', new NoXSSInput()],
            'bank_account_number' => ['required', 'max:20', new NoXSSInput()],
            'employee_pengenal' => ['nullable', 'string', 'max:30', 'unique:employees_tables,employee_id', new NoXSSInput()],
            'last_education' => ['required', 'string', 'max:255', new NoXSSInput()],
            'religion' => ['required', 'string', new NoXSSInput()],
            // 'daily_allowance' => ['nullable','numeric',
//                 new NoXSSInput()],
            'place_of_birth' => ['required', 'string', 'max:255', new NoXSSInput()],
            'biological_mother_name' => ['required', 'string', 'max:255', new NoXSSInput()],
            'current_address' => ['required', 'string', 'max:255', new NoXSSInput()],
            'id_card_address' => ['required', 'string', 'max:255', new NoXSSInput()],
            'institution' => ['required', 'string', 'max:255', new NoXSSInput()],
            'npwp' => ['required', 'string', 'max:50', new NoXSSInput()],
            // 'pin' => ['nullable', 'string', 'max:4', new NoXSSInput()],
            'position_id' => ['required', 'exists:position_tables,id', new NoXSSInput()],
            'store_id' => ['required', 'exists:stores_tables,id', new NoXSSInput()],
            'company_id' => ['required', 'exists:company_tables,id', new NoXSSInput()],
            'department_id' => ['required', 'exists:departments_tables,id', new NoXSSInput()],
            'banks_id' => ['required', 'exists:banks_tables,id', new NoXSSInput()],

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
            // 'daily_allowance.numeric' => 'Net salary must be a number.',
            'bpjs_ket.required' => 'The BPJS Ketenagakerjaan field is required.',
            'bpjs_ket.max' => 'The BPJS Ketenagakerjaan may not be greater than 255 characters.',

            'email.required' => 'The email is required.',
            'email.max' => 'The email may not be greater than 255 characters.',

            'emergency_contact_name.required' => 'The emergency contact name is required.',
            'marriage.required' => 'The marriage status is required.',
            'notes.max' => 'The notes may not be greater than 255 characters.',
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


        ]);
        try {
            DB::beginTransaction();
            $lastEmployee = Employee::orderBy('employee_pengenal', 'desc')->first();

            $currentYearMonth = date('Ym'); // Format: TahunBulan (contoh: 202504)

            if ($lastEmployee) {
                $lastId = $lastEmployee->employee_pengenal;

                // Ambil 5 digit terakhir
                $lastSequence = (int) substr($lastId, -5);

                // Ambil bagian tahun-bulan dari ID terakhir
                $lastYearMonth = substr($lastId, 0, 6);

                if ($lastYearMonth === $currentYearMonth) {
                    // Jika tahun-bulan sama, increment sequence
                    $sequence = $lastSequence + 1;
                } else {
                    // Jika tahun-bulan berbeda, tetap increment sequence
                    $sequence = $lastSequence + 1;
                }
            } else {
                $sequence = 1; // Jika tidak ada data, mulai dari 1
            }

            // Format employee_id: TahunBulan + 5 digit sequence dengan leading zero
            $employeeId = $currentYearMonth . str_pad($sequence, 5, '0', STR_PAD_LEFT);
            $employees = Employee::create([
                // 'employee_name' => $request->employee_name,
                'employee_pengenal' => $employeeId,
                'employee_name' => $validatedData['employee_name'] ?? '',
                'nik' => $validatedData['nik'] ?? '',
                'bank_account_number' => $validatedData['bank_account_number'] ?? '',
                'position_id' => $validatedData['position_id'] ?? '',
                'company_id' => $validatedData['company_id'] ?? '',
                'banks_id' => $validatedData['banks_id'] ?? '',
                'store_id' => $validatedData['store_id'] ?? '',
                'department_id' => $validatedData['department_id'] ?? '',
                'status_employee' => $validatedData['status_employee'] ?? '',
                'join_date' => $validatedData['join_date'] ?? '',
                'marriage' => $validatedData['marriage'] ?? '',
                'child' => $validatedData['child'] ?? '',
                'telp_number' => $validatedData['telp_number'] ?? '',
                'gender' => $validatedData['gender'] ?? '',
                'date_of_birth' => $validatedData['date_of_birth'] ?? '',
                // 'daily_allowance' => Crypt::encrypt($validatedData['daily_allowance']),


                'bpjs_kes' => $validatedData['bpjs_kes'] ?? '',
                'bpjs_ket' => $validatedData['bpjs_ket'] ?? '',
                'email' => $validatedData['email'] ?? '',
                'emergency_contact_name' => $validatedData['emergency_contact_name'] ?? '',

                'notes' => $validatedData['notes'] ?? '',
                'status' => $validatedData['status'] ?? 'Pending',
                'religion' => $validatedData['religion'] ?? '',
                'last_education' => $validatedData['last_education'] ?? '',
                // disini masi error
                'place_of_birth' => $validatedData['place_of_birth'] ?? '',
                'biological_mother_name' => $validatedData['biological_mother_name'] ?? '',
                'current_address' => $validatedData['current_address'] ?? '',
                'id_card_address' => $validatedData['id_card_address'] ?? '',
                'institution' => $validatedData['institution'] ?? '',
                'npwp' => $validatedData['npwp'] ?? '',
                // 'pin' => $validatedData['pin'] ?? '',
            ]);
            // dd($employees->toArray());
            $user = User::create([
                'username' => $employeeId,
                'password' => Hash::make($employeeId),
                'employee_id' => $employees->id,
            ]);
            // dd($user->toArray());
            DB::commit();
            return redirect()->route('pages.Employee')->with('success', 'Done!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Error while creating data: ' . $e->getMessage()])
                ->withInput();
        }
    }
    public function update(Request $request, $hashedId)
    {
        $user = User::with('Employee')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$user) {
            return redirect()->route('pages.Employee')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([

            'join_date' => ['required', 'date_format:Y-m-d', new NoXSSInput()],
            'date_of_birth' => ['required', 'date_format:Y-m-d', new NoXSSInput()],
            'employee_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('employees_tables', 'employee_name')->ignore($user->Employee->id),
                new NoXSSInput()
            ],
            'bpjs_kes' => ['required', 'string', 'max:255'],
            // 'bpjs_kes' => ['required', 'string', 'max:255',Rule::unique('employees_tables', 'bpjs_kes')->ignore($user->Employee->id), new NoXSSInput()],
            'bpjs_ket' => ['required', 'string', 'max:255'],
            // 'bpjs_ket' => ['required', 'string', 'max:255',Rule::unique('employees_tables', 'bpjs_ket')->ignore($user->Employee->id), new NoXSSInput()],
            'email' => ['required', 'string', 'max:255',],
            // Rule::unique('employees_tables', 'email')->ignore($user->Employee->id), new NoXSSInput()],
            'emergency_contact_name' => ['required', 'string', 'max:255', new NoXSSInput()],
            'marriage' => ['required', 'string', 'max:255', new NoXSSInput()],
            'notes' => ['nullable', 'string', 'max:255', new NoXSSInput()],
            'child' => ['required', 'string', 'max:255', new NoXSSInput()],
            'gender' => ['required', 'string', 'max:255', new NoXSSInput()],
            'telp_number' => ['required', 'numeric', 'digits_between:10,13', Rule::unique('employees_tables', 'telp_number')->ignore($user->Employee->id), new NoXSSInput()],
            'status_employee' => ['required', 'string', 'max:255', new NoXSSInput()],
            'nik' => ['required', 'max:20', Rule::unique('employees_tables', 'nik')->ignore($user->Employee->id), new NoXSSInput()],
            'bank_account_number' => ['required', 'max:20', new NoXSSInput()],
            'last_education' => ['required', 'string', 'max:255', new NoXSSInput()],
            'religion' => ['required', 'string', new NoXSSInput()],
            'status' => ['required', 'string', new NoXSSInput()],
            // 'daily_allowance' => ['nullable','string',
            // new NoXSSInput()],
            'place_of_birth' => ['required', 'string', 'max:255', new NoXSSInput()],
            'biological_mother_name' => ['required', 'string', 'max:255', new NoXSSInput()],
            'current_address' => ['required', 'string', 'max:255', new NoXSSInput()],
            'id_card_address' => ['required', 'string', 'max:255', new NoXSSInput()],
            'institution' => ['required', 'string', 'max:255', new NoXSSInput()],
            'npwp' => ['required', 'string', 'max:50'],
            // 'pin' => ['required', 'string', 'max:50'],
            'position_id' => ['required', 'exists:position_tables,id', new NoXSSInput()],
            'store_id' => ['required', 'exists:stores_tables,id', new NoXSSInput()],
            'company_id' => ['required', 'exists:company_tables,id', new NoXSSInput()],
            'department_id' => ['required', 'exists:departments_tables,id', new NoXSSInput()],
            'banks_id' => ['required', 'exists:banks_tables,id', new NoXSSInput()],
        ], [
            'join_date.required' => 'The join date is required.',
            'join_date.date_format' => 'The join date must be in the format YYYY-MM-DD.',
            // 'daily_allowance.numeric' => 'Net salary must be a number.',

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
            'notes.max' => 'The notes may not be greater than 255 characters.',
            'child.required' => 'The child information is required.',
            'gender.required' => 'The gender is required.',

            'telp_number.required' => 'The phone number is required.',
            'telp_number.numeric' => 'The phone number must be numeric.',
            'telp_number.max' => 'The phone number may not be greater than 13 digits.',

            'status_employee.required' => 'The employee status is required.',
            'nik.required' => 'The NIK is required.',
            'nik.max' => 'The NIK may not be greater than 20 characters.',
            'bank_account_number.required' => 'The bank account number is required.',
            'bank_account_number.max' => 'The bank account number may not be greater than 20 characters.',


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
        ]);

        DB::beginTransaction();
        $user->Employee->update([
            'employee_name' => $validatedData['employee_name'] ?? '',
            'nik' => $validatedData['nik'] ?? '',
            'bank_account_number' => $validatedData['bank_account_number'] ?? '',
            'position_id' => $validatedData['position_id'] ?? '',
            'company_id' => $validatedData['company_id'] ?? '',
            'store_id' => $validatedData['store_id'] ?? '',
            'department_id' => $validatedData['department_id'] ?? '',
            'banks_id' => $validatedData['banks_id'] ?? '',
            'status_employee' => $validatedData['status_employee'] ?? '',
            // 'daily_allowance' => Crypt::encrypt($validatedData['daily_allowance'])?? 0,

            'join_date' => $validatedData['join_date'] ?? '',
            'marriage' => $validatedData['marriage'] ?? '',
            'child' => $validatedData['child'] ?? '',
            'telp_number' => $validatedData['telp_number'] ?? '',
            'gender' => $validatedData['gender'] ?? '',
            'date_of_birth' => $validatedData['date_of_birth'] ?? '',

            'bpjs_kes' => $validatedData['bpjs_kes'] ?? '',
            'bpjs_ket' => $validatedData['bpjs_ket'] ?? '',
            'email' => $validatedData['email'] ?? '',
            'emergency_contact_name' => $validatedData['emergency_contact_name'] ?? '',

            'notes' => $validatedData['notes'] ?? '',
            'status' => $validatedData['status'],
            'religion' => $validatedData['religion'] ?? '',
            'last_education' => $validatedData['last_education'] ?? '',
            // disini masi error
            'place_of_birth' => $validatedData['place_of_birth'] ?? '',
            'biological_mother_name' => $validatedData['biological_mother_name'] ?? '',
            'current_address' => $validatedData['current_address'] ?? '',
            'id_card_address' => $validatedData['id_card_address'] ?? '',
            'institution' => $validatedData['institution'] ?? '',
            'npwp' => $validatedData['npwp'] ?? '',
            // 'pin' => $validatedData['pin'] ?? '',
        ]);
        DB::commit();
        return redirect()->route('pages.Employee')->with('success', 'Employee Berhasil Diupdate.');
    }

    // public function transferAllToPayroll(Request $request)
    // {
    //     try {
    //         // Gunakan format Y-m-d yang sesuai dengan definisi casts di model
    //         $month_year = $request->input('month_year', date('Y-m-d')); // Format: YYYY-MM-DD

    //         // Ekstrak bulan dan tahun dari tanggal yang dipilih
    //         $month = date('m', strtotime($month_year));
    //         $year = date('Y', strtotime($month_year));

    //         // Ambil semua employee_id dari model User
    //         $employeeIds = User::whereNotNull('employee_id')
    //             ->pluck('employee_id')
    //             ->toArray();

    //         $transferred = 0;
    //         $skipped = 0;

    //         foreach ($employeeIds as $employeeId) {
    //             // Check jika employee_id sudah ada di Payrolls untuk bulan dan tahun yang sama
    //             // menggunakan whereMonth dan whereYear untuk membandingkan HANYA bulan dan tahun
    //             $exists = Payrolls::where('employee_id', $employeeId)
    //                 ->whereMonth('month_year', $month)
    //                 ->whereYear('month_year', $year)
    //                 ->exists();

    //             if (!$exists) {
    //                 // Buat record baru di Payrolls hanya jika employee_id belum ada untuk bulan dan tahun ini
    //                 Payrolls::create([
    //                     'employee_id' => $employeeId,
    //                     'month_year' => $month_year, // Format Y-m-d
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ]);
    //                 $transferred++;
    //             } else {
    //                 $skipped++;
    //             }
    //         }

    //         // Format tampilan bulan tahun yang benar untuk pesan
    //         $message = "Transfer completed: $transferred employee(s) transferred for period " . date('F Y', strtotime($month_year)) .
    //             ", $skipped employee(s) skipped (already exist for this month)";

    //         return response()->json([
    //             'success' => true,
    //             'message' => $message,
    //             'transferred' => $transferred,
    //             'skipped' => $skipped,
    //             'period' => date('F Y', strtotime($month_year))
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json(['success' => false, 'message' => 'Failed to transfer: ' . $e->getMessage()]);
    //     }
    // }
    public function transferAllToPayroll(Request $request)
    {
        try {
            // Gunakan format Y-m-d yang sesuai dengan definisi casts di model
            $month_year = $request->input('month_year', date('Y-m-d')); // Format: YYYY-MM-DD

            // Ekstrak bulan dan tahun dari tanggal yang dipilih
            $month = date('m', strtotime($month_year));
            $year = date('Y', strtotime($month_year));

            // Ambil semua employee_id dengan status tertentu dari model User
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
