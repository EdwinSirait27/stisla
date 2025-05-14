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
        return view('pages.Employeeall.Employeeall', compact('storeList','statusList'));

    }
    public function index()
    {
        return view('pages.Employee.Employee');
    }
    public function getEmployees()
    {
        $employees = User::with('Employee')
            ->select(['id', 'employee_id'])
            ->get()
            ->map(function ($employee) {
                $employee->id_hashed = substr(hash('sha256', $employee->id . env('APP_KEY')), 0, 8);
                $employee->action = '
                    <a href="' . route('Employee.edit', $employee->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user" title="Edit Employee: ' . e($employee->Employee->employee_name) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
                return $employee;
            });
        return DataTables::of($employees)
            ->addColumn('name_company', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->company->name)
                    ? $employee->Employee->company->name
                    : 'Empty';
            })
            ->addColumn('name_store', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->store->name)
                    ? $employee->Employee->store->name
                    : 'Empty';
            })

            ->addColumn('position_name', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->position->name)
                    ? $employee->Employee->position->name
                    : 'Empty';
            })
            ->addColumn('department_name', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->department->department_name)
                    ? $employee->Employee->department->department_name
                    : 'Empty';
            })
            ->addColumn('employee_name', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->employee_name)
                    ? $employee->Employee->employee_name
                    : 'Empty';
            })

            ->addColumn('created_at', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->created_at)
                    ? $employee->Employee->created_at
                    : 'Empty';
            })
            ->addColumn('length_of_service', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->length_of_service)
                    ? $employee->Employee->length_of_service
                    : 'Empty';
            })
            ->addColumn('status', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->status)
                    ? $employee->Employee->status
                    : 'Empty';
            })
            ->rawColumns(['employ', 'position_name', 'status', 'department_name', 'created_at', 'employee_name', 'name_store', 'action'])
            ->make(true);
    }

    public function getEmployeesall()
    {
        $storeFilter = request()->get('name'); // Ambil nilai filter status dari request
        $statusFilter = request()->get('status'); // Ambil nilai filter status dari request
    
        $query = User::with('Employee','Employee.company','Employee.store')
        ->select(['id', 'username', 'employee_id']);
    
        // Terapkan filter status jika ada
        if (!empty($storeFilter)) {
            $query->whereHas('Employee.store', function ($q) use ($storeFilter) {
                $q->where('name', $storeFilter);
            });
        }
        if (!empty($statusFilter)) {
            $query->whereHas('Employee', function ($q) use ($statusFilter) {
                $q->whereIn('status', $statusFilter); // gunakan whereIn untuk array
            });
        }
        
        
       
        $employees = $query->get()->map(function ($employee) {
            $employee->id_hashed = substr(hash('sha256', $employee->id . env('APP_KEY')), 0, 8);
            return $employee;
        });
    
        return DataTables::of($employees)
                  ->addColumn('name', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->store->name)
                    ? $employee->Employee->store->name
                    : 'Empty';
            })
            ->addColumn('name_company', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->company->name)
                    ? $employee->Employee->company->name
                    : 'Empty';
            })
            ->addColumn('position_name', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->position->name)
                    ? $employee->Employee->position->name
                    : 'Empty';
            })
            ->addColumn('employee_pengenal', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->employee_pengenal)
                    ? $employee->Employee->employee_pengenal
                    : 'Empty';
            })
            ->addColumn('department_name', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->department->department_name)
                    ? $employee->Employee->department->department_name
                    : 'Empty';
            })
            ->addColumn('employee_name', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->employee_name)
                    ? $employee->Employee->employee_name
                    : 'Empty';
            })
            ->addColumn('id', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->id)
                    ? $employee->Employee->id
                    : 'Empty';
            })
            ->addColumn('status_employee', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->status_employee)
                    ? $employee->Employee->status_employee
                    : 'Empty';
            })
            ->addColumn('join_date', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->join_date)
                    ? $employee->Employee->join_date
                    : 'Empty';
            })
            ->addColumn('marriage', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->marriage)
                    ? $employee->Employee->marriage
                    : 'Empty';
            })
            ->addColumn('child', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->child)
                    ? $employee->Employee->child
                    : 'Empty';
            })
            ->addColumn('telp_number', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->telp_number)
                    ? $employee->Employee->telp_number
                    : 'Empty';
            })
            ->addColumn('nik', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->nik)
                    ? $employee->Employee->nik
                    : 'Empty';
            })
            ->addColumn('gender', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->gender)
                    ? $employee->Employee->gender
                    : 'Empty';
            })
            ->addColumn('date_of_birth', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->date_of_birth)
                    ? $employee->Employee->date_of_birth
                    : 'Empty';
            })
            ->addColumn('place_of_birth', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->place_of_birth)
                    ? $employee->Employee->place_of_birth
                    : 'Empty';
            })
            ->addColumn('biological_mother_name', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->biological_mother_name)
                    ? $employee->Employee->biological_mother_name
                    : 'Empty';
            })
            ->addColumn('religion', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->religion)
                    ? $employee->Employee->religion
                    : 'Empty';
            })
            ->addColumn('current_address', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->current_address)
                    ? $employee->Employee->current_address
                    : 'Empty';
            })
            ->addColumn('id_card_address', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->id_card_address)
                    ? $employee->Employee->id_card_address
                    : 'Empty';
            })
            ->addColumn('last_education', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->last_education)
                    ? $employee->Employee->last_education
                    : 'Empty';
            })
            ->addColumn('institution', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->institution)
                    ? $employee->Employee->institution
                    : 'Empty';
            })
            ->addColumn('npwp', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->npwp)
                    ? $employee->Employee->npwp
                    : 'Empty';
            })
            ->addColumn('bpjs_kes', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->bpjs_kes)
                    ? $employee->Employee->bpjs_kes
                    : 'Empty';
            })
            ->addColumn('bpjs_ket', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->bpjs_ket)
                    ? $employee->Employee->bpjs_ket
                    : 'Empty';
            })
            ->addColumn('email', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->email)
                    ? $employee->Employee->email
                    : 'Empty';
            })
            ->addColumn('emergency_contact_name', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->emergency_contact_name)
                    ? $employee->Employee->emergency_contact_name
                    : 'Empty';
            })

            ->addColumn('notes', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->notes)
                    ? $employee->Employee->notes
                    : 'Empty';
            })
            ->addColumn('created_at', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->created_at)
                    ? $employee->Employee->created_at
                    : 'Empty';
            })
            ->addColumn('bank_name', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->bank->name)
                    ? $employee->Employee->bank->name
                    : 'Empty';
            })
            ->addColumn('bank_account_number', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->bank_account_number)
                    ? $employee->Employee->bank_account_number
                    : 'Empty';
            })
            ->addColumn('status', function ($employee) {
                return !empty($employee->Employee) && !empty($employee->Employee->status)
                    ? $employee->Employee->status
                    : 'Empty';
            })
          
            
            ->make(true);
    }


    public function edit($hashedId)
    {
        $employee = User::with('Employee', 'Employee.store', 'Employee.department', 'Employee.position','Employee.bank')->get()->first(function ($u) use ($hashedId) {
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
        $status_employee = ['PKWT', 'DW', 'PKWTT','On Job Training'];
        $child = ['0', '1', '2', '3', '4', '5'];
        $marriage = ['Yes', 'No'];
        $gender = ['Male', 'Female', 'MD'];
        $status = ['Active', 'Pending', 'Inactive', 'On Leave','Mutation'];
        $banks = Banks::get();
        $religion = ['Buddha', 'Catholic Christian', 'Christian', 'Confusian', 'Hindu', 'Islam'];
        $last_education = ['Elementary School', 'Junior High School', 'Senior High School', 'Diploma I','Diploma II','Diploma III','Diploma IV', 'Bachelor Degree','Masters degree','Vocational School','Lord'];
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
        $status_employee = ['PKWT', 'DW', 'PKWTT','On Job Training'];
        $status_child = ['0', '1', '2', '3', '4', '5'];
        $status_marriage = ['Yes', 'No'];
        $status_gender = ['Male', 'Female', 'MD'];
        $status =  ['Active', 'Pending', 'Inactive', 'On Leave','Mutation'];

        $status_religion = ['Buddha', 'Catholic Christian', 'Christian', 'Confusian', 'Hindu', 'Islam'];
        $status_last_education = ['Elementary School', 'Junior High School', 'Senior High School', 'Diploma I','Diploma II','Diploma III','Diploma IV', 'Bachelor Degree','Masters degree','Vocational School','Lord'];
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
            'employee_name' => ['required', 'string', 'max:255','unique:employees_tables,employee_name', new NoXSSInput()],
            'bpjs_kes' => ['required', 'string', 'max:255', 'unique:employees_tables,bpjs_kes', new NoXSSInput()],
            'bpjs_ket' => ['required', 'string', 'max:255', 'unique:employees_tables,bpjs_ket', new NoXSSInput()],
            'email' => ['required', 'string', 'max:255', 'unique:employees_tables,email', new NoXSSInput()],
            'emergency_contact_name' => ['required', 'string', 'max:255', new NoXSSInput()],
            'marriage' => ['required', 'string', 'max:255', new NoXSSInput()],
            'notes' => ['nullable', 'string', 'max:255', new NoXSSInput()],
            'child' => ['required', 'string', 'max:255', new NoXSSInput()],
            'gender' => ['required', 'string', 'max:255', new NoXSSInput()],
            'telp_number' => ['required', 'numeric', 'digits_between:10,13', 'unique:employees_tables,telp_number', new NoXSSInput()],
            'status_employee' => ['required', 'string', 'max:255', new NoXSSInput()],
            'nik' => ['required', 'max:20', 'unique:employees_tables,nik', new NoXSSInput()],
            'bank_account_number' => ['required', 'max:20', 'unique:employees_tables,bank_account_number', new NoXSSInput()],
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
            'npwp' => ['required', 'string', 'max:50', 'unique:employees_tables,npwp', new NoXSSInput()],
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
            'employee_name' => ['required', 'string', 'max:255',Rule::unique('employees_tables', 'employee_name')->ignore($user->Employee->id),
            new NoXSSInput()],
            'bpjs_kes' => ['required', 'string', 'max:255',Rule::unique('employees_tables', 'bpjs_kes')->ignore($user->Employee->id), new NoXSSInput()],
            'bpjs_ket' => ['required', 'string', 'max:255',Rule::unique('employees_tables', 'bpjs_ket')->ignore($user->Employee->id), new NoXSSInput()],
            'email' => ['required', 'string', 'max:255',],
            // Rule::unique('employees_tables', 'email')->ignore($user->Employee->id), new NoXSSInput()],
            'emergency_contact_name' => ['required', 'string', 'max:255', new NoXSSInput()],
            'marriage' => ['required', 'string', 'max:255', new NoXSSInput()],
            'notes' => ['nullable', 'string', 'max:255', new NoXSSInput()],
            'child' => ['required', 'string', 'max:255', new NoXSSInput()],
            'gender' => ['required', 'string', 'max:255', new NoXSSInput()],
            'telp_number' => ['required', 'numeric', 'digits_between:10,13',Rule::unique('employees_tables', 'telp_number')->ignore($user->Employee->id), new NoXSSInput()],
            'status_employee' => ['required', 'string', 'max:255', new NoXSSInput()],
            'nik' => ['required', 'max:20',Rule::unique('employees_tables', 'nik')->ignore($user->Employee->id), new NoXSSInput()],
            'bank_account_number' => ['required', 'max:20',Rule::unique('employees_tables', 'bank_account_number')->ignore($user->Employee->id), new NoXSSInput()],
            'last_education' => ['required', 'string', 'max:255', new NoXSSInput()],
            'religion' => ['required', 'string', new NoXSSInput()],
            // 'daily_allowance' => ['nullable','string',
            // new NoXSSInput()],
            'place_of_birth' => ['required', 'string', 'max:255', new NoXSSInput()],
            'biological_mother_name' => ['required', 'string', 'max:255', new NoXSSInput()],
            'current_address' => ['required', 'string', 'max:255', new NoXSSInput()],
            'id_card_address' => ['required', 'string', 'max:255', new NoXSSInput()],
            'institution' => ['required', 'string', 'max:255', new NoXSSInput()],
            'npwp' => ['required', 'string', 'max:50',Rule::unique('employees_tables')->ignore($user->id), new NoXSSInput()],
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
