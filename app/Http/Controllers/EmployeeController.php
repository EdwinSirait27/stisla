<?php

namespace App\Http\Controllers;

use App\Models\Departments;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;
use App\Models\User;
use Yajra\DataTables\DataTables;
use App\Models\Stores;
use Illuminate\Support\Facades\Hash;
use App\Rules\NoXSSInput;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
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
                    </a>

                    <a href="' . route('Employee.show', $employee->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="show employee: ' . e($employee->Employee->employee_name) . '">
                        <i class="fas fa-eye text-secondary"></i>
                    </a>';
                return $employee;
            });
        return DataTables::of($employees)
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
            ->rawColumns(['position_name', 'status', 'department_name', 'created_at', 'employee_name', 'name_store', 'action'])
            ->make(true);
    }
  
    public function edit($hashedId)
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
    $departments = Departments::with('user.Employee')->get();
    $stores = Stores::with('user.Employee')->get();
    $status_employee = ['PKWT', 'DW','PKWTT'];
    $child = ['0', '1', '2', '3', '4', '5'];
    $marriage = ['Yes', 'No'];
    $gender = ['Male', 'Female', 'MD'];
    $status = ['Active', 'Pending','Inactive', 'On Leave'];
    $religion = ['Buddha', 'Catholic Christian', 'Christian', 'Confusian', 'Hindu', 'Islam'];
    $last_education = ['Elementary School', 'Junior High School', 'Senior High School', 'Diploma', 'Bachelor Degree'];
    // dd($employee->Employee->join_date, $employee->Employee->getOriginal('join_date'));

    return view('pages.Employee.edit', [
        'employee' => $employee,
        'status_employee' => $status_employee,
        'child' => $child,
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
    $departments = Departments::with('user.Employee')->get();
    $stores = Stores::with('user.Employee')->get();
    $status_employee = ['PKWT', 'DW','PKWTT'];
    $child = ['0', '1', '2', '3', '4', '5'];
    $marriage = ['Yes', 'No'];
    $gender = ['Male', 'Female', 'MD'];
    $status = ['Active', 'Pending','Inactive', 'On Leave'];
    $religion = ['Buddha', 'Catholic Christian', 'Christian', 'Confusian', 'Hindu', 'Islam'];
    $last_education = ['Elementary School', 'Junior High School', 'Senior High School', 'Diploma', 'Bachelor Degree'];
    // dd($employee->Employee->join_date, $employee->Employee->getOriginal('join_date'));

    return view('pages.Employee.show', [
        'employee' => $employee,
        'status_employee' => $status_employee,
        'child' => $child,
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
        $status_employee = ['PKWT', 'DW','PKWTT'];
        $status_child = ['0', '1', '2', '3', '4', '5'];
        $status_marriage = ['Yes', 'No'];
        $status_gender = ['Male', 'Female', 'MD'];
        $status = ['Active', 'Inactive', 'On Leave'];
        $status_religion = ['Buddha', 'Catholic Christian', 'Christian', 'Confusian', 'Hindu', 'Islam'];
        $status_last_education = ['Elementary School', 'Junior High School', 'Senior High School', 'Diploma', 'Bachelor Degree'];
        return view('pages.Employee.create', compact('stores', 'status_marriage', 'positions', 'departments', 'status_employee', 'status_child', 'status_gender', 'status_religion', 'status_last_education', 'status'));
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
            'religion' => ['required', 'string', new NoXSSInput()],
            'employee_name' => ['required', 'string', 'max:255', new NoXSSInput()],
            'bpjs_kes' => ['required', 'string', 'max:255', new NoXSSInput()],
            'bpjs_ket' => ['required', 'string', 'max:255', new NoXSSInput()],
            'email' => ['required', 'string', 'max:255', new NoXSSInput()],
            'emergency_contact_name' => ['required', 'string', 'max:255', new NoXSSInput()],
            'salary' => [
                'required',
                'numeric',
                'between:0,9999999999.99',
                'regex:/^\d+(\.\d{1,2})?$/',
                new NoXSSInput()
            ],
            'house_allowance' => [
                'nullable',
                'numeric',
                'between:0,9999999999.99',
                'regex:/^\d+(\.\d{1,2})?$/',
                new NoXSSInput()
            ],
            'meal_allowance' => [
                'nullable',
                'numeric',
                'between:0,9999999999.99',
                'regex:/^\d+(\.\d{1,2})?$/',
                new NoXSSInput()
            ],
            'transport_allowance' => [
                'nullable',
                'numeric',
                'between:0,9999999999.99',
                'regex:/^\d+(\.\d{1,2})?$/',
                new NoXSSInput()
            ],
            'total_salary' => [
                'nullable',
                'numeric',
                'between:0,9999999999.99',
                'regex:/^\d+(\.\d{1,2})?$/',
                new NoXSSInput()
            ],
            'marriage' => ['required', 'string', 'max:255', new NoXSSInput()],
            'notes' => ['nullable', 'string', 'max:255', new NoXSSInput()],
            'child' => ['required', 'string', 'max:255', new NoXSSInput()],
            'gender' => ['required', 'string', 'max:255', new NoXSSInput()],
            'status_employee' => ['required', 'string', 'max:255', new NoXSSInput()],
            'last_education' => ['required', 'string', 'max:255', new NoXSSInput()],
            'nik' => ['required', 'max:20', new NoXSSInput()],
            'employee_id' => ['nullable', 'string', 'max:30', 'unique:employees_tables,employee_id', new NoXSSInput()],
            'position_id' => ['nullable', 'exists:position_tables,id', new NoXSSInput()],
            'store_id' => ['nullable', 'exists:stores_tables,id', new NoXSSInput()],
            'department_id' => ['nullable', 'exists:departments_tables,id', new NoXSSInput()],
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.regex' => 'NIK hanya boleh berupa angka.',
            'nik.max' => 'NIK MAX 20 karakter.',

        ]);
        try {
            DB::beginTransaction();
            $lastEmployee = Employee::orderBy('employee_id', 'desc')->first();

            $currentYearMonth = date('Ym'); // Format: TahunBulan (contoh: 202504)

            if ($lastEmployee) {
                $lastId = $lastEmployee->employee_id;

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
            $totalSalary =
                (float) ($validatedData['salary'] ?? 0) +
                (float) ($validatedData['house_allowance'] ?? 0) +
                (float) ($validatedData['meal_allowance'] ?? 0) +
                (float) ($validatedData['transport_allowance'] ?? 0);
            $employees = Employee::create([
                'employee_name' => $validatedData['employee_name'] ?? '',
                // 'employee_name' => $request->employee_name,
                'nik' => $validatedData['nik'] ?? '',
                'employee_id' => $employeeId,
                'position_id' => $validatedData['position_id'] ?? '',
                'store_id' => $validatedData['store_id'] ?? '',
                'department_id' => $validatedData['department_id'] ?? '',
                'status_employee' => $validatedData['status_employee'] ?? '',
                'join_date' => $validatedData['join_date'] ?? '',
                'marriage' => $validatedData['marriage'] ?? '',
                'child' => $validatedData['child'] ?? '',
                'telp_number' => $validatedData['telp_number'] ?? '',
                'gender' => $validatedData['gender'] ?? '',
                'date_of_birth' => $validatedData['date_of_birth'] ?? '',
                'place_of_birth' => $validatedData['place_of_birth'] ?? '',
                'biological_mother_name' => $validatedData['biological_mother_name'] ?? '',
                'religion' => $validatedData['religion'] ?? '',
                'current_address' => $validatedData['current_address'] ?? '',
                'id_card_address' => $validatedData['id_card_address'] ?? '',
                'last_education' => $validatedData['last_education'] ?? '',
                'institution' => $validatedData['institution'] ?? '',
                'npwp' => $validatedData['npwp'] ?? '',
                'bpjs_kes' => $validatedData['bpjs_kes'] ?? '',
                'bpjs_ket' => $validatedData['bpjs_ket'] ?? '',
                'email' => $validatedData['email'] ?? '',
                'emergency_contact_name' => $validatedData['emergency_contact_name'] ?? '',
                'emergency_contact_number' => $validatedData['emergency_contact_number'] ?? '',
                'salary' => $validatedData['salary'] ?? '',
                'house_allowance' => $validatedData['house_allowance'] ?? '',
                'meal_allowance' => $validatedData['meal_allowance'] ?? '',
                'transport_allowance' => $validatedData['transport_allowance'] ?? '',
                'total_salary' => $totalSalary,
                'notes' => $validatedData['notes'] ?? '',
                'status' => $validatedData['status'] ?? 'Pending',
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
            'religion' => ['required', 'string', new NoXSSInput()],
            'employee_name' => ['required', 'string', 'max:255', new NoXSSInput()],
            'place_of_birth' => ['required', 'string', 'max:255', new NoXSSInput()],
            'bpjs_kes' => ['required', 'string', 'max:255', new NoXSSInput()],
            'telp_number' => ['required', 'string', 'max:255', new NoXSSInput()],
            'bpjs_ket' => ['required', 'string', 'max:255', new NoXSSInput()],
            'email' => ['required', 'string', 'max:255', new NoXSSInput()],
            'emergency_contact_name' => ['required', 'string', 'max:255', new NoXSSInput()],
            'salary' => [
                'required',
                'numeric',
                'between:0,9999999999.99',
                'regex:/^\d+(\.\d{1,2})?$/',
                new NoXSSInput()
            ],
            'house_allowance' => [
                'nullable',
                'numeric',
                'between:0,9999999999.99',
                'regex:/^\d+(\.\d{1,2})?$/',
                new NoXSSInput()
            ],
            'meal_allowance' => [
                'nullable',
                'numeric',
                'between:0,9999999999.99',
                'regex:/^\d+(\.\d{1,2})?$/',
                new NoXSSInput()
            ],
            'transport_allowance' => [
                'nullable',
                'numeric',
                'between:0,9999999999.99',
                'regex:/^\d+(\.\d{1,2})?$/',
                new NoXSSInput()
            ],
            'total_salary' => [
                'nullable',
                'numeric',
                'between:0,9999999999.99',
                'regex:/^\d+(\.\d{1,2})?$/',
                new NoXSSInput()
            ],
            'marriage' => ['required', 'string', 'max:255', new NoXSSInput()],
            'notes' => ['nullable', 'string', 'max:255', new NoXSSInput()],
            'child' => ['required', 'string', 'max:255', new NoXSSInput()],
            'biological_mother_name' => ['required', 'string', 'max:255', new NoXSSInput()],
            'gender'    => ['required', 'string', 'max:255', new NoXSSInput()],
            'status_employee' => ['required', 'string', 'max:255', new NoXSSInput()],
            'current_address' => ['required', 'string', 'max:255', new NoXSSInput()],
            'last_education' => ['required', 'string', 'max:255', new NoXSSInput()],
            'id_card_address' => ['required', 'string', 'max:255', new NoXSSInput()],
            'institution' => ['required', 'string', 'max:255', new NoXSSInput()],
            'nik' => ['required', 'max:20', new NoXSSInput()],
            'npwp' => ['required', 'max:30', new NoXSSInput()],
            'position_id' => ['nullable', 'exists:position_tables,id', new NoXSSInput()],
            'store_id' => ['nullable', 'exists:stores_tables,id', new NoXSSInput()],
            'department_id' => ['nullable', 'exists:departments_tables,id', new NoXSSInput()],
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.regex' => 'NIK hanya boleh berupa angka.',
            'nik.max' => 'NIK MAX 20 karakter.',
        ]);
        // Tidak perlu implode untuk user_type karena sudah string
      
        DB::beginTransaction();
        $user->Employee->update([
            'join_date' => $validatedData['join_date'],
            'email' => $validatedData['email'],
            'date_of_birth' => $validatedData['date_of_birth'],
            'place_of_birth' => $validatedData['place_of_birth'],
            'religion' => $validatedData['religion'],
            'employee_name' => $validatedData['employee_name'],
            'id_card_address' => $validatedData['id_card_address'],
            'institution' => $validatedData['institution'],
            'telp_number' => $validatedData['telp_number'],
            'npwp' => $validatedData['npwp'],
            'biological_mother_name' => $validatedData['biological_mother_name'],
            'current_address' => $validatedData['current_address'],
            'bpjs_kes' => $validatedData['bpjs_kes'],
            'bpjs_ket' => $validatedData['bpjs_ket'],
            'emergency_contact_name' => $validatedData['emergency_contact_name'],
            'salary' => $validatedData['salary'],
            'house_allowance' => $validatedData['house_allowance'],
            'meal_allowance' => $validatedData['meal_allowance'],
            'transport_allowance' => $validatedData['transport_allowance'],
            // 'total_salary' => $validatedData['total_salary'],
            'marriage' => $validatedData['marriage'],
            'notes' => $validatedData['notes'],
            'child' => $validatedData['child'],
            'gender' => $validatedData['gender'],
            'status_employee' => $validatedData['status_employee'],
            'last_education' => $validatedData['last_education'],
            'nik' => $validatedData['nik'],
            'position_id' => $validatedData['position_id'],
            'store_id' => $validatedData['store_id'],
            'department_id' => $validatedData['department_id'],
        ]);
        DB::commit();
        return redirect()->route('pages.Employee')->with('success', 'Employee Berhasil Diupdate.');
    }
}
