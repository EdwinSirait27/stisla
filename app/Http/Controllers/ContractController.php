<?php
namespace App\Http\Controllers;
use App\Models\Banks;
use App\Models\Company;
use App\Models\Departments;
use App\Models\Employee;
use App\Models\Grading;
use App\Models\Payrolls;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;
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
use App\Models\Contract;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ContractController extends Controller
{
    public function index()
    {
        $countactivecontracts = Contract::where('contract_status', 'Active')->count();
        $countexpiredcontracts = Contract::where('contract_status', 'Expired')->count();
        $countterminatedcontracts = Contract::where('contract_status', 'Terminated')->count();
        $contractstatuses = Contract::getContractStatusOptions();
        $contracttypes = Contract::getContractTypeOptions();
        $employeestatuses = Employee::getStatusOptions();
        $structures = Structuresnew::with('submissionposition')->where('', 'Active');
        $gradings = Grading::pluck('grading_name', 'id');
        $groups = Groups::pluck('remark', 'id');
        $companies = Company::pluck('name', 'id');
        $departments = Departments::pluck('department_name', 'id');
        $locations = Stores::pluck('name', 'id');
        return view('pages.Contract.Contract', compact('contracttypes', 'employeestatuses', 'companies', 'locations', 'departments', 'countactivecontracts', 'countexpiredcontracts', 'countterminatedcontracts', 'contractstatuses', 'structures', 'gradings', 'groups'));
    }
    public function getActivities(Request $request)
    {
        if ($request->ajax()) {
            $query = Activity::where('log_name', 'contract')
                ->where('subject_type', Contract::class)
                ->with(['causer.employee'])  // eager load sampai employee
                ->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('description', function ($row) {
                    return $row->description ?? '-';
                })
                ->addColumn('causer', function ($row) {
                    return $row->causer?->employee?->employee_name ?? '-';
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at->format('d M Y H:i');
                })
                ->addColumn('event', function ($row) {
                    return ucfirst($row->event ?? '-');
                })
                ->addColumn('changes', function ($row) {
                    $old = $row->properties['old'] ?? [];
                    $new = $row->properties['attributes'] ?? [];

                    $diff = [];
                    foreach ($new as $key => $value) {
                        $diff[$key] = [
                            'old' => $old[$key] ?? null,
                            'new' => $value,
                        ];
                    }

                    return json_encode($diff);
                })
                ->filter(function ($instance) use ($request) {
                    if ($request->has('search') && $request->get('search')['value'] != '') {
                        $search = $request->get('search')['value'];

                        $instance->where(function ($q) use ($search) {
                            $q->where('description', 'like', "%{$search}%")
                                ->orWhere('event', 'like', "%{$search}%")
                                ->orWhereHas('causer.employee', function ($q2) use ($search) {
                                    $q2->where('employee_name', 'like', "%{$search}%");
                                });
                        });
                    }
                })
                ->rawColumns(['description'])
                ->make(true);
        }
    }

    public function getContracts(Request $request)
    {
        $query = Contract::query()

            ->leftJoin('employees_tables as e', 'contract.employee_id', '=', 'e.id')
            ->leftJoin('structures_tables as st', 'st.id', '=', 'e.structure_id')
            ->leftJoin('submission_position_tables as sp', 'sp.id', '=', 'st.submission_position_id')

            ->leftJoin('position_tables as p', 'p.id', '=', 'sp.position_id')
            ->leftJoin('departments_tables as d', 'd.id', '=', 'sp.department_id')
            ->leftJoin('stores_tables as s', 's.id', '=', 'sp.store_id')
            ->leftJoin('company_tables as c', 'c.id', '=', 'sp.company_id')

            ->leftJoin('grading as g', 'g.id', '=', 'e.grading_id')
            ->leftJoin('groups_tables as gr', 'gr.id', '=', 'e.group_id')

            ->select([
                'contract.*',
                'e.employee_name',
                'e.employee_pengenal',

                'p.name as position_name',
                'd.department_name',
                's.name as store_name',
                'g.grading_name',
                'gr.remark',
                'contract.contract_type',
                'contract.contract_status',
                'e.status as employee_status',
                'contract.created_at',
                'c.name as company_name',
            ]);

        $query->when(
            $request->filled('filter_company'),
            fn($q) => $q->where('c.name', $request->filter_company)
        );
        $query->when(
            $request->filled('filter_position'),
            fn($q) => $q->where('p.name', $request->filter_position)
        );
        $query->when(
            $request->filled('filter_store'),
            fn($q) => $q->where('s.name', $request->filter_store)
        );
        $query->when(
            $request->filled('filter_department'),
            fn($q) => $q->where('d.department_name', $request->filter_department)
        );
        $query->when(
            $request->filled('filter_group'),
            fn($q) =>
            $q->where('gr.remark', $request->filter_group)
        );
        $query->when(
            $request->filled('filter_grading'),
            fn($q) =>
            $q->where('g.grading_name', $request->filter_grading)
        );
        $query->when(
            $request->filled('filter_contract_type'),
            fn($q) =>
            $q->where('contract.contract_type', $request->filter_contract_type)
        );
        $query->when(
            $request->filled('filter_contract_status'),
            fn($q) =>
            $q->where('contract.contract_status', $request->filter_contract_status)
        );
        $query->when(
            $request->filled('filter_employee_status'),
            fn($q) =>
            $q->where('e.status', $request->filter_employee_status)
        );
        return DataTables::of($query)
            ->addColumn('action', function ($e) {
                $id = $e->id;
                return '
                <a href="' . route('editcontract', $id) . '" class="mx-2">
                    <i class="fas fa-user-edit text-secondary"></i>
                </a>
                <a href="' . route('showcontract', $id) . '" class="mx-2">
                    <i class="fas fa-eye text-secondary"></i>
                </a>';
            })
            ->filterColumn('employee_name', fn($q, $k) =>
            $q->where('e.employee_name', 'like', "%$k%"))
            ->filterColumn('employee_pengenal', fn($q, $k) =>
            $q->where('e.employee_pengenal', 'like', "%$k%"))
            ->filterColumn('position_name', fn($q, $k) =>
            $q->where('p.name', 'like', "%$k%"))
            ->filterColumn('remark', fn($q, $k) =>
            $q->where('gr.remark', 'like', "%$k%"))
            ->filterColumn('department_name', fn($q, $k) =>
            $q->where('d.department_name', 'like', "%$k%"))
            ->filterColumn('store_name', fn($q, $k) =>
            $q->where('s.name', 'like', "%$k%"))
            ->filterColumn('company_name', fn($q, $k) =>
            $q->where('c.name', 'like', "%$k%"))
            ->filterColumn('grading_name', fn($q, $k) =>
            $q->where('g.grading_name', 'like', "%$k%"))
            ->filterColumn('contract_type', fn($q, $k) =>
            $q->where('contract.contract_type', 'like', "%$k%"))
            ->filterColumn('contract_status', fn($q, $k) =>
            $q->where('contract.contract_status', 'like', "%$k%"))
            ->filterColumn('employee_status', fn($q, $k) =>
            $q->where('e.status', 'like', "%$k%"))
             ->editColumn('created_at', function ($e) {
                return optional($e->created_at)
                    ->timezone('Asia/Makassar')
                    ->translatedFormat('d F Y');
            })
            ->rawColumns(['action'])
            ->make(true);
    }


    public function edit($id) {}

    public function show($id) {}
//     public function create()
// {
// $employeeData = Employee::whereIn('status', ['Active', 'Mutation', 'Pending'])
//     ->select('id', 'employee_name', 'join_date')
//     ->get();

// $employees = $employeeData->pluck('employee_name', 'id');
// $employeeJoinDates = $employeeData
//     ->mapWithKeys(function ($item) {
//         return [$item->id => \Carbon\Carbon::parse($item->join_date)->format('Y-m-d')];
//     })
//     ->toArray();
//   $structures = Structuresnew::with('submissionposition.positionRelation')
//     ->get()
//     ->pluck('submissionposition.positionRelation.name', 'id');
//     $statusOptions = Contract::getContractStatusOptions();
//     $typeOptions   = Contract::getContractTypeOptions();
//     return view('pages.contract.createcontract', compact(
//         'employees',
//         'employeeJoinDates',
//         'structures',
//         'statusOptions',
//         'typeOptions'
//     ));
// }
public function create()
{
    if (!auth()->user()->hasRole('HeadHR')) {
        abort(403, 'Unauthorized');
    }
    $isHeadHR = auth()->user()->hasRole('HeadHR'); // ✅ WAJIB ADA

    $employeeData = Employee::whereIn('status', ['Active', 'Mutation', 'Pending'])
        ->select('id', 'employee_name', 'join_date')
        ->get();

    $employees = $employeeData->pluck('employee_name', 'id');

    $employeeJoinDates = $employeeData
        ->mapWithKeys(function ($item) {
            return [$item->id => \Carbon\Carbon::parse($item->join_date)->format('Y-m-d')];
        })
        ->toArray();

    $structures = Structuresnew::with('submissionposition.positionRelation')
        ->get()
        ->pluck('submissionposition.positionRelation.name', 'id');

    $statusOptions = Contract::getContractStatusOptions();
    $typeOptions   = Contract::getContractTypeOptions();
$confirmedAt = session('contract_password_confirmed_at');

$isPasswordExpired = true;

if ($confirmedAt) {
    $isPasswordExpired = now()->diffInMinutes($confirmedAt) >= 5;
}
    return view('pages.contract.createcontract', compact(
        'employees',
        'employeeJoinDates',
        'structures',
        'statusOptions',
        'typeOptions',
        'isHeadHR',
    'isPasswordExpired'
    ));
}
public function checkPassword(Request $request)
{
    if (Hash::check($request->password, auth()->user()->password)) {

        session([
            'contract_password_confirmed_at' => now()
        ]);

        return response()->json(['success' => true]);
    }

    return response()->json(['success' => false]);
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
                'nullable',
                'mimes:jpg,jpeg,png,webp',
                'max:512'

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
            'company_email' => ['nullable', 'string', 'max:255', new NoXSSInput()],
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
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:512'
            ],
            'photos.mimes' => 'The photo must be a file of type: jpg, jpeg, png, webp.',
            'photos.max' => 'photos must under 512 kb.',

        ]);
        $filePath = null;

        if ($request->hasFile('photos')) {
            $file = $request->file('photos');

            if ($file->getSize() > 512 * 1024) {
                return back()->withErrors(['photos' => 'Photos must be under 512 KB']);
            }

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
                'company_email' => $validatedData['company_email'] ?? '',
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
            'photos' => [
                'nullable',
                'mimes:jpg,jpeg,png,webp',
                'max:512'
            ],
            'photos.mimes' => 'The photo must be a file of type: jpg, jpeg, png, webp.',
            'photos.max' => 'photos must under 512 kb.',
            'join_date' => ['required', 'date_format:Y-m-d', new NoXSSInput()],
            'end_date' => ['nullable', 'date_format:Y-m-d', new NoXSSInput()],
            'date_of_birth' => ['required', 'date_format:Y-m-d', new NoXSSInput()],

            'employee_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('employees_tables', 'employee_name')->ignore($user->Employee->id),
                new NoXSSInput()
            ],

            'structure_id' => ['nullable', 'exists:structures_tables,id', new NoXSSInput()],
            'grading_id' => ['nullable', 'exists:grading,id', new NoXSSInput()],
            'group_id' => ['nullable', 'exists:groups_tables,id', new NoXSSInput()],
            'bpjs_kes' => ['required', 'string', 'max:255'],
            'bpjs_ket' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255'],
            'company_email' => ['nullable', 'string', 'max:255'],
            'emergency_contact_name' => ['required', 'string', 'max:255', new NoXSSInput()],
            'marriage' => ['required', 'string', 'max:255', new NoXSSInput()],
            'notes' => ['nullable', 'string', 'max:255', new NoXSSInput()],
            'child' => ['required', 'string', 'max:255', new NoXSSInput()],
            'gender' => ['required', 'string', 'max:255', new NoXSSInput()],
            'telp_number' => [
                'required',
                'numeric',
                'digits_between:10,13',
                Rule::unique('employees_tables', 'telp_number')->ignore($user->Employee->id),
                new NoXSSInput()
            ],
            'status_employee' => ['required', 'string', 'max:255', new NoXSSInput()],
            'nik' => [
                'required',
                'max:20',
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
                'required',
                'string',
                'max:50',
                Rule::unique('employees_tables', 'pin')->ignore($user->Employee->id),
                new NoXSSInput()
            ],
            'position_id' => ['nullable', 'exists:position_tables,id', new NoXSSInput()],
            'store_id' => ['nullable', 'exists:stores_tables,id', new NoXSSInput()],
            'company_id' => ['nullable', 'exists:company_tables,id', new NoXSSInput()],
            'department_id' => ['nullable', 'exists:departments_tables,id', new NoXSSInput()],
            'banks_id' => ['required', 'exists:banks_tables,id', new NoXSSInput()],
        ]);
        // $filePath = $user->Employee->photos;
        $filePath = optional($user->Employee)->photos;
        try {
            DB::transaction(function () use ($user, &$validatedData, $request, &$filePath) {

                if ($request->hasFile('photos')) {
                    $file = $request->file('photos');

                    $fileName = hash('sha256', $file->getClientOriginalName() . time()) . '.' .
                        $file->getClientOriginalExtension();

                    $folderPath = 'employeesphotos/' . date('Y/m');

                    Storage::disk('public')->putFileAs($folderPath, $file, $fileName);

                    $newFilePath = $folderPath . '/' . $fileName;

                    if ($filePath && Storage::disk('public')->exists($filePath)) {
                        Storage::disk('public')->delete($filePath);
                    }

                    $filePath = $validatedData['photos'] = $newFilePath;
                }
                /** --------------------------
                 *  Lock employee row
                 * -------------------------*/
                $employee = $user->Employee()->lockForUpdate()->first();
                $oldStructureId = $employee->structure_id;
                $statusEmployee = $validatedData['status'];
                $inactiveStatus = ['Resign', 'On Leave'];
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
                        // $validatedData['store_id'] = $submission->store_id;
                        if (empty($validatedData['store_id'])) {
                            $validatedData['store_id'] = $submission->store_id;
                        }

                        // $validatedData['position_id'] = $submission->position_id;
                        if (empty($validatedData['position_id'])) {
                            $validatedData['position_id'] = $submission->position_id;
                        }

                        $validatedData['is_manager'] = $submission->is_manager;
                    }
                    $newStructure->update(['status' => 'active']);
                }
                $employee->update($validatedData);
                if ($oldStructureId && $oldStructureId != ($validatedData['structure_id'] ?? null)) {
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
