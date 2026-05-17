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
use App\Exports\EmployeesExport;
use App\Exports\EmployeesExportall;
use Yajra\DataTables\DataTables;
use App\Models\Stores;
use App\Models\Structuresnew;
use Illuminate\Support\Facades\Hash;
use App\Rules\NoXSSInput;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Mail\WelcomeEmployeeMail;
use App\Models\Groups;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
class EmployeeController extends Controller
{
    public function indexall()
    {
        $countactives = Employee::where('status', 'Active')->count();
        $countpendings = Employee::where('status', 'Pending')->count();
        $countresigns = Employee::where('status', 'Resign')->count();
        $departments = Departments::pluck('department_name', 'id');
        $gradings = Grading::pluck('grading_name', 'id');
        $groups = Groups::pluck('remark', 'id');
        $companies = Company::pluck('name', 'id');
        $locations = Stores::pluck('name', 'id');
        $employeestatuses = Employee::getStatusEmployeeOptions();
        $statuses = Employee::getStatusOptions();
        $banks = Banks::pluck('name', 'id');
        $genders = Employee::getGenderOptions();
        $marriages = Employee::getMarriageOptions();
        $religions = Employee::getReligionOptions();
        $lasteducations = Employee::getLastEducationOptions();
        return view('pages.Employeeall.Employeeall', compact('marriages', 'genders', 'lasteducations', 'religions', 'banks', 'departments', 'companies', 'locations', 'employeestatuses', 'statuses', 'countactives', 'countpendings', 'countresigns', 'groups', 'gradings'));
    }
    public function index()
    {
        $countactives = Employee::where('status', 'Active')->count();
        $countpendings = Employee::where('status', 'Pending')->count();
        $countresigns = Employee::where('status', 'Resign')->count();
        $departments = Departments::pluck('department_name', 'id');
        $gradings = Grading::pluck('grading_name', 'id');
        $groups = Groups::pluck('remark', 'id');
        $companies = Company::pluck('name', 'id');
        $locations = Stores::pluck('name', 'id');
        $employeestatuses = Employee::getStatusEmployeeOptions();
        $statuses = Employee::getStatusOptions();
        return view('pages.Employee.Employee', compact('departments', 'companies', 'locations', 'employeestatuses', 'statuses', 'countactives', 'countpendings', 'countresigns', 'groups', 'gradings'));
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
    public function getEmployeesall(Request $request)
    {
        $isHeadHR = auth()->user()->hasAnyRole(['HeadHR', 'HR', 'Admin']);

        $query = User::query()
            ->with([
                'employee',
                'employee.position',
                'employee.department',
                'employee.grading',
                'employee.group',
                'employee.store',
                'employee.bank',
                'employee.company'
            ])
            ->leftJoin('employees_tables', 'users.employee_id', '=', 'employees_tables.id')
            ->leftJoin('position_tables', 'position_tables.id', '=', 'employees_tables.position_id')
            ->leftJoin('stores_tables', 'stores_tables.id', '=', 'employees_tables.store_id')
            ->leftJoin('departments_tables', 'departments_tables.id', '=', 'employees_tables.department_id')
            ->leftJoin('grading', 'grading.id', '=', 'employees_tables.grading_id')
            ->leftJoin('groups_tables', 'groups_tables.id', '=', 'employees_tables.group_id')
            ->leftJoin('company_tables', 'company_tables.id', '=', 'employees_tables.company_id')
            ->leftJoin('banks_tables', 'banks_tables.id', '=', 'employees_tables.banks_id')
            ->select([
                'users.*',
                'employees_tables.employee_name',
                'employees_tables.employee_pengenal',
                'employees_tables.bank_account_number',
                'employees_tables.join_date',
                'employees_tables.end_date',
                'employees_tables.created_at',
                'employees_tables.marriage',
                'employees_tables.child',
                'employees_tables.telp_number',
                'employees_tables.nik',
                'employees_tables.gender',
                'employees_tables.date_of_birth',
                'employees_tables.place_of_birth',
                'employees_tables.biological_mother_name',
                'employees_tables.religion',
                'employees_tables.current_address',
                'employees_tables.id_card_address',
                'employees_tables.last_education',
                'employees_tables.institution',
                'employees_tables.npwp',
                'employees_tables.bpjs_kes',
                'employees_tables.bpjs_ket',
                'employees_tables.email',
                'employees_tables.company_email',
                'employees_tables.emergency_contact_name',
                'employees_tables.pin',
                'employees_tables.pending_email',
                'employees_tables.pending_telp_number',
                'employees_tables.status_employee',
                'employees_tables.status',
                'employees_tables.join_date',
                'position_tables.name as position_name',
                'groups_tables.remark as remark',
                'stores_tables.name as name',
                'banks_tables.name as bank_name',
                'departments_tables.department_name',
                'grading.grading_name',
                'company_tables.name as name_company',
            ]);
        $query->when(
            $request->filled('filter_company'),
            fn($q) =>
            $q->where('company_tables.name', $request->filter_company)
        );
        $query->when(
            $request->filled('filter_department'),
            fn($q) =>
            $q->where('departments_tables.department_name', $request->filter_department)
        );
        $query->when(
            $request->filled('filter_group'),
            fn($q) =>
            $q->where('groups_tables.remark', $request->filter_group)
        );
        $query->when(
            $request->filled('filter_grading'),
            fn($q) =>
            $q->where('grading.grading_name', $request->filter_grading)
        );
        $query->when(
            $request->filled('filter_store'),
            fn($q) =>
            $q->where('stores_tables.name', $request->filter_store)
        );
        $query->when(
            $request->filled('filter_emp_status'),
            fn($q) =>
            $q->where('employees_tables.status_employee', $request->filter_emp_status)
        );
        $query->when(
            $request->filled('filter_status'),
            fn($q) =>
            $q->where('employees_tables.status', $request->filter_status)
        );
        $query->when(
            $request->filled('filter_religion'),
            fn($q) =>
            $q->where('employees_tables.religion', $request->filter_religion)
        );
        $query->when(
            $request->filled('filter_marriage'),
            fn($q) =>
            $q->where('employees_tables.marriage', $request->filter_marriage)
        );
        $query->when(
            $request->filled('filter_last_education'),
            fn($q) =>
            $q->where('employees_tables.last_education', $request->filter_last_education)
        );
        $query->when(
            $request->filled('filter_gender'),
            fn($q) =>
            $q->where('employees_tables.gender', $request->filter_gender)
        );
        $query->when(
            $request->filled('filter_bank'),
            fn($q) =>
            $q->where('banks_tables.name', $request->filter_bank)
        );
        $query->when($request->filled('filter_los'), function ($q) use ($request) {
            $los = $request->filter_los;

            if ($los === 'under3months') {
                // Khusus kurang dari 3 bulan, operator berbeda
                $q->where('employees_tables.join_date', '>=', Carbon::now()->subMonths(3));
            } else {
                $date = match ($los) {
                    '1year'  => Carbon::now()->subYear(),
                    '3years' => Carbon::now()->subYears(3),
                    '5years' => Carbon::now()->subYears(5),
                    default  => null,
                };
                if ($date) {
                    $q->where('employees_tables.join_date', '<=', $date);
                }
            }
        });
        return DataTables::of($query)
            ->addColumn('length_of_service', function ($e) {
                if (!$e->join_date) return 'Empty';
                $diff = Carbon::parse($e->join_date)->diff(Carbon::now());
                return sprintf('%d year %d month %d days', $diff->y, $diff->m, $diff->d);
            })
            ->addColumn('action', function ($e) use ($isHeadHR) {
                if (!$isHeadHR) return '';
                $id_hashed = substr(hash('sha256', $e->id . env('APP_KEY')), 0, 8);
                return '
                <a href="' . route('Employee.edit', $id_hashed) . '" class="mx-2">
                    <i class="fas fa-user-edit text-secondary"></i>
                </a>
                <a href="' . route('Employee.show', $id_hashed) . '" class="mx-2">
                    <i class="fas fa-eye text-secondary"></i>
                </a>';
            })
            // Daftarkan kolom yang bisa di-search
            ->filterColumn('employee_name', fn($q, $k) => $q->where('employees_tables.employee_name', 'like', "%$k%"))
            ->filterColumn('employee_pengenal', fn($q, $k) => $q->where('employees_tables.employee_pengenal', 'like', "%$k%"))
            ->filterColumn('bank_account_number', fn($q, $k) => $q->where('employees_tables.bank_account_number', 'like', "%$k%"))
            ->filterColumn('join_date', fn($q, $k) => $q->where('employees_tables.join_date', 'like', "%$k%"))
            ->filterColumn('end_date', fn($q, $k) => $q->where('employees_tables.end_date', 'like', "%$k%"))
            ->filterColumn('marriage', fn($q, $k) => $q->where('employees_tables.marriage', 'like', "%$k%"))
            ->filterColumn('child', fn($q, $k) => $q->where('employees_tables.child', 'like', "%$k%"))
            ->filterColumn('telp_number', fn($q, $k) => $q->where('employees_tables.telp_number', 'like', "%$k%"))
            ->filterColumn('nik', fn($q, $k) => $q->where('employees_tables.nik', 'like', "%$k%"))
            ->filterColumn('gender', fn($q, $k) => $q->where('employees_tables.gender', 'like', "%$k%"))
            ->filterColumn('date_of_birth', fn($q, $k) => $q->where('employees_tables.date_of_birth', 'like', "%$k%"))
            ->filterColumn('place_of_birth', fn($q, $k) => $q->where('employees_tables.place_of_birth', 'like', "%$k%"))
            ->filterColumn('biological_mother_name', fn($q, $k) => $q->where('employees_tables.biological_mother_name', 'like', "%$k%"))
            ->filterColumn('religion', fn($q, $k) => $q->where('employees_tables.religion', 'like', "%$k%"))
            ->filterColumn('current_address', fn($q, $k) => $q->where('employees_tables.current_address', 'like', "%$k%"))
            ->filterColumn('id_card_address', fn($q, $k) => $q->where('employees_tables.id_card_address', 'like', "%$k%"))
            ->filterColumn('last_education', fn($q, $k) => $q->where('employees_tables.last_education', 'like', "%$k%"))
            ->filterColumn('institution', fn($q, $k) => $q->where('employees_tables.institution', 'like', "%$k%"))
            ->filterColumn('npwp', fn($q, $k) => $q->where('employees_tables.npwp', 'like', "%$k%"))
            ->filterColumn('bpjs_kes', fn($q, $k) => $q->where('employees_tables.bpjs_kes', 'like', "%$k%"))
            ->filterColumn('bpjs_ket', fn($q, $k) => $q->where('employees_tables.bpjs_ket', 'like', "%$k%"))
            ->filterColumn('email', fn($q, $k) => $q->where('employees_tables.email', 'like', "%$k%"))
            ->filterColumn('company_email', fn($q, $k) => $q->where('employees_tables.company_email', 'like', "%$k%"))
            ->filterColumn('emergency_contact_name', fn($q, $k) => $q->where('employees_tables.emergency_contact_name', 'like', "%$k%"))
            ->filterColumn('pin', fn($q, $k) => $q->where('employees_tables.pin', 'like', "%$k%"))
            ->filterColumn('pending_email', fn($q, $k) => $q->where('employees_tables.pending_email', 'like', "%$k%"))
            ->filterColumn('pending_telp_number', fn($q, $k) => $q->where('employees_tables.pending_telp_number', 'like', "%$k%"))
            ->filterColumn('position_name', fn($q, $k) => $q->where('position_tables.name', 'like', "%$k%"))
            ->filterColumn('bank_name', fn($q, $k) => $q->where('banks_tables.name', 'like', "%$k%"))
            ->filterColumn('remark', fn($q, $k) => $q->where('groups_tables.remark', 'like', "%$k%"))
            ->filterColumn('department_name', fn($q, $k) => $q->where('departments_tables.department_name', 'like', "%$k%"))
            ->filterColumn('name', fn($q, $k) => $q->where('stores_tables.name', 'like', "%$k%"))
            ->filterColumn('name_company', fn($q, $k) => $q->where('company_tables.name', 'like', "%$k%"))
            ->filterColumn('grading_name', fn($q, $k) => $q->where('grading.grading_name', 'like', "%$k%"))
            ->filterColumn('status_employee', fn($q, $k) => $q->where('employees_tables.status_employee', 'like', "%$k%"))
            ->filterColumn('status', fn($q, $k) => $q->where('employees_tables.status', 'like', "%$k%"))
            ->editColumn('created_at', function ($e) {
                return optional($e->created_at)
                    ->timezone('Asia/Makassar')
                    ->translatedFormat('d F Y H:i');
            })

            ->editColumn('join_date', function ($e) {
                return $e->join_date
                    ? Carbon::parse($e->join_date)
                    ->timezone('Asia/Makassar')
                    ->translatedFormat('d F Y')
                    : '-';
            })
            ->editColumn('end_date', function ($e) {
                return $e->end_date
                    ? Carbon::parse($e->end_date)
                    ->timezone('Asia/Makassar')
                    ->translatedFormat('d F Y')
                    : '-';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    public function exportEmployeesall(Request $request)
    {
        $filters = [
            'filter_company'    => $request->query('filter_company'),
            'filter_department' => $request->query('filter_department'),
            'filter_group'      => $request->query('filter_group'),
            'filter_grading'    => $request->query('filter_grading'),
            'filter_store'      => $request->query('filter_store'),
            'filter_emp_status' => $request->query('filter_emp_status'),
            'filter_status'     => $request->query('filter_status'),
            'filter_los'        => $request->query('filter_los'),
            'filter_bank'        => $request->query('filter_bank'),
            'filter_gender'        => $request->query('filter_gender'),
            'filter_marriage'        => $request->query('filter_marriage'),
            'filter_religion'        => $request->query('filter_religion'),
            'filter_last_education'        => $request->query('filter_last_education'),
        ];
        // dd($filters); // cek dulu, hapus setelah confirmed

        $fileName = 'employeesall_' . Carbon::now()->format('Ymd_His');

        if ($request->query('type') === 'csv') {
            return Excel::download(new EmployeesExportall($filters), $fileName . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download(new EmployeesExportall($filters), $fileName . '.xlsx');
    }
    public function getEmployees(Request $request)
    {
        $isHeadHR = auth()->user()->hasAnyRole(['HeadHR', 'HR', 'Admin']);

        $query = User::query()
            ->with([
                'employee',
                'employee.position',
                'employee.department',
                'employee.grading',
                'employee.group',
                'employee.store',
                'employee.company'
            ])
            ->leftJoin('employees_tables', 'users.employee_id', '=', 'employees_tables.id')
            ->leftJoin('position_tables', 'position_tables.id', '=', 'employees_tables.position_id')
            ->leftJoin('stores_tables', 'stores_tables.id', '=', 'employees_tables.store_id')
            ->leftJoin('departments_tables', 'departments_tables.id', '=', 'employees_tables.department_id')
            ->leftJoin('grading', 'grading.id', '=', 'employees_tables.grading_id')
            ->leftJoin('groups_tables', 'groups_tables.id', '=', 'employees_tables.group_id')
            ->leftJoin('company_tables', 'company_tables.id', '=', 'employees_tables.company_id')

            ->select([
                'users.*',
                'employees_tables.employee_name',
                'employees_tables.employee_pengenal',
                'employees_tables.status_employee',
                'employees_tables.status',
                'employees_tables.join_date',
                'position_tables.name as position_name',
                'groups_tables.remark as remark',
                'stores_tables.name as name',
                'departments_tables.department_name',
                'grading.grading_name',
                'company_tables.name as name_company',
            ]);
        // Filter tetap pakai whereHas atau bisa pakai where langsung karena sudah di-join
        $query->when(
            $request->filled('filter_company'),
            fn($q) =>
            $q->where('company_tables.name', $request->filter_company)
        );

        $query->when(
            $request->filled('filter_department'),
            fn($q) =>
            $q->where('departments_tables.department_name', $request->filter_department)
        );
        $query->when(
            $request->filled('filter_group'),
            fn($q) =>
            $q->where('groups_tables.remark', $request->filter_group)
        );
        $query->when(
            $request->filled('filter_grading'),
            fn($q) =>
            $q->where('grading.grading_name', $request->filter_grading)
        );
        $query->when(
            $request->filled('filter_store'),
            fn($q) =>
            $q->where('stores_tables.name', $request->filter_store)
        );
        $query->when(
            $request->filled('filter_emp_status'),
            fn($q) =>
            $q->where('employees_tables.status_employee', $request->filter_emp_status)
        );
        $query->when(
            $request->filled('filter_status'),
            fn($q) =>
            $q->where('employees_tables.status', $request->filter_status)
        );
        $query->when($request->filled('filter_los'), function ($q) use ($request) {
            $los = $request->filter_los;

            if ($los === 'under3months') {
                // Khusus kurang dari 3 bulan, operator berbeda
                $q->where('employees_tables.join_date', '>=', Carbon::now()->subMonths(3));
            } else {
                $date = match ($los) {
                    '1year'  => Carbon::now()->subYear(),
                    '3years' => Carbon::now()->subYears(3),
                    '5years' => Carbon::now()->subYears(5),
                    default  => null,
                };
                if ($date) {
                    $q->where('employees_tables.join_date', '<=', $date);
                }
            }
        });
        return DataTables::of($query)
            ->addColumn('length_of_service', function ($e) {
                if (!$e->join_date) return 'Empty';
                $diff = Carbon::parse($e->join_date)->diff(Carbon::now());
                return sprintf('%d year %d month %d days', $diff->y, $diff->m, $diff->d);
            })
            ->addColumn('action', function ($e) use ($isHeadHR) {
                if (!$isHeadHR) return '';
                $id_hashed = substr(hash('sha256', $e->id . env('APP_KEY')), 0, 8);
                return '
                <a href="' . route('Employee.edit', $id_hashed) . '" class="mx-2">
                    <i class="fas fa-user-edit text-secondary"></i>
                </a>
                <a href="' . route('Employee.show', $id_hashed) . '" class="mx-2">
                    <i class="fas fa-eye text-secondary"></i>
                </a>';
            })
            // Daftarkan kolom yang bisa di-search
            ->filterColumn('employee_name', fn($q, $k) => $q->where('employees_tables.employee_name', 'like', "%$k%"))
            ->filterColumn('employee_pengenal', fn($q, $k) => $q->where('employees_tables.employee_pengenal', 'like', "%$k%"))
            ->filterColumn('position_name', fn($q, $k) => $q->where('position_tables.name', 'like', "%$k%"))
            ->filterColumn('remark', fn($q, $k) => $q->where('groups_tables.remark', 'like', "%$k%"))
            ->filterColumn('department_name', fn($q, $k) => $q->where('departments_tables.department_name', 'like', "%$k%"))
            ->filterColumn('name', fn($q, $k) => $q->where('stores_tables.name', 'like', "%$k%"))
            ->filterColumn('name_company', fn($q, $k) => $q->where('company_tables.name', 'like', "%$k%"))
            ->filterColumn('grading_name', fn($q, $k) => $q->where('grading.grading_name', 'like', "%$k%"))
            ->filterColumn('status_employee', fn($q, $k) => $q->where('employees_tables.status_employee', 'like', "%$k%"))
            ->filterColumn('status', fn($q, $k) => $q->where('employees_tables.status', 'like', "%$k%"))
            ->rawColumns(['action'])
            ->make(true);
    }

    public function exportEmployees(Request $request)
    {
        // ❌ Masalah - only() kadang tidak baca query string
        // $filters = $request->only([...]);

        // ✅ Ambil manual dari query string
        $filters = [
            'filter_company'    => $request->query('filter_company'),
            'filter_department' => $request->query('filter_department'),
            'filter_group'      => $request->query('filter_group'),
            'filter_grading'    => $request->query('filter_grading'),
            'filter_store'      => $request->query('filter_store'),
            'filter_emp_status' => $request->query('filter_emp_status'),
            'filter_status'     => $request->query('filter_status'),
            'filter_los'        => $request->query('filter_los'),
        ];

        // dd($filters); // cek dulu, hapus setelah confirmed

        $fileName = 'employees_' . Carbon::now()->format('Ymd_His');

        if ($request->query('type') === 'csv') {
            return Excel::download(new EmployeesExport($filters), $fileName . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download(new EmployeesExport($filters), $fileName . '.xlsx');
    }

    public function edit($hashedId)
    {
        $employee = User::with('Employee', 'Employee.store', 'Employee.department', 'Employee.position', 'Employee.bank', 'Employee.grading', 'Employee.group', 'Employee.employees', 'Employee.structuresnew')->get()->first(function ($u) use ($hashedId) {
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
        $status = ['Pending', 'On Leave', 'Mutation', 'Active', 'Resign'];
        $banks = Banks::get();
        $usedStructureIds = Employee::whereNotNull('structure_id')->pluck('structure_id')->toArray();

        $structures = Structuresnew::with('submissionposition')
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
        $structures = Structuresnew::with('submissionposition')
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
        $status = ['Pending', 'On Leave', 'Mutation', 'Active', 'Resign'];
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
            'isManager'
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
    $file     = $request->file('photos');
    $safeName = Str::slug($request->employee_name);
    $fileName = $safeName . '.' . $file->getClientOriginalExtension();
    $folder   = 'employees-photos';

    $filePath = $file->storeAs($folder, $fileName, 'local');
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


            if ($filePath && Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
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

                // if ($request->hasFile('photos')) {
                //     $file = $request->file('photos');

                //     $fileName = hash('sha256', $file->getClientOriginalName() . time()) . '.' .
                //         $file->getClientOriginalExtension();

                //     $folderPath = 'employeesphotos/' . date('Y/m');

                //     Storage::disk('public')->putFileAs($folderPath, $file, $fileName);

                //     $newFilePath = $folderPath . '/' . $fileName;

                //     if ($filePath && Storage::disk('public')->exists($filePath)) {
                //         Storage::disk('public')->delete($filePath);
                //     }

                //     $filePath = $validatedData['photos'] = $newFilePath;
                // }
                if ($request->hasFile('photos')) {
    $file     = $request->file('photos');
    $safeName = Str::slug($request->employee_name);
    $fileName = $safeName . '.' . $file->getClientOriginalExtension();
    $folder   = 'employees-photos';

    // Hapus foto lama
    if ($filePath && Storage::disk('local')->exists($filePath)) {
        Storage::disk('local')->delete($filePath);
    }

    // Upload baru
    $filePath = $file->storeAs($folder, $fileName, 'local');
    $validatedData['photos'] = $filePath;
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
