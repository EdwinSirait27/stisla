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
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Rules\NoXSSInput;
use Carbon\Carbon;
use App\Models\Documents;
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
    private function isSPVOnly(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();
        return $user->hasPermissionTo('ManageEmployeeSPVManager')
            && !$user->hasPermissionTo('ManageEmployee');
    }

    public function indexall()
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        if (!$user->hasPermissionTo('ManageEmployee')) {
        abort(403);
    }
        $countactives = Employee::where('status', 'Active')->count();
        $countpendings = Employee::where('status', 'Pending')->count();
        $countresigns = Employee::where('status', 'Resign')->count();
        $gradings = Grading::pluck('grading_name', 'id');
        $atasans = Employee::pluck('employee_name', 'id');
        $groups = Groups::pluck('remark', 'id');
        $companies = Company::pluck('name', 'id');
        $employeestatuses = Employee::getStatusEmployeeOptions();
        $bloodtypes = Employee::getBloodTypeOptions();
        $statuses = Employee::getStatusOptions();
        $banks = Banks::pluck('name', 'id');
        $genders = Employee::getGenderOptions();
        $marriages = Employee::getMarriageOptions();
        $religions = Employee::getReligionOptions();
        $lasteducations = Employee::getLastEducationOptions();
        $stores = Stores::get();
        $departments = Departments::get();
        return view('pages.Employeeall.Employeeall', compact('atasans', 'bloodtypes', 'marriages', 'genders', 'lasteducations', 'religions', 'banks', 'departments', 'companies', 'stores', 'employeestatuses', 'statuses', 'countactives', 'countpendings', 'countresigns', 'groups', 'gradings'));
    }

    // public function index()
    // {
    //     $countactives = Employee::where('status', 'Active')->count();
    //     $countpendings = Employee::where('status', 'Pending')->count();
    //     $countresigns = Employee::where('status', 'Resign')->count();
    //     $gradings = Grading::pluck('grading_name', 'id');
    //     $groups = Groups::pluck('remark', 'id');
    //     $companies = Company::pluck('name', 'id');
    //     $stores = Stores::get();
    //     $departments = Departments::get();
    //     $employeestatuses = Employee::getStatusEmployeeOptions();
    //     $statuses = Employee::getStatusOptions();
    //     return view('pages.Employee.Employee', compact('departments', 'companies', 'stores', 'employeestatuses', 'statuses', 'countactives', 'countpendings', 'countresigns', 'groups', 'gradings'));
    // }
    public function index()
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();

    // ViewEmployee: hanya bisa akses route, tidak ada data tambahan
    if ($user->hasPermissionTo('ViewEmployee') 
        && !$user->hasPermissionTo('ManageEmployee') 
        && !$user->hasPermissionTo('ManageEmployeeSPVManager')) {
        return view('pages.Employee.Employee');
    }

    // Data yang bisa diakses semua role (ManageEmployee & ManageEmployeeSPVManager)
    $countactives = Employee::where('status', 'Active')->count();
    $countpendings = Employee::where('status', 'Pending')->count();
    $companies = Company::pluck('name', 'id');
    $stores = Stores::get();
    $departments = Departments::get();
    $employeestatuses = Employee::getStatusEmployeeOptions();
    $statuses = Employee::getStatusOptions();

    // SPV Only: hanya data terbatas
    if ($user->hasPermissionTo('ManageEmployeeSPVManager') 
        && !$user->hasPermissionTo('ManageEmployee')) {
        return view('pages.Employee.Employee', compact(
            'countactives', 'countpendings',
            'companies', 'stores', 'departments',
            'employeestatuses', 'statuses'
        ));
    }

    // ManageEmployee: semua data
    $countresigns = Employee::where('status', 'Resign')->count();
    $gradings = Grading::pluck('grading_name', 'id');
    $groups = Groups::pluck('remark', 'id');

    return view('pages.Employee.Employee', compact(
        'countactives', 'countpendings', 'countresigns',
        'companies', 'stores', 'departments',
        'employeestatuses', 'statuses',
        'gradings', 'groups'
    ));
}
    public function getBagan(Request $request)
    {
    /** @var \App\Models\User|null $user */

        $user = auth()->user();

         if (!$user->hasPermissionTo('ManageEmployee')) {
        abort(403);
    }

        try {
            $storeId = $request->store_id;
            $departmentId = $request->department_id;
            $employees = Employee::with(['grading', 'atasanList'])
                ->when(
                    $storeId && $storeId !== 'all',
                    fn($q) => $q->whereHas('store', fn($q) => $q->where('stores_tables.id', $storeId))
                )
                ->when(
                    $departmentId && $departmentId !== 'all',
                    fn($q) => $q->whereHas('department', fn($q) => $q->where('departments_tables.id', $departmentId))
                )
                ->whereHas('grading')
                ->whereIn('status', ['Active', 'Pending'])
                ->get()
                ->sortBy('grading.level');

            Log::info('getBagan debug', [
                'store_id'     => $storeId,
                'department_id' => $departmentId,
                'count'        => $employees->count(),
            ]);

            $bagan = $employees->map(function ($employee) {
                try {
                    $atasan = $employee->atasan();

                    $photoFilename = $employee->photos
                        ? basename($employee->photos)
                        : null;

                    return [
                        'id'            => $employee->id,
                        'company_name'          => $employee->company->name,
                        'name'          => $employee->employee_name,
                        'position'      => $employee->primaryPosition()->first()?->name ?? '-',
                        'grading'       => $employee->grading?->grading_name ?? '-',
                        'grading_level' => $employee->grading?->level ?? 0,
                        'photo'         => $photoFilename
                            ? route('employee.serve.photo', ['filename' => $photoFilename])
                            : null,
                        // 'atasan_id'     => $atasan?->id ?? null,
                        'atasan_id' => $employee->atasanStruktur()?->id ?? null,

                        'all_positions'   => $employee->position->pluck('name')->join(', '),
                        'all_stores'      => $employee->store->pluck('name')->join(', '),
                        'all_departments' => $employee->department->pluck('department_name')->join(', '),
                    ];
                } catch (\Throwable $e) {
                    Log::error('map error employee ' . $employee->id, [
                        'error' => $e->getMessage(),
                        'line'  => $e->getLine(),
                    ]);
                    return null;
                }
            })->filter()->values();
            return response()->json(['nodes' => $bagan]); // ← ini yang hilang

        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ], 500);
        }
    }
    public function getActivities(Request $request)
    {
       /** @var \App\Models\User|null $user */
    $user = auth()->user();
         if (!$user->hasPermissionTo('ManageEmployee')) {
        abort(403);
    }
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
        /** @var \App\Models\User|null $user */
    $user = auth()->user();
         if (!$user->hasPermissionTo('ManageEmployee')) {
        abort(403);
    }
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
            ->leftJoin('grading', 'grading.id', '=', 'employees_tables.grading_id')
            ->leftJoin('groups_tables', 'groups_tables.id', '=', 'employees_tables.group_id')
            ->leftJoin('company_tables', 'company_tables.id', '=', 'employees_tables.company_id')
            ->leftJoin('banks_tables', 'banks_tables.id', '=', 'employees_tables.banks_id')

            ->leftJoin('employee_stores', function ($join) {
                $join->on('employee_stores.employee_id', '=', 'employees_tables.id')
                    ->where('employee_stores.is_primary', true);
            })
            ->leftJoin('stores_tables', 'stores_tables.id', '=', 'employee_stores.store_id')
            ->leftJoin('employee_positions', function ($join) {
                $join->on('employee_positions.employee_id', '=', 'employees_tables.id')
                    ->where('employee_positions.is_primary', true);
            })
            ->leftJoin('position_tables', 'position_tables.id', '=', 'employee_positions.position_id')

            // ← Join pivot + tabel departments untuk primary department
            ->leftJoin('employee_departments', function ($join) {
                $join->on('employee_departments.employee_id', '=', 'employees_tables.id')
                    ->where('employee_departments.is_primary', true);
            })
            ->leftJoin('departments_tables', 'departments_tables.id', '=', 'employee_departments.department_id')

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
                'employees_tables.blood_type',
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
                'employees_tables.can_approve',
                'employees_tables.pending_email',
                'employees_tables.pending_telp_number',
                'employees_tables.status_employee',
                'employees_tables.status',
                'employees_tables.join_date',
                'groups_tables.remark as remark',
                'departments_tables.department_name',
                'stores_tables.name as store_name',
                'position_tables.name as position_name',
                'banks_tables.name as bank_name',
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
            $request->filled('filter_blood_type'),
            fn($q) =>
            $q->where('employees_tables.blood_type', $request->filter_blood_type)
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
        $query->when(
            $request->filled('filter_join_date_from'),
            fn($q) =>
            $q->where('employees_tables.join_date', '>=', Carbon::parse($request->filter_join_date_from)->startOfDay())
        );
        $query->when(
            $request->filled('filter_join_date_to'),
            fn($q) =>
            $q->where('employees_tables.join_date', '<=', Carbon::parse($request->filter_join_date_to)->endOfDay())
        );

        $query->when(
            $request->filled('filter_end_date_from'),
            fn($q) =>
            $q->where('employees_tables.end_date', '>=', Carbon::parse($request->filter_end_date_from)->startOfDay())
        );
        $query->when(
            $request->filled('filter_end_date_to'),
            fn($q) =>
            $q->where('employees_tables.end_date', '<=', Carbon::parse($request->filter_end_date_to)->endOfDay())
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
            // ->addColumn('action', function ($e) use ($isHeadHR) {
            //     if (!$isHeadHR) return '';
            //     $id_hashed = substr(hash('sha256', $e->id . env('APP_KEY')), 0, 8);
            //     return '
            //     <a href="' . route('Employee.edit', $id_hashed) . '" class="mx-2">
            //         <i class="fas fa-user-edit text-secondary"></i>
            //     </a>
            //     <a href="' . route('Employee.show', $id_hashed) . '" class="mx-2">
            //         <i class="fas fa-eye text-secondary"></i>
            //     </a>';
            // })
            ->addColumn('action', function ($e) {
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
            ->filterColumn('blood_type', fn($q, $k) => $q->where('employees_tables.blood_type', 'like', "%$k%"))
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
            ->filterColumn('can_approve', fn($q, $k) => $q->where('employees_tables.can_approve', 'like', "%$k%"))
            ->filterColumn('pending_email', fn($q, $k) => $q->where('employees_tables.pending_email', 'like', "%$k%"))
            ->filterColumn('pending_telp_number', fn($q, $k) => $q->where('employees_tables.pending_telp_number', 'like', "%$k%"))
            ->filterColumn('position_name', fn($q, $k) => $q->where('position_tables.name', 'like', "%$k%"))
            ->filterColumn('bank_name', fn($q, $k) => $q->where('banks_tables.name', 'like', "%$k%"))
            ->filterColumn('remark', fn($q, $k) => $q->where('groups_tables.remark', 'like', "%$k%"))
            ->filterColumn('department_name', fn($q, $k) => $q->where('departments_tables.department_name', 'like', "%$k%"))
            ->filterColumn('store_name', fn($q, $k) => $q->where('stores_tables.name', 'like', "%$k%"))
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
        /** @var \App\Models\User|null $user */
    $user = auth()->user();
         if (!$user->hasPermissionTo('ManageEmployee')) {
        abort(403);
    }
        $filters = [
            'filter_company'    => $request->query('filter_company'),
            'filter_department' => $request->query('filter_department'),
            'filter_group'      => $request->query('filter_group'),
            'filter_grading'    => $request->query('filter_grading'),
            'filter_store'      => $request->query('filter_store'),
            'filter_emp_status' => $request->query('filter_emp_status'),
            'filter_blood_type' => $request->query('filter_blood_type'),
            'filter_status'     => $request->query('filter_status'),
            'filter_los'        => $request->query('filter_los'),
            'filter_bank'        => $request->query('filter_bank'),
            'filter_gender'        => $request->query('filter_gender'),
            'filter_marriage'        => $request->query('filter_marriage'),
            'filter_religion'        => $request->query('filter_religion'),
            'filter_last_education'        => $request->query('filter_last_education'),
            'filter_join_date_from' => $request->filter_join_date_from, // ← tambahkan
            'filter_join_date_to'   => $request->filter_join_date_to,   // ← tambahkan
            'filter_end_date_from'  => $request->filter_end_date_from,  // ← tambahkan
            'filter_end_date_to'    => $request->filter_end_date_to,    // ← tambahkan
        ];
        // dd($filters); // cek dulu, hapus setelah confirmed

        $fileName = 'employeesall_' . Carbon::now()->format('Ymd_His');

        if ($request->query('type') === 'csv') {
            return Excel::download(new EmployeesExportall($filters), $fileName . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download(new EmployeesExportall($filters), $fileName . '.xlsx');
    }

//     public function getEmployees(Request $request)
//     {
//         /** @var \App\Models\User|null $user */
//    $user = auth()->user();
//          if (!$user->hasPermissionTo('ManageEmployee')) {
//         abort(403);
//     }    

//         $query = User::query()
//             ->with([
//                 'employee',
//                 'employee.position',
//                 'employee.department',
//                 'employee.grading',
//                 'employee.group',
//                 'employee.store',
//                 'employee.company'
//             ])
//             ->leftJoin('employees_tables', 'users.employee_id', '=', 'employees_tables.id')
//             ->leftJoin('grading', 'grading.id', '=', 'employees_tables.grading_id')
//             ->leftJoin('groups_tables', 'groups_tables.id', '=', 'employees_tables.group_id')
//             ->leftJoin('company_tables', 'company_tables.id', '=', 'employees_tables.company_id')

//             // ← Join pivot + tabel stores untuk primary store
//             ->leftJoin('employee_stores', function ($join) {
//                 $join->on('employee_stores.employee_id', '=', 'employees_tables.id')
//                     ->where('employee_stores.is_primary', true);
//             })
//             ->leftJoin('stores_tables', 'stores_tables.id', '=', 'employee_stores.store_id')
//             ->leftJoin('employee_positions', function ($join) {
//                 $join->on('employee_positions.employee_id', '=', 'employees_tables.id')
//                     ->where('employee_positions.is_primary', true);
//             })
//             ->leftJoin('position_tables', 'position_tables.id', '=', 'employee_positions.position_id')

//             // ← Join pivot + tabel departments untuk primary department
//             ->leftJoin('employee_departments', function ($join) {
//                 $join->on('employee_departments.employee_id', '=', 'employees_tables.id')
//                     ->where('employee_departments.is_primary', true);
//             })
//             ->leftJoin('departments_tables', 'departments_tables.id', '=', 'employee_departments.department_id')

//             ->select([
//                 'users.*',
//                 'employees_tables.employee_name',
//                 'employees_tables.employee_pengenal',
//                 'employees_tables.status_employee',
//                 'employees_tables.status',
//                 'employees_tables.join_date',
//                 'position_tables.name as position_name',
//                 'groups_tables.remark as remark',
//                 'stores_tables.name as store_name',           
//                 'departments_tables.department_name',
//                 'grading.grading_name',
//                 'company_tables.name as name_company',
//             ]);

//         $query->when(
//             $request->filled('filter_company'),
//             fn($q) => $q->where('company_tables.name', $request->filter_company)
//         );
//         $query->when(
//             $request->filled('filter_department'),
//             fn($q) => $q->where('departments_tables.department_name', $request->filter_department)
//         );
//         $query->when(
//             $request->filled('filter_group'),
//             fn($q) => $q->where('groups_tables.remark', $request->filter_group)
//         );
//         $query->when(
//             $request->filled('filter_grading'),
//             fn($q) => $q->where('grading.grading_name', $request->filter_grading)
//         );
//         $query->when(
//             $request->filled('filter_store'),
//             fn($q) => $q->where('stores_tables.name', $request->filter_store)
//         );
//         $query->when(
//             $request->filled('filter_emp_status'),
//             fn($q) => $q->where('employees_tables.status_employee', $request->filter_emp_status)
//         );
//         $query->when(
//             $request->filled('filter_status'),
//             fn($q) => $q->where('employees_tables.status', $request->filter_status)
//         );
//         $query->when($request->filled('filter_los'), function ($q) use ($request) {
//             $los = $request->filter_los;
//             if ($los === 'under3months') {
//                 $q->where('employees_tables.join_date', '>=', Carbon::now()->subMonths(3));
//             } else {
//                 $date = match ($los) {
//                     '1year'  => Carbon::now()->subYear(),
//                     '3years' => Carbon::now()->subYears(3),
//                     '5years' => Carbon::now()->subYears(5),
//                     default  => null,
//                 };
//                 if ($date) {
//                     $q->where('employees_tables.join_date', '<=', $date);
//                 }
//             }
//         });

//         return DataTables::of($query)
//             ->addColumn('length_of_service', function ($e) {
//                 if (!$e->join_date) return 'Empty';
//                 $diff = Carbon::parse($e->join_date)->diff(Carbon::now());
//                 return sprintf('%d year %d month %d days', $diff->y, $diff->m, $diff->d);
//             })
           
//                ->addColumn('action', function ($e) {
//     $id_hashed = substr(hash('sha256', $e->id . env('APP_KEY')), 0, 8);
//     return '
//     <a href="' . route('Employee.edit', $id_hashed) . '" class="mx-2">
//         <i class="fas fa-user-edit text-secondary"></i>
//     </a>
//     <a href="' . route('Employee.show', $id_hashed) . '" class="mx-2">
//         <i class="fas fa-eye text-secondary"></i>
//     </a>';
// })

//             ->filterColumn('employee_name', fn($q, $k) => $q->where('employees_tables.employee_name', 'like', "%$k%"))
//             ->filterColumn('employee_pengenal', fn($q, $k) => $q->where('employees_tables.employee_pengenal', 'like', "%$k%"))
//             ->filterColumn('position_name', fn($q, $k) => $q->where('position_tables.name', 'like', "%$k%"))
//             ->filterColumn('remark', fn($q, $k) => $q->where('groups_tables.remark', 'like', "%$k%"))
//             ->filterColumn('department_name', fn($q, $k) => $q->where('departments_tables.department_name', 'like', "%$k%"))
//             ->filterColumn('store_name', fn($q, $k) => $q->where('stores_tables.name', 'like', "%$k%")) // ← ganti alias
//             ->filterColumn('name_company', fn($q, $k) => $q->where('company_tables.name', 'like', "%$k%"))
//             ->filterColumn('grading_name', fn($q, $k) => $q->where('grading.grading_name', 'like', "%$k%"))
//             ->filterColumn('status_employee', fn($q, $k) => $q->where('employees_tables.status_employee', 'like', "%$k%"))
//             ->filterColumn('status', fn($q, $k) => $q->where('employees_tables.status', 'like', "%$k%"))
//             ->rawColumns(['action'])
//             ->make(true);
//     }
public function getEmployees(Request $request)
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();

    $canManage     = $user->hasPermissionTo('ManageEmployee');
    $canSpvManager = $user->hasPermissionTo('ManageEmployeeSPVManager');
    $canView       = $user->hasPermissionTo('ViewEmployee');

    if (!$canManage && !$canSpvManager && !$canView) {
        abort(403);
    }

    // ← with berbeda berdasarkan permission
    $withRelations = $canManage ? [
        'employee',
        'employee.position' => fn($q) => $q->wherePivot('is_primary', true),
        'employee.department' => fn($q) => $q->wherePivot('is_primary', true),
        'employee.grading',
        'employee.group',
        'employee.documents.companydocumentconfigs.documenttypes',
        'employee.store' => fn($q) => $q->wherePivot('is_primary', true),
        'employee.company'
    ] : [
        'employee' => fn($q) => $q->select('id', 'employee_name', 'employee_pengenal', 'status', 'company_id'),
    'employee.position' => fn($q) => $q->wherePivot('is_primary', true)->select('position_tables.id', 'position_tables.name'),
    'employee.department' => fn($q) => $q->wherePivot('is_primary', true)->select('departments_tables.id', 'departments_tables.department_name'),
    'employee.store' => fn($q) => $q->wherePivot('is_primary', true)->select('stores_tables.id', 'stores_tables.name'),
    'employee.company' => fn($q) => $q->select('id', 'name'),
    ];

    $query = User::query()
        ->with($withRelations)
        ->leftJoin('employees_tables', 'users.employee_id', '=', 'employees_tables.id')
        ->leftJoin('grading', 'grading.id', '=', 'employees_tables.grading_id')
        ->leftJoin('groups_tables', 'groups_tables.id', '=', 'employees_tables.group_id')
        ->leftJoin('company_tables', 'company_tables.id', '=', 'employees_tables.company_id')
        ->leftJoin('employee_stores', function ($join) {
            $join->on('employee_stores.employee_id', '=', 'employees_tables.id')
                ->where('employee_stores.is_primary', true);
        })
        ->leftJoin('stores_tables', 'stores_tables.id', '=', 'employee_stores.store_id')
        ->leftJoin('employee_positions', function ($join) {
            $join->on('employee_positions.employee_id', '=', 'employees_tables.id')
                ->where('employee_positions.is_primary', true);
        })
        ->leftJoin('position_tables', 'position_tables.id', '=', 'employee_positions.position_id')
        ->leftJoin('employee_departments', function ($join) {
            $join->on('employee_departments.employee_id', '=', 'employees_tables.id')
                ->where('employee_departments.is_primary', true);
        })
        ->leftJoin('departments_tables', 'departments_tables.id', '=', 'employee_departments.department_id');

    if ($canManage) {
        $query->select([
            'users.*',
            'employees_tables.employee_name',
            'employees_tables.employee_pengenal',
            'employees_tables.status_employee',
            'employees_tables.status',
            'employees_tables.join_date',
            'position_tables.name as position_name',
            'groups_tables.remark as remark',
            'stores_tables.name as store_name',
            'departments_tables.department_name',
            'grading.grading_name',
            'company_tables.name as name_company',
        ]);
    } else {
        $query->select([
            'users.id',
            'users.employee_id',
            'employees_tables.employee_name',
            'employees_tables.employee_pengenal',
            'employees_tables.status',
            'position_tables.name as position_name',
            'stores_tables.name as store_name',
            'departments_tables.department_name',
            'company_tables.name as name_company',
        ]);
    }
if (!$canManage) {
    $employee = $user->employee;
    $companyId = $employee->company_id; // ← ambil company_id employee yang login

    // ← Filter wajib berdasarkan company_id
    $query->where('employees_tables.company_id', $companyId);

    if ($canSpvManager) {
        $storeIds = $employee->store()->pluck('stores_tables.id')->toArray();
        $departmentIds = $employee->department()->pluck('departments_tables.id')->toArray();

        if (empty($storeIds) || empty($departmentIds)) {
            return DataTables::of(collect())->make(true);
        }

        $query->whereExists(function ($q) use ($storeIds) {
            $q->select(DB::raw(1))
                ->from('employee_stores')
                ->whereColumn('employee_stores.employee_id', 'employees_tables.id')
                ->whereIn('employee_stores.store_id', $storeIds);
        })
        ->whereExists(function ($q) use ($departmentIds) {
            $q->select(DB::raw(1))
                ->from('employee_departments')
                ->whereColumn('employee_departments.employee_id', 'employees_tables.id')
                ->whereIn('employee_departments.department_id', $departmentIds);
        });

    } elseif ($canView) {
        $storeId = $employee->primaryStore()->first()?->id;
        $departmentId = $employee->primaryDepartment()->first()?->id;

        if (!$storeId || !$departmentId) {
            return DataTables::of(collect())->make(true);
        }

        $query->whereExists(function ($q) use ($storeId) {
            $q->select(DB::raw(1))
                ->from('employee_stores')
                ->whereColumn('employee_stores.employee_id', 'employees_tables.id')
                ->where('employee_stores.store_id', $storeId)
                ->where('employee_stores.is_primary', true);
        })
        ->whereExists(function ($q) use ($departmentId) {
            $q->select(DB::raw(1))
                ->from('employee_departments')
                ->whereColumn('employee_departments.employee_id', 'employees_tables.id')
                ->where('employee_departments.department_id', $departmentId)
                ->where('employee_departments.is_primary', true);
        });
    }
}
    // if (!$canManage) {
    //     $employee = $user->employee;

    //     if ($canSpvManager) {
    //         $storeIds = $employee->store()->pluck('stores_tables.id')->toArray();
    //         $departmentIds = $employee->department()->pluck('departments_tables.id')->toArray();

    //         if (empty($storeIds) || empty($departmentIds)) {
    //             return DataTables::of(collect())->make(true);
    //         }

    //         $query->whereExists(function ($q) use ($storeIds) {
    //             $q->select(DB::raw(1))
    //                 ->from('employee_stores')
    //                 ->whereColumn('employee_stores.employee_id', 'employees_tables.id')
    //                 ->whereIn('employee_stores.store_id', $storeIds);
    //         })
    //         ->whereExists(function ($q) use ($departmentIds) {
    //             $q->select(DB::raw(1))
    //                 ->from('employee_departments')
    //                 ->whereColumn('employee_departments.employee_id', 'employees_tables.id')
    //                 ->whereIn('employee_departments.department_id', $departmentIds);
    //         });

    //     } elseif ($canView) {
    //         $storeId = $employee->primaryStore()->first()?->id;
    //         $departmentId = $employee->primaryDepartment()->first()?->id;

    //         if (!$storeId || !$departmentId) {
    //             return DataTables::of(collect())->make(true);
    //         }

    //         $query->whereExists(function ($q) use ($storeId) {
    //             $q->select(DB::raw(1))
    //                 ->from('employee_stores')
    //                 ->whereColumn('employee_stores.employee_id', 'employees_tables.id')
    //                 ->where('employee_stores.store_id', $storeId)
    //                 ->where('employee_stores.is_primary', true);
    //         })
    //         ->whereExists(function ($q) use ($departmentId) {
    //             $q->select(DB::raw(1))
    //                 ->from('employee_departments')
    //                 ->whereColumn('employee_departments.employee_id', 'employees_tables.id')
    //                 ->where('employee_departments.department_id', $departmentId)
    //                 ->where('employee_departments.is_primary', true);
    //         });
    //     }
    // }

    if ($canManage) {
        $query->when($request->filled('filter_group'), fn($q) => $q->where('groups_tables.remark', $request->filter_group));
        $query->when($request->filled('filter_grading'), fn($q) => $q->where('grading.grading_name', $request->filter_grading));
        $query->when($request->filled('filter_emp_status'), fn($q) => $q->where('employees_tables.status_employee', $request->filter_emp_status));
        $query->when($request->filled('filter_los'), function ($q) use ($request) {
            $los = $request->filter_los;
            if ($los === 'under3months') {
                $q->where('employees_tables.join_date', '>=', Carbon::now()->subMonths(3));
            } else {
                $date = match ($los) {
                    '1year'  => Carbon::now()->subYear(),
                    '3years' => Carbon::now()->subYears(3),
                    '5years' => Carbon::now()->subYears(5),
                    default  => null,
                };
                if ($date) $q->where('employees_tables.join_date', '<=', $date);
            }
        });
    }

    $query->when($request->filled('filter_company'), fn($q) => $q->where('company_tables.name', $request->filter_company));
    $query->when($request->filled('filter_department'), fn($q) => $q->where('departments_tables.department_name', $request->filter_department));
    $query->when($request->filled('filter_store'), fn($q) => $q->where('stores_tables.name', $request->filter_store));
    $query->when($request->filled('filter_status'), fn($q) => $q->where('employees_tables.status', $request->filter_status));

    $dt = DataTables::of($query);

    // ← Sembunyikan data sensitif dari response untuk non-ManageEmployee
    if (!$canManage) {
        $sensitiveFields = [
            'nik', 'telp_number', 'email', 'bank_account_number',
            'bpjs_kes', 'bpjs_ket', 'npwp', 'pin', 'date_of_birth',
            'place_of_birth', 'biological_mother_name', 'current_address',
            'id_card_address', 'marriage', 'child', 'gender', 'religion',
            'last_education', 'institution', 'kk_photos', 'ktp_photos',
            'signature', 'join_date', 'end_date', 'can_approve',
            'grading_id', 'banks_id', 'structure_id', 'group_id',
            'level_id', 'is_manager', 'is_manager_store', 'photos',
            'remaining', 'approved', 'pending', 'total',
            'position_id', 'store_id', 'department_id',
        ];

        $dt->addColumn('employee', function ($row) use ($sensitiveFields) {
            if (!$row->employee) return null;
            return $row->employee->makeHidden($sensitiveFields);
        });
    }

    if ($canManage) {
        $dt->addColumn('length_of_service', function ($e) {
            if (!$e->join_date) return 'Empty';
            $diff = Carbon::parse($e->join_date)->diff(Carbon::now());
            return sprintf('%d year %d month %d days', $diff->y, $diff->m, $diff->d);
        });
    }

    $dt->addColumn('action', function ($e) use ($canManage) {
        $id_hashed = substr(hash('sha256', $e->id . env('APP_KEY')), 0, 8);
        $actions = '<a href="' . route('Employee.show', $id_hashed) . '" class="mx-2">
            <i class="fas fa-eye text-secondary"></i>
        </a>';
        if ($canManage) {
            $actions .= '<a href="' . route('Employee.edit', $id_hashed) . '" class="mx-2">
                <i class="fas fa-user-edit text-secondary"></i>
            </a>';
        }
        return $actions;
    });

    $dt->filterColumn('employee_name', fn($q, $k) => $q->where('employees_tables.employee_name', 'like', "%$k%"))
        ->filterColumn('employee_pengenal', fn($q, $k) => $q->where('employees_tables.employee_pengenal', 'like', "%$k%"))
        ->filterColumn('position_name', fn($q, $k) => $q->where('position_tables.name', 'like', "%$k%"))
        ->filterColumn('department_name', fn($q, $k) => $q->where('departments_tables.department_name', 'like', "%$k%"))
        ->filterColumn('store_name', fn($q, $k) => $q->where('stores_tables.name', 'like', "%$k%"))
        ->filterColumn('name_company', fn($q, $k) => $q->where('company_tables.name', 'like', "%$k%"))
        ->filterColumn('status', fn($q, $k) => $q->where('employees_tables.status', 'like', "%$k%"));

    if ($canManage) {
        $dt->filterColumn('remark', fn($q, $k) => $q->where('groups_tables.remark', 'like', "%$k%"))
            ->filterColumn('grading_name', fn($q, $k) => $q->where('grading.grading_name', 'like', "%$k%"))
            ->filterColumn('status_employee', fn($q, $k) => $q->where('employees_tables.status_employee', 'like', "%$k%"));
    }

    return $dt->rawColumns(['action'])->make(true);
}
// public function getEmployees(Request $request)
// {
//     /** @var \App\Models\User|null $user */
//     $user = auth()->user();

//     $canManage     = $user->hasPermissionTo('ManageEmployee');
//     $canSpvManager = $user->hasPermissionTo('ManageEmployeeSPVManager');
//     $canView       = $user->hasPermissionTo('ViewEmployee');

//     if (!$canManage && !$canSpvManager && !$canView) {
//         abort(403);
//     }

//     $query = User::query()
//         ->with([
//             'employee',
//             'employee.position' => fn($q) => $q->wherePivot('is_primary', true),
//             'employee.department' => fn($q) => $q->wherePivot('is_primary', true),
//             'employee.grading',
//             'employee.group',
//             'employee.store' => fn($q) => $q->wherePivot('is_primary', true),
//             'employee.company'
//         ])
//         ->leftJoin('employees_tables', 'users.employee_id', '=', 'employees_tables.id')
//         ->leftJoin('grading', 'grading.id', '=', 'employees_tables.grading_id')
//         ->leftJoin('groups_tables', 'groups_tables.id', '=', 'employees_tables.group_id')
//         ->leftJoin('company_tables', 'company_tables.id', '=', 'employees_tables.company_id')
//         ->leftJoin('employee_stores', function ($join) {
//             $join->on('employee_stores.employee_id', '=', 'employees_tables.id')
//                 ->where('employee_stores.is_primary', true);
//         })
//         ->leftJoin('stores_tables', 'stores_tables.id', '=', 'employee_stores.store_id')
//         ->leftJoin('employee_positions', function ($join) {
//             $join->on('employee_positions.employee_id', '=', 'employees_tables.id')
//                 ->where('employee_positions.is_primary', true);
//         })
//         ->leftJoin('position_tables', 'position_tables.id', '=', 'employee_positions.position_id')
//         ->leftJoin('employee_departments', function ($join) {
//             $join->on('employee_departments.employee_id', '=', 'employees_tables.id')
//                 ->where('employee_departments.is_primary', true);
//         })
//         ->leftJoin('departments_tables', 'departments_tables.id', '=', 'employee_departments.department_id')
//         ->select([
//             'users.*',
//             'employees_tables.employee_name',
//             'employees_tables.employee_pengenal',
//             'employees_tables.status_employee',
//             'employees_tables.status',
//             'employees_tables.join_date',
//             'position_tables.name as position_name',
//             'groups_tables.remark as remark',
//             'stores_tables.name as store_name',
//             'departments_tables.department_name',
//             'grading.grading_name',
//             'company_tables.name as name_company',
//         ]);

//     // ← ManageEmployeeSPVManager: semua store dan department kepunyaan (pivot)
//     if ($canSpvManager && !$canManage) {
//         $employee = $user->employee;
//         $storeIds = $employee->store()->pluck('stores_tables.id')->toArray();
//         $departmentIds = $employee->department()->pluck('departments_tables.id')->toArray();

//         $query->whereHas('employee.store', fn($q) =>
//             $q->whereIn('stores_tables.id', $storeIds)
//         )
//         ->whereHas('employee.department', fn($q) =>
//             $q->whereIn('departments_tables.id', $departmentIds)
//         );
//     }

//     // ← ViewEmployee: hanya primary store dan department
//     if ($canView && !$canManage && !$canSpvManager) {
//         $employee = $user->employee;
//         $storeId = $employee->primaryStore()->first()?->id;
//         $departmentId = $employee->primaryDepartment()->first()?->id;

//         $query->whereHas('employee.store', fn($q) =>
//             $q->where('stores_tables.id', $storeId)
//               ->where('employee_stores.is_primary', true)
//         )
//         ->whereHas('employee.department', fn($q) =>
//             $q->where('departments_tables.id', $departmentId)
//               ->where('employee_departments.is_primary', true)
//         );
//     }

//     // Filter
//     $query->when($request->filled('filter_company'), fn($q) => $q->where('company_tables.name', $request->filter_company));
//     $query->when($request->filled('filter_department'), fn($q) => $q->where('departments_tables.department_name', $request->filter_department));
//     $query->when($request->filled('filter_group'), fn($q) => $q->where('groups_tables.remark', $request->filter_group));
//     $query->when($request->filled('filter_grading'), fn($q) => $q->where('grading.grading_name', $request->filter_grading));
//     $query->when($request->filled('filter_store'), fn($q) => $q->where('stores_tables.name', $request->filter_store));
//     $query->when($request->filled('filter_emp_status'), fn($q) => $q->where('employees_tables.status_employee', $request->filter_emp_status));
//     $query->when($request->filled('filter_status'), fn($q) => $q->where('employees_tables.status', $request->filter_status));
//     $query->when($request->filled('filter_los'), function ($q) use ($request) {
//         $los = $request->filter_los;
//         if ($los === 'under3months') {
//             $q->where('employees_tables.join_date', '>=', Carbon::now()->subMonths(3));
//         } else {
//             $date = match ($los) {
//                 '1year'  => Carbon::now()->subYear(),
//                 '3years' => Carbon::now()->subYears(3),
//                 '5years' => Carbon::now()->subYears(5),
//                 default  => null,
//             };
//             if ($date) $q->where('employees_tables.join_date', '<=', $date);
//         }
//     });

//     return DataTables::of($query)
//         ->addColumn('length_of_service', function ($e) {
//             if (!$e->join_date) return 'Empty';
//             $diff = Carbon::parse($e->join_date)->diff(Carbon::now());
//             return sprintf('%d year %d month %d days', $diff->y, $diff->m, $diff->d);
//         })
//         ->addColumn('action', function ($e) use ($canManage) {
//             $id_hashed = substr(hash('sha256', $e->id . env('APP_KEY')), 0, 8);
//             $actions = '<a href="' . route('Employee.show', $id_hashed) . '" class="mx-2">
//                 <i class="fas fa-eye text-secondary"></i>
//             </a>';
//             if ($canManage) {
//                 $actions .= '<a href="' . route('Employee.edit', $id_hashed) . '" class="mx-2">
//                     <i class="fas fa-user-edit text-secondary"></i>
//                 </a>';
//             }
//             return $actions;
//         })
//         ->filterColumn('employee_name', fn($q, $k) => $q->where('employees_tables.employee_name', 'like', "%$k%"))
//         ->filterColumn('employee_pengenal', fn($q, $k) => $q->where('employees_tables.employee_pengenal', 'like', "%$k%"))
//         ->filterColumn('position_name', fn($q, $k) => $q->where('position_tables.name', 'like', "%$k%"))
//         ->filterColumn('remark', fn($q, $k) => $q->where('groups_tables.remark', 'like', "%$k%"))
//         ->filterColumn('department_name', fn($q, $k) => $q->where('departments_tables.department_name', 'like', "%$k%"))
//         ->filterColumn('store_name', fn($q, $k) => $q->where('stores_tables.name', 'like', "%$k%"))
//         ->filterColumn('name_company', fn($q, $k) => $q->where('company_tables.name', 'like', "%$k%"))
//         ->filterColumn('grading_name', fn($q, $k) => $q->where('grading.grading_name', 'like', "%$k%"))
//         ->filterColumn('status_employee', fn($q, $k) => $q->where('employees_tables.status_employee', 'like', "%$k%"))
//         ->filterColumn('status', fn($q, $k) => $q->where('employees_tables.status', 'like', "%$k%"))
//         ->rawColumns(['action'])
//         ->make(true);
// }


    public function exportEmployees(Request $request)
    {
        /** @var \App\Models\User|null $user */
    $user = auth()->user();
         if (!$user->hasPermissionTo('ManageEmployee')) {
        abort(403);
    }
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
        /** @var \App\Models\User|null $user */
    $user = auth()->user();
         if (!$user->hasPermissionTo('ManageEmployee')) {
        abort(403);
    }
        $employee = User::with('Employee', 'Employee.store', 'Employee.department', 'Employee.position', 'Employee.bank', 'Employee.grading', 'Employee.group', 'Employee.employees')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$employee) {
            abort(404, 'Employee not found.');
        }
        $allStores = Stores::get();
        $allPositions = Position::get();
        $allDepartments = Departments::get();
        // $allEmployees = Employee::get();
        // $allEmployees = Employee::where('status', 'Active')->get();
        $allEmployees = Employee::where('status', 'Active')
            ->whereHas(
                'grading',
                fn($q) =>
                $q->where('level', '<', $employee->Employee->grading->level)
            )
            ->get();
        $selectedStores = $employee->Employee->store->pluck('id')->toArray();
        $selectedDepartments = $employee->Employee->department->pluck('id')->toArray();
        $selectedPositions = $employee->Employee->position->pluck('id')->toArray();
        $selectedAtasans = $employee->Employee->atasanList->pluck('id')->toArray();
        $primaryStoreId = $employee->Employee->store()
            ->wherePivot('is_primary', true)
            ->first()?->id;
        $primaryPositionId = $employee->Employee->position()
            ->wherePivot('is_primary', true)
            ->first()?->id;
        $primaryDepartmentId = $employee->Employee->department()
            ->wherePivot('is_primary', true)
            ->first()?->id;
        $primaryEmployeeId = $employee->Employee->atasanList()
            ->wherePivot('is_primary', true)
            ->first()?->id;
        $companys = Company::get();
        $gradings = Grading::get();
        $groups = Groups::get();
        $employees = Employee::where('status', 'Active')->pluck('employee_name', 'id');
        $status_employee = ['PKWT', 'DW', 'PKWTT', 'On Job Training'];
        $child = ['0', '1', '2', '3', '4', '5'];
        $marriage = ['Yes', 'No'];
        $bloodtypes = Employee::getBloodTypeOptions();

        $gender = ['Male', 'Female', 'MD'];
        $status = ['Pending', 'On Leave', 'Mutation', 'Active', 'Resign'];
        $banks = Banks::get();
        $religion = ['Buddha', 'Catholic Christian', 'Christian', 'Confucian', 'Hindu', 'Islam'];
        $last_education = ['Elementary School', 'Junior High School', 'Senior High School', 'Diploma I', 'Diploma II', 'Diploma III', 'Diploma IV', 'Bachelor Degree', 'Masters degree', 'Vocational School', 'Lord'];
        return view('pages.Employee.edit', [
            'allEmployees' => $allEmployees,
            'selectedAtasans' => $selectedAtasans,
            'primaryEmployeeId' => $primaryEmployeeId,
            'employee' => $employee,
            'status_employee' => $status_employee,
            'child' => $child,
            'employees' => $employees,
            'bloodtypes' => $bloodtypes,
            'companys' => $companys,
            'marriage' => $marriage,
            'status' => $status,
            'gender' => $gender,
            'gradings' => $gradings,
            'groups' => $groups,
            'banks' => $banks,
            'religion' => $religion,
            'last_education' => $last_education,
            'allStores' => $allStores,
            'allPositions' => $allPositions,
            'allDepartments' => $allDepartments,
            'selectedStores' => $selectedStores,
            'selectedDepartments' => $selectedDepartments,
            'selectedPositions' => $selectedPositions,
            'primaryStoreId' => $primaryStoreId,
            'primaryDepartmentId' => $primaryDepartmentId,
            'primaryPositionId' => $primaryPositionId,
            'hashedId' => $hashedId,
        ]);
    }
    public function show($hashedId)
    {
        
        $employee = User::with('Employee', 'Employee.store', 'Employee.department', 'Employee.position', 'Employee.bank', 'Employee.grading', 'Employee.group', 'Employee.employees')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$employee) {
            abort(404, 'Employee not found.');
        }
        $allStores = Stores::get();
        $allPositions = Position::get();
        $allDepartments = Departments::get();
        
        $allEmployees = Employee::where('status', 'Active')
            ->whereHas(
                'grading',
                fn($q) =>
                $q->where('level', '<', $employee->Employee->grading->level)
            )
            ->get();
        $selectedStores = $employee->Employee->store->pluck('id')->toArray();
        $selectedDepartments = $employee->Employee->department->pluck('id')->toArray();
        $selectedPositions = $employee->Employee->position->pluck('id')->toArray();
        $selectedAtasans = $employee->Employee->atasanList->pluck('id')->toArray();
        $primaryStoreId = $employee->Employee->store()
            ->wherePivot('is_primary', true)
            ->first()?->id;
        $primaryPositionId = $employee->Employee->position()
            ->wherePivot('is_primary', true)
            ->first()?->id;
        $primaryDepartmentId = $employee->Employee->department()
            ->wherePivot('is_primary', true)
            ->first()?->id;
        $primaryEmployeeId = $employee->Employee->atasanList()
            ->wherePivot('is_primary', true)
            ->first()?->id;
        $companys = Company::get();
        $gradings = Grading::get();
        $groups = Groups::get();
        $employees = Employee::where('status', 'Active')->pluck('employee_name', 'id');
        $status_employee = ['PKWT', 'DW', 'PKWTT', 'On Job Training'];
        $child = ['0', '1', '2', '3', '4', '5'];
        $marriage = ['Yes', 'No'];
        $bloodtypes = Employee::getBloodTypeOptions();

        $gender = ['Male', 'Female', 'MD'];
        $status = ['Pending', 'On Leave', 'Mutation', 'Active', 'Resign'];
        $banks = Banks::get();
        $religion = ['Buddha', 'Catholic Christian', 'Christian', 'Confucian', 'Hindu', 'Islam'];
        $last_education = ['Elementary School', 'Junior High School', 'Senior High School', 'Diploma I', 'Diploma II', 'Diploma III', 'Diploma IV', 'Bachelor Degree', 'Masters degree', 'Vocational School', 'Lord'];
        // Tambahkan sebelum return view
$documents = Documents::with([
    'companydocumentconfigs.documenttypes',
    'companydocumentconfigs.company',
])
->where('employee_id', $employee->Employee->id)
->get();
        return view('pages.Employee.show', [
            'documents' => $documents,
            'allEmployees' => $allEmployees,
            'selectedAtasans' => $selectedAtasans,
            'primaryEmployeeId' => $primaryEmployeeId,
            'employee' => $employee,
            'status_employee' => $status_employee,
            'child' => $child,
            'employees' => $employees,
            'bloodtypes' => $bloodtypes,
            'companys' => $companys,
            'marriage' => $marriage,
            'status' => $status,
            'gender' => $gender,
            'gradings' => $gradings,
            'groups' => $groups,
            'banks' => $banks,
            'religion' => $religion,
            'last_education' => $last_education,
            'allStores' => $allStores,
            'allPositions' => $allPositions,
            'allDepartments' => $allDepartments,
            'selectedStores' => $selectedStores,
            'selectedDepartments' => $selectedDepartments,
            'selectedPositions' => $selectedPositions,
            'primaryStoreId' => $primaryStoreId,
            'primaryDepartmentId' => $primaryDepartmentId,
            'primaryPositionId' => $primaryPositionId,
            'hashedId' => $hashedId,
        ]);
    }

    public function create()
    {
        /** @var \App\Models\User|null $user */
    $user = auth()->user();
         if (!$user->hasPermissionTo('ManageEmployee')) {
        abort(403);
    }
        $employees = Employee::where('status', 'Active')
            ->pluck('employee_name', 'id');
        $atasans = Employee::where('status', 'Active')
            ->pluck('employee_name', 'id');
        $stores = Stores::pluck('name', 'id')->all();
        $positions = Position::pluck('name', 'id')->all();
        $gradings = Grading::pluck('grading_name', 'id')->all();
        $departments = Departments::pluck('department_name', 'id')->all();
        $companys = Company::pluck('name', 'id')->all();
        $banks = Banks::pluck('name', 'id')->all();
        $status_employee = ['PKWT', 'DW', 'PKWTT', 'On Job Training'];
        $employeestatuses = Employee::getStatusEmployeeOptions();
        $status_child = ['0', '1', '2', '3', '4', '5'];
        $status_marriage = ['Yes', 'No'];
        $status_gender = ['Male', 'Female', 'MD'];
        $status = ['Active', 'Pending', 'On Leave', 'Mutation', 'Resign'];
        $bloodtypes = Employee::getBloodTypeOptions();
        $status_religion = ['Buddha', 'Catholic Christian', 'Christian', 'Confucian', 'Hindu', 'Islam'];
        $status_last_education = ['Elementary School', 'Junior High School', 'Senior High School', 'Diploma I', 'Diploma II', 'Diploma III', 'Diploma IV', 'Bachelor Degree', 'Masters degree', 'Vocational School', 'Lord'];
        return view('pages.Employee.create', compact('atasans', 'employeestatuses', 'gradings', 'employees', 'bloodtypes', 'companys', 'stores', 'banks', 'status_marriage', 'positions', 'departments', 'status_employee', 'status_child', 'status_gender', 'status_religion', 'status_last_education', 'status'));
    }
    public function store(Request $request)
    {
        /** @var \App\Models\User|null $user */
    $user = auth()->user();
         if (!$user->hasPermissionTo('ManageEmployee')) {
        abort(403);
    }
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
            'kk_photos' => [
                'nullable',
                'mimes:jpg,jpeg,png,webp',
                'max:512'
            ],
            'ktp_photos' => [
                'nullable',
                'mimes:jpg,jpeg,png,webp',
                'max:512'
            ],
            'signature_file' => [
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
            'email' => ['required', 'string', 'max:255',  'not_regex:/[\r\n]/', new NoXSSInput()],
            'company_email' => ['nullable', 'string', 'max:255',  'not_regex:/[\r\n]/', new NoXSSInput()],
            'emergency_contact_name' => ['required', 'string', 'max:255', new NoXSSInput()],
            'marriage' => ['required', 'string', 'max:255', new NoXSSInput()],
            'child' => ['required', 'string', 'max:255', new NoXSSInput()],
            'blood_type' => ['nullable', 'string', 'max:255', new NoXSSInput()],
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
            'grading_id' => ['required', 'exists:grading,id', new NoXSSInput()],
            'position_id' => ['required', 'exists:position_tables,id', new NoXSSInput()],
            'store_id' => ['required', 'exists:stores_tables,id', new NoXSSInput()],
            'department_id' => ['required', 'exists:departments_tables,id', new NoXSSInput()],
            'atasan_id' => ['nullable', 'exists:employees_tables,id', new NoXSSInput()],
            'company_id' => ['required', 'exists:company_tables,id', new NoXSSInput()],
            'banks_id' => ['required', 'exists:banks_tables,id', new NoXSSInput()],
            'can_approve'       => 'nullable|boolean',

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
            'company_id.exists' => 'The selected company is invalid.',
            'company_id.required' => 'The Company is required.',
            'banks_id.exists' => 'The selected banks is invalid.',
            'banks_id.required' => 'The banks is required.',
            'grading_id.required' => 'The Grading is required.',
        ]);
        /*
    |--------------------------------------------------------------------------
    | HELPER: Upload file ke S3 dengan aman
    |--------------------------------------------------------------------------
    */
        $uploadToS3 = function ($file, string $safeName, string $suffix, string $folder) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower($file->getClientOriginalExtension());

            // ✅ Whitelist ekstensi
            if (!in_array($ext, $allowedExtensions)) {
                abort(400, 'File type not allowed');
            }

            $fileName = $safeName . '-' . now()->timestamp . '-' . $suffix . '.' . $ext;
            $path     = $folder . '/' . $fileName;

            Storage::disk('s3')->putFileAs($folder, $file, $fileName);

            return $path;
        };

        $safeName      = Str::slug($request->employee_name);
        $photoPath     = null;
        $kkPhotoPath   = null;
        $ktpPhotoPath  = null;
        $signaturePath = null;
        /*
    |--------------------------------------------------------------------------
    | PHOTO
    |--------------------------------------------------------------------------
    */
        if ($request->hasFile('photos')) {
            $photoPath = $uploadToS3(
                $request->file('photos'),
                $safeName,
                'photos',
                'employees-photos'
            );
        }

        /*
    |--------------------------------------------------------------------------
    | KK PHOTO
    |--------------------------------------------------------------------------
    */
        if ($request->hasFile('kk_photos')) {
            $kkPhotoPath = $uploadToS3(
                $request->file('kk_photos'),
                $safeName,
                'kk',
                'employees-kk-photos'
            );
        }

        /*
    |--------------------------------------------------------------------------
    | KTP PHOTO
    |--------------------------------------------------------------------------
    */
        if ($request->hasFile('ktp_photos')) {
            $ktpPhotoPath = $uploadToS3(
                $request->file('ktp_photos'),
                $safeName,
                'ktp',
                'employees-ktp-photos'
            );
        }

        /*
    |--------------------------------------------------------------------------
    | SIGNATURE FILE
    |--------------------------------------------------------------------------
    */
        if ($request->hasFile('signature_file')) {
            $signaturePath = $uploadToS3(
                $request->file('signature_file'),
                $safeName,
                'signature',
                'employees-signatures-photos'
            );
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
                'photos'                  => $photoPath,
                'kk_photos'               => $kkPhotoPath,
                'ktp_photos'              => $ktpPhotoPath,
                'signature'               => $signaturePath,
                'employee_pengenal' => $employeeId,
                'employee_name' => $validatedData['employee_name'] ?? '',
                'nik' => $validatedData['nik'] ?? '',
                'bank_account_number' => $validatedData['bank_account_number'] ?? '',
                'company_id' => $validatedData['company_id'] ?? '',
                'banks_id' => $validatedData['banks_id'] ?? '',
                'grading_id' => $validatedData['grading_id'] ?? '',
                'status_employee' => $validatedData['status_employee'] ?? '',
                'join_date' => $validatedData['join_date'] ?? '',
                'blood_type' => $validatedData['blood_type'] ?? '',
                'marriage' => $validatedData['marriage'] ?? '',
                'child' => $validatedData['child'] ?? '',
                'telp_number' => $validatedData['telp_number'] ?? '',
                'gender' => $validatedData['gender'] ?? '',
                'date_of_birth' => $validatedData['date_of_birth'] ?? '',
                'is_manager' => $validatedData['is_manager'] ?? 0,
                'bpjs_kes' => $validatedData['bpjs_kes'] ?? '',
                'bpjs_ket' => $validatedData['bpjs_ket'] ?? '',
                'email' => $validatedData['email'] ?? '',
                'can_approve' => $validatedData['can_approve'] ?? false,
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

            $employees->store()->attach($validatedData['store_id'], ['is_primary' => true]);
            $employees->position()->attach($validatedData['position_id'], ['is_primary' => true]);
            $employees->department()->attach($validatedData['department_id'], ['is_primary' => true]);
            $employees->atasanList()->attach($validatedData['atasan_id'], ['is_primary' => true]);

            // ← Log pivot saat create
            activity('employee')
                ->performedOn($employees)
                ->causedBy(auth()->user())
                ->withProperties([
                    'attributes' => [
                        'store'      => $employees->store()->wherePivot('is_primary', true)->first()?->name ?? '-',
                        'position'   => $employees->position()->wherePivot('is_primary', true)->first()?->name ?? '-',
                        'department' => $employees->department()->wherePivot('is_primary', true)->first()?->department_name ?? '-',
                        'atasanList' => $employees->atasanList()->wherePivot('is_primary', true)->first()?->employee_name ?? '-',
                    ],
                ])
                ->log('Employee pivot data ' . $employees->employee_name . ' has been created.');


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


            foreach ([$photoPath, $kkPhotoPath, $ktpPhotoPath, $signaturePath] as $path) {
                if ($path && Storage::disk('s3')->exists($path)) {
                    Storage::disk('s3')->delete($path);
                }
            }
            return redirect()->back()
                ->withErrors(['error' => 'Error while creating data: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function update(Request $request, $hashedId)
    {
        /** @var \App\Models\User|null $user */
    $user = auth()->user();
         if (!$user->hasPermissionTo('ManageEmployee')) {
        abort(403);
    }
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
            'photos'                  => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:512'],
            'kk_photos'               => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:512'],
            'ktp_photos'              => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:512'],
            'signature_file'          => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:512'],
            'join_date'               => ['required', 'date_format:Y-m-d', new NoXSSInput()],
            'end_date'                => ['nullable', 'date_format:Y-m-d', new NoXSSInput()],
            'date_of_birth'           => ['required', 'date_format:Y-m-d', new NoXSSInput()],
            'employee_name'           => ['required', 'string', 'max:255', Rule::unique('employees_tables', 'employee_name')->ignore($user->Employee->id), new NoXSSInput()],
            'grading_id'              => ['nullable', 'exists:grading,id', new NoXSSInput()],
            'group_id'                => ['nullable', 'exists:groups_tables,id', new NoXSSInput()],
            'bpjs_kes'                => ['required', 'string', 'max:255'],
            'blood_type'              => ['nullable', 'string', 'max:255'],
            'bpjs_ket'                => ['required', 'string', 'max:255'],
            'email'                   => ['required', 'string', 'max:255', 'not_regex:/[\r\n]/'],
            'company_email'           => ['nullable', 'string', 'max:255', 'not_regex:/[\r\n]/'],
            'emergency_contact_name'  => ['required', 'string', 'max:255', new NoXSSInput()],
            'marriage'                => ['required', 'string', 'max:255', new NoXSSInput()],
            'notes'                   => ['nullable', 'string', 'max:255', new NoXSSInput()],
            'child'                   => ['required', 'string', 'max:255', new NoXSSInput()],
            'gender'                  => ['required', 'string', 'max:255', new NoXSSInput()],
            'telp_number'             => ['required', 'numeric', 'digits_between:10,13', Rule::unique('employees_tables', 'telp_number')->ignore($user->Employee->id), new NoXSSInput()],
            'status_employee'         => ['required', 'string', 'max:255', new NoXSSInput()],
            'nik'                     => ['required', 'max:20', Rule::unique('employees_tables', 'nik')->ignore($user->Employee->id), new NoXSSInput()],
            'bank_account_number'     => ['required', 'max:20', new NoXSSInput()],
            'last_education'          => ['required', 'string', 'max:255', new NoXSSInput()],
            'religion'                => ['required', 'string', new NoXSSInput()],
            'status'                  => ['required', 'string', new NoXSSInput()],
            'place_of_birth'          => ['required', 'string', 'max:255', new NoXSSInput()],
            'biological_mother_name'  => ['required', 'string', 'max:255', new NoXSSInput()],
            'current_address'         => ['required', 'string', 'max:255', new NoXSSInput()],
            'id_card_address'         => ['required', 'string', 'max:255', new NoXSSInput()],
            'institution'             => ['required', 'string', 'max:255', new NoXSSInput()],
            'npwp'                    => ['required', 'string', 'max:50'],
            'can_approve'       => 'nullable|boolean',
            'pin'                     => ['required', 'string', 'max:50', Rule::unique('employees_tables', 'pin')->ignore($user->Employee->id), new NoXSSInput()],
            'company_id'              => ['nullable', 'exists:company_tables,id', new NoXSSInput()],
            'banks_id'                => ['required', 'exists:banks_tables,id', new NoXSSInput()],
            'stores'   => ['nullable', 'array'],
            'stores.*' => ['exists:stores_tables,id', new NoXSSInput()],
            'atasans'   => ['nullable', 'array'],
            'atasans.*' => ['exists:employees_tables,id', new NoXSSInput()],
            'positions'   => ['nullable', 'array'],
            'positions.*' => ['exists:position_tables,id', new NoXSSInput()],
            'departments'   => ['nullable', 'array'],
            'departments.*' => ['exists:departments_tables,id', new NoXSSInput()],
        ]);

        $uploadToS3 = function ($file, string $safeName, string $suffix, string $folder) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, $allowedExtensions)) {
                abort(400, 'File type not allowed');
            }
            $fileName = $safeName . '-' . now()->timestamp . '-' . $suffix . '.' . $ext;
            Storage::disk('s3')->putFileAs($folder, $file, $fileName);
            return $folder . '/' . $fileName;
        };

        $safeName = Str::slug($request->employee_name);
        $employee = $user->Employee;

        $oldPaths = [
            'photos'    => $employee->photos,
            'kk_photos' => $employee->kk_photos,
            'ktp_photos' => $employee->ktp_photos,
            'signature' => $employee->signature,
        ];

        $newPaths = [];

        if ($request->hasFile('photos')) {
            $newPaths['photos'] = $uploadToS3($request->file('photos'), $safeName, 'photos', 'employees-photos');
            $validatedData['photos'] = $newPaths['photos'];
        }
        if ($request->hasFile('kk_photos')) {
            $newPaths['kk_photos'] = $uploadToS3($request->file('kk_photos'), $safeName, 'kk', 'employees-kk-photos');
            $validatedData['kk_photos'] = $newPaths['kk_photos'];
        }
        if ($request->hasFile('ktp_photos')) {
            $newPaths['ktp_photos'] = $uploadToS3($request->file('ktp_photos'), $safeName, 'ktp', 'employees-ktp-photos');
            $validatedData['ktp_photos'] = $newPaths['ktp_photos'];
        }
        if ($request->hasFile('signature_file')) {
            $newPaths['signature'] = $uploadToS3($request->file('signature_file'), $safeName, 'signature', 'employees-signatures-photos');
            $validatedData['signature'] = $newPaths['signature'];
        }

        try {

            DB::transaction(function () use ($user, &$validatedData, $oldPaths, $newPaths) {
                $employee = $user->Employee()->lockForUpdate()->first();

                $oldStore      = $employee->store()->pluck('name')->sort()->values()->toArray();
                $oldPosition   = $employee->position()->pluck('name')->sort()->values()->toArray();
                $oldDepartment = $employee->department()->pluck('department_name')->sort()->values()->toArray();
                $oldAtasan     = $employee->atasanList()->pluck('employee_name')->sort()->values()->toArray();

                // ── Sync store ──
                if (!empty($validatedData['stores'])) {
                    $currentPrimary = $employee->store()->wherePivot('is_primary', true)->first();
                    $primaryId = $currentPrimary && in_array($currentPrimary->id, $validatedData['stores'])
                        ? $currentPrimary->id
                        : $validatedData['stores'][0];

                    $syncData = [];
                    foreach ($validatedData['stores'] as $storeId) {
                        $syncData[$storeId] = ['is_primary' => $storeId === $primaryId];
                    }
                    $employee->store()->sync($syncData);
                } else {
                    // Kalau kosong → hapus semua atasan
                    $employee->store()->detach();
                }


                if (!empty($validatedData['positions'])) {
                    $currentPrimary = $employee->position()->wherePivot('is_primary', true)->first();
                    $primaryId = $currentPrimary && in_array($currentPrimary->id, $validatedData['positions'])
                        ? $currentPrimary->id
                        : $validatedData['positions'][0];

                    $syncData = [];
                    foreach ($validatedData['positions'] as $positionId) {
                        $syncData[$positionId] = ['is_primary' => $positionId === $primaryId];
                    }
                    $employee->position()->sync($syncData);
                } else {
                    // Kalau kosong → hapus semua atasan
                    $employee->position()->detach();
                }
                // Sync atasanList
                if (!empty($validatedData['departments'])) {
                    $currentPrimary = $employee->department()->wherePivot('is_primary', true)->first();
                    $primaryId = $currentPrimary && in_array($currentPrimary->id, $validatedData['departments'])
                        ? $currentPrimary->id
                        : $validatedData['departments'][0];

                    $syncData = [];
                    foreach ($validatedData['departments'] as $departmentId) {
                        $syncData[$departmentId] = ['is_primary' => $departmentId === $primaryId];
                    }
                    $employee->department()->sync($syncData);
                } else {
                    // Kalau kosong → hapus semua atasan
                    $employee->department()->detach();
                }
                if (!empty($validatedData['atasans'])) {
                    $currentPrimary = $employee->atasanList()->wherePivot('is_primary', true)->first();
                    $primaryId = $currentPrimary && in_array($currentPrimary->id, $validatedData['atasans'])
                        ? $currentPrimary->id
                        : $validatedData['atasans'][0];

                    $syncData = [];
                    foreach ($validatedData['atasans'] as $atasanId) {
                        $syncData[$atasanId] = ['is_primary' => $atasanId === $primaryId];
                    }
                    $employee->atasanList()->sync($syncData);
                } else {
                    // Kalau kosong → hapus semua atasan
                    $employee->atasanList()->detach();
                }

                // ── Ambil nilai baru setelah sync ──
                $newStore      = $employee->store()->pluck('name')->sort()->values()->toArray();
                $newPosition   = $employee->position()->pluck('name')->sort()->values()->toArray();
                $newDepartment = $employee->department()->pluck('department_name')->sort()->values()->toArray();
                $newAtasan     = $employee->atasanList()->pluck('employee_name')->sort()->values()->toArray();


                // ── Log manual perubahan pivot ──
                $pivotChanges = [];

                if ($oldStore !== $newStore) {
                    $pivotChanges[] = "Location: [" . implode(', ', $oldStore) . "] → [" . implode(', ', $newStore) . "]";
                }
                if ($oldPosition !== $newPosition) {
                    $pivotChanges[] = "Position: [" . implode(', ', $oldPosition) . "] → [" . implode(', ', $newPosition) . "]";
                }
                if ($oldDepartment !== $newDepartment) {
                    $pivotChanges[] = "Department: [" . implode(', ', $oldDepartment) . "] → [" . implode(', ', $newDepartment) . "]";
                }
                if ($oldAtasan !== $newAtasan) {
                    $pivotChanges[] = "Atasan: [" . implode(', ', $oldAtasan) . "] → [" . implode(', ', $newAtasan) . "]";
                }

                if (!empty($pivotChanges)) {
                    activity('employee')
                        ->performedOn($employee)
                        ->causedBy(auth()->user())
                        ->withProperties([
                            'attributes' => [
                                'store'      => $newStore,
                                'position'   => $newPosition,
                                'department' => $newDepartment,
                                'atasanList' => $newAtasan,
                            ],
                            'old' => [
                                'store'      => $oldStore,
                                'position'   => $oldPosition,
                                'department' => $oldDepartment,
                                'atasanList' => $oldAtasan,
                            ],
                        ])
                        ->log('Employee pivot data ' . $employee->employee_name . ' has been updated. Changes: ' . implode(', ', $pivotChanges));
                }

                // ── Hapus FK dari validatedData ──
                unset(
                    $validatedData['stores'],
                    $validatedData['departments'],
                    $validatedData['positions'],
                    $validatedData['atasans']
                );

                // ── Update employee biasa ──
                $employee->update($validatedData);

                // ── Hapus file lama di S3 ──
                foreach (['photos', 'kk_photos', 'ktp_photos', 'signature'] as $key) {
                    if (isset($newPaths[$key]) && $oldPaths[$key] && Storage::disk('s3')->exists($oldPaths[$key])) {
                        Storage::disk('s3')->delete($oldPaths[$key]);
                    }
                }
            });


            return redirect()->route('pages.Employee')->with('success', 'Employee Updated Successfully.');
        } catch (\Throwable $th) {
            foreach ($newPaths as $path) {
                if ($path && Storage::disk('s3')->exists($path)) {
                    Storage::disk('s3')->delete($path);
                }
            }

            Log::error('Employee update failed', [
                'error'       => $th->getMessage(),
                'employee_id' => $user->Employee->id ?? null,
            ]);

            return redirect()->route('pages.Employee')
                ->with('error', 'Update failed: ' . $th->getMessage());
        }
    }

    // public function transferAllToPayroll(Request $request)
    // {
    //     try {
    //         $month_year = $request->input('month_year', date('Y-m-d'));

    //         $month = date('m', strtotime($month_year));
    //         $year = date('Y', strtotime($month_year));

    //         $employeeIds = User::whereNotNull('employee_id')
    //             ->whereHas('employee', function ($query) {
    //                 $query->whereIn('status', ['Mutation', 'Active', 'On Leave']);
    //             })
    //             ->pluck('employee_id')
    //             ->toArray();

    //         $transferred = 0;
    //         $skipped = 0;

    //         foreach ($employeeIds as $employeeId) {
    //             $exists = Payrolls::where('employee_id', $employeeId)
    //                 ->whereMonth('month_year', $month)
    //                 ->whereYear('month_year', $year)
    //                 ->exists();

    //             if (!$exists) {
    //                 Payrolls::create([
    //                     'employee_id' => $employeeId,
    //                     'month_year' => $month_year,
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ]);
    //                 $transferred++;
    //             } else {
    //                 $skipped++;
    //             }
    //         }
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

    private function serveFile(string $filename, string $folder, string $column): \Illuminate\Http\Response
    {
        // 1. Autentikasi
        if (!auth()->check()) {
            abort(401);
        }

        // 2. Validasi format filename
        if (!preg_match('/^[\w\-]+\.(jpg|jpeg|png|gif|webp)$/i', $filename)) {
            abort(400, 'Invalid filename');
        }

        // 3. Basename untuk mencegah path traversal
        $filename = basename($filename);

        // 4. Whitelist ekstensi
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions)) {
            abort(400, 'File type not allowed');
        }
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        // 5. ✅ Hanya user dengan permission yang bisa akses
        if (!$user->can('ManageEmployee')) {
            abort(403, 'Forbidden: You are not allowed to access this file');
        }

        $fullPath = $folder . '/' . $filename;

        // 6. Cek file exists di S3
        if (!Storage::disk('s3')->exists($fullPath)) {
            abort(404);
        }

        $file = Storage::disk('s3')->get($fullPath);

        // 7. MIME type dari whitelist
        $mimeTypes = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
        ];

        return response($file, 200)
            ->header('Content-Type', $mimeTypes[$extension])
            ->header('Content-Security-Policy', "default-src 'none'")
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Cache-Control', 'private, max-age=3600');
    }
    // ============================================================
    // PUBLIC: masing-masing file punya route sendiri
    // ============================================================
    public function serveSignature($filename)
    {
        return $this->serveFile($filename, 'employees-signatures-photos', 'signature');
    }

    public function servePhoto($filename)
    {
        return $this->serveFile($filename, 'employees-photos', 'photos');
    }

    public function serveKtpPhoto($filename)
    {
        return $this->serveFile($filename, 'employees-ktp-photos', 'ktp_photos');
    }

    public function serveKkPhoto($filename)
    {
        return $this->serveFile($filename, 'employees-kk-photos', 'kk_photos');
    }
   public function downloadDocument(string $hashedId, string $documentId)
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();

    if (!$user->hasPermissionTo('ManageEmployee')) {
        abort(403);
    }

    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $documentId)) {
        abort(400, 'Invalid ID');
    }

    $targetUser = User::with('Employee')
        ->get()
        ->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

    if (!$targetUser) {
        abort(404, 'Employee not found');
    }

    $document = Documents::with([
        'employee.position',
        'issued.position',
        'companydocumentconfigs.company',
        'companydocumentconfigs.documenttypes',
    ])
        ->where('employee_id', $targetUser->employee_id)
        ->findOrFail($documentId);

    if (!$document->companydocumentconfigs || !$document->companydocumentconfigs->documenttypes) {
        abort(404, 'Document configuration not found');
    }

    $viewName = $document->companydocumentconfigs->documenttypes->view_name;

    $allowedViews = [
        'documents.types.SPK',
        'documents.types.SPPRP',
    ];

    if (!in_array($viewName, $allowedViews)) {
        abort(403, 'Invalid document view');
    }

    $signatureData = null;
    if ($document->issued && $document->issued->signature) {
        $path = 'employees-signatures-photos/' . basename($document->issued->signature);
        if (Storage::disk('s3')->exists($path)) {
            $signatureData = 'data:image/png;base64,' . base64_encode(
                Storage::disk('s3')->get($path)
            );
        }
    }

    $pdf = Pdf::loadView($viewName, [
        'document'      => $document,
        'employee'      => $document->employee,
        'issued'        => $document->issued,
        'config'        => $document->companydocumentconfigs,
        'company'       => $document->companydocumentconfigs->company,
        'signatureData' => $signatureData,
    ])->setPaper('a4');

    $password = Carbon::parse(
        $targetUser->employee->date_of_birth
    )->format('Ymd');

    $domPdf = $pdf->getDomPDF();
    $canvas = $domPdf->getCanvas();

    if (method_exists($canvas, 'get_cpdf')) {
        $cpdf = $canvas->get_cpdf();
        $cpdf->setEncryption($password, $password);
    }

    $filename = str_replace('/', '-', $document->document_number) . '.pdf';
    return $pdf->download($filename);
}
}
 // public function getBagan(Request $request)
    // {
    //     try {
    //         $storeId = $request->store_id;
    //         $departmentId = $request->department_id;

    //         $employees = Employee::with(['grading'])
    //             ->whereHas('store', fn($q) => $q->where('stores_tables.id', $storeId))
    //             ->whereHas('department', fn($q) => $q->where('departments_tables.id', $departmentId))
    //             ->whereHas('grading')
    //             ->whereIn('status', ['Active', 'Pending'])
    //             ->get()
    //             ->sortBy('grading.level');

    //         Log::info('getBagan debug', [
    //             'store_id'     => $storeId,
    //             'department_id' => $departmentId,
    //             'count'        => $employees->count(),
    //         ]);

    //         $bagan = $employees->map(function ($employee) {
    //             try {
    //                 $atasan = $employee->atasan();

    //                 $photoFilename = $employee->photos
    //                     ? basename($employee->photos)
    //                     : null;

    //                 return [
    //                     'id'            => $employee->id,
    //                     'name'          => $employee->employee_name,
    //                     'position'      => $employee->primaryPosition()->first()?->name ?? '-',
    //                     'grading'       => $employee->grading?->grading_name ?? '-',
    //                     'grading_level' => $employee->grading?->level ?? 0,
    //                     'photo'         => $photoFilename
    //                                         ? route('employee.serve.photo', ['filename' => $photoFilename])
    //                                         : null,
    //                     'atasan_id'     => $atasan?->id ?? null,
    //                 ];
    //             } catch (\Throwable $e) {
    //                 Log::error('map error employee ' . $employee->id, [
    //                     'error' => $e->getMessage(),
    //                     'line'  => $e->getLine(),
    //                 ]);
    //                 return null;
    //             }
    //         })->filter()->values();

    //         return response()->json(['nodes' => $bagan]); // ← ini yang hilang

    //     } catch (\Throwable $e) {
    //         return response()->json([
    //             'error' => $e->getMessage(),
    //             'line'  => $e->getLine(),
    //             'file'  => $e->getFile(),
    //         ], 500);
    //     }
    // }
// public function getEmployees(Request $request)
    // {
    //     /** @var \App\Models\User|null $user */
    //     $user = auth()->user();
    //     $isHeadHR = $user->hasAnyRole(['HeadHR', 'HR', 'Admin']);

    //     $query = User::query()
    //         ->with([
    //             'employee',
    //             'employee.position',
    //             'employee.department',
    //             'employee.grading',
    //             'employee.group',
    //             'employee.store',
    //             'employee.company'
    //         ])
    //         ->leftJoin('employees_tables', 'users.employee_id', '=', 'employees_tables.id')
    //         ->leftJoin('position_tables', 'position_tables.id', '=', 'employees_tables.position_id')
    //         // ->leftJoin('stores_tables', 'stores_tables.id', '=', 'employees_tables.store_id')
    //         // ->leftJoin('departments_tables', 'departments_tables.id', '=', 'employees_tables.department_id')
    //         ->leftJoin('grading', 'grading.id', '=', 'employees_tables.grading_id')
    //         ->leftJoin('groups_tables', 'groups_tables.id', '=', 'employees_tables.group_id')
    //         ->leftJoin('company_tables', 'company_tables.id', '=', 'employees_tables.company_id')

    //         ->select([
    //             'users.*',
    //             'employees_tables.employee_name',
    //             'employees_tables.employee_pengenal',
    //             'employees_tables.status_employee',
    //             'employees_tables.status',
    //             'employees_tables.join_date',
    //             'position_tables.name as position_name',
    //             'groups_tables.remark as remark',
    //             'stores_tables.name as name',
    //             'departments_tables.department_name',
    //             'grading.grading_name',
    //             'company_tables.name as name_company',
    //         ]);
    //     // Filter tetap pakai whereHas atau bisa pakai where langsung karena sudah di-join
    //     $query->when(
    //         $request->filled('filter_company'),
    //         fn($q) =>
    //         $q->where('company_tables.name', $request->filter_company)
    //     );

    //     $query->when(
    //         $request->filled('filter_department'),
    //         fn($q) =>
    //         $q->where('departments_tables.department_name', $request->filter_department)
    //     );
    //     $query->when(
    //         $request->filled('filter_group'),
    //         fn($q) =>
    //         $q->where('groups_tables.remark', $request->filter_group)
    //     );
    //     $query->when(
    //         $request->filled('filter_grading'),
    //         fn($q) =>
    //         $q->where('grading.grading_name', $request->filter_grading)
    //     );
    //     $query->when(
    //         $request->filled('filter_store'),
    //         fn($q) =>
    //         $q->where('stores_tables.name', $request->filter_store)
    //     );
    //     $query->when(
    //         $request->filled('filter_emp_status'),
    //         fn($q) =>
    //         $q->where('employees_tables.status_employee', $request->filter_emp_status)
    //     );
    //     $query->when(
    //         $request->filled('filter_status'),
    //         fn($q) =>
    //         $q->where('employees_tables.status', $request->filter_status)
    //     );
    //     $query->when($request->filled('filter_los'), function ($q) use ($request) {
    //         $los = $request->filter_los;

    //         if ($los === 'under3months') {
    //             // Khusus kurang dari 3 bulan, operator berbeda
    //             $q->where('employees_tables.join_date', '>=', Carbon::now()->subMonths(3));
    //         } else {
    //             $date = match ($los) {
    //                 '1year'  => Carbon::now()->subYear(),
    //                 '3years' => Carbon::now()->subYears(3),
    //                 '5years' => Carbon::now()->subYears(5),
    //                 default  => null,
    //             };
    //             if ($date) {
    //                 $q->where('employees_tables.join_date', '<=', $date);
    //             }
    //         }
    //     });
    //     return DataTables::of($query)
    //         ->addColumn('length_of_service', function ($e) {
    //             if (!$e->join_date) return 'Empty';
    //             $diff = Carbon::parse($e->join_date)->diff(Carbon::now());
    //             return sprintf('%d year %d month %d days', $diff->y, $diff->m, $diff->d);
    //         })
    //         ->addColumn('action', function ($e) use ($isHeadHR) {
    //             if (!$isHeadHR) return '';
    //             $id_hashed = substr(hash('sha256', $e->id . env('APP_KEY')), 0, 8);
    //             return '
    //             <a href="' . route('Employee.edit', $id_hashed) . '" class="mx-2">
    //                 <i class="fas fa-user-edit text-secondary"></i>
    //             </a>
    //             <a href="' . route('Employee.show', $id_hashed) . '" class="mx-2">
    //                 <i class="fas fa-eye text-secondary"></i>
    //             </a>';
    //         })
    //         // Daftarkan kolom yang bisa di-search
    //         ->filterColumn('employee_name', fn($q, $k) => $q->where('employees_tables.employee_name', 'like', "%$k%"))
    //         ->filterColumn('employee_pengenal', fn($q, $k) => $q->where('employees_tables.employee_pengenal', 'like', "%$k%"))
    //         ->filterColumn('position_name', fn($q, $k) => $q->where('position_tables.name', 'like', "%$k%"))
    //         ->filterColumn('remark', fn($q, $k) => $q->where('groups_tables.remark', 'like', "%$k%"))
    //         ->filterColumn('department_name', fn($q, $k) => $q->where('departments_tables.department_name', 'like', "%$k%"))
    //         ->filterColumn('name', fn($q, $k) => $q->where('stores_tables.name', 'like', "%$k%"))
    //         ->filterColumn('name_company', fn($q, $k) => $q->where('company_tables.name', 'like', "%$k%"))
    //         ->filterColumn('grading_name', fn($q, $k) => $q->where('grading.grading_name', 'like', "%$k%"))
    //         ->filterColumn('status_employee', fn($q, $k) => $q->where('employees_tables.status_employee', 'like', "%$k%"))
    //         ->filterColumn('status', fn($q, $k) => $q->where('employees_tables.status', 'like', "%$k%"))
    //         ->rawColumns(['action'])
    //         ->make(true);
    // }

    // public function show($hashedId)
    // {
    //     $employee = User::with(
    //         'Employee',
    //         'Employee.store',
    //         'Employee.grading',
    //         'Employee.group',
    //         'Employee.department',
    //         'Employee.position',
    //         'Employee.bank',
    //         'Employee.employees'
           
    //     )->get()->first(function ($u) use ($hashedId) {
    //         $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
    //         return $expectedHash === $hashedId;
    //     });

    //     if (!$employee) {
    //         abort(404, 'Employee not found.');
    //     }
    //     // ---------------------------
    //     // Tambahkan logic aman disini
    //     // ---------------------------

       
    //     // Data lain
    //     $allStores = Stores::get();
    //     $allPositions = Position::get();
    //     $allDepartments = Departments::get();
    //     $selectedStores = $employee->Employee->store->pluck('id')->toArray();
    //     $selectedDepartments = $employee->Employee->department->pluck('id')->toArray();
    //     $selectedPositions = $employee->Employee->position->pluck('id')->toArray();
    //     $primaryStoreId = $employee->Employee->store()
    //         ->wherePivot('is_primary', true)
    //         ->first()?->id;
    //     $primaryPositionId = $employee->Employee->position()
    //         ->wherePivot('is_primary', true)
    //         ->first()?->id;
    //     $primaryDepartmentId = $employee->Employee->department()
    //         ->wherePivot('is_primary', true)
    //         ->first()?->id;
    //     $companys = Company::get();
    //     $employees = Employee::where('status', 'Active')->pluck('employee_name', 'id');
    //     $status_employee = ['PKWT', 'DW', 'PKWTT', 'On Job Training'];
    //     $child = ['0', '1', '2', '3', '4', '5'];
    //     $marriage = ['Yes', 'No'];
    //     $gender = ['Male', 'Female', 'MD'];
    //     $bloodtypes = Employee::getBloodTypeOptions();

    //     $status = ['Pending', 'On Leave', 'Mutation', 'Active', 'Resign'];
    //     $banks = Banks::get();
    //     $gradings = Grading::get();
    //     $groups = Groups::get();
    //     $religion = ['Buddha', 'Catholic Christian', 'Christian', 'Confucian', 'Hindu', 'Islam'];
    //     $last_education = ['Elementary School', 'Junior High School', 'Senior High School', 'Diploma I', 'Diploma II', 'Diploma III', 'Diploma IV', 'Bachelor Degree', 'Masters degree', 'Vocational School', 'Lord'];

    //     return view('pages.Employee.show', compact(
    //         'employee',
    //         'employees',
    //         'status_employee',
    //         'child',
    //         'bloodtypes',
    //         'companys',
    //         'marriage',
    //         'gender',
    //         'gradings',
    //         'groups',
    //         'status',
    //         'banks',
    //         'religion',
    //         'last_education',
    //         'hashedId',
    //         'allStores',
    //         'allPositions',
    //         'allDepartments',
    //         'selectedStores',
    //         'selectedDepartments',
    //         'selectedPositions',
    //         'primaryStoreId',
    //         'primaryDepartmentId',
    //         'primaryPositionId',
    //         'isManager'
    //     ));
    // }
    // public function show($hashedId)
    // {
    //     $employee = User::with('Employee', 'Employee.store', 'Employee.department', 'Employee.position', 'Employee.bank', 'Employee.grading', 'Employee.group', 'Employee.employees')->get()->first(function ($u) use ($hashedId) {
    //         $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
    //         return $expectedHash === $hashedId;
    //     });
    //     if (!$employee) {
    //         abort(404, 'Employee not found.');
    //     }
    //     $allStores = Stores::get();
    //     $allPositions = Position::get();
    //     $allDepartments = Departments::get();
    //     $selectedStores = $employee->Employee->store->pluck('id')->toArray();
    //     $selectedDepartments = $employee->Employee->department->pluck('id')->toArray();
    //     $selectedPositions = $employee->Employee->position->pluck('id')->toArray();
    //     $primaryStoreId = $employee->Employee->store()
    //         ->wherePivot('is_primary', true)
    //         ->first()?->id;
    //     $primaryPositionId = $employee->Employee->position()
    //         ->wherePivot('is_primary', true)
    //         ->first()?->id;
    //     $primaryDepartmentId = $employee->Employee->department()
    //         ->wherePivot('is_primary', true)
    //         ->first()?->id;
    //     $companys = Company::get();
    //     $gradings = Grading::get();
    //     $groups = Groups::get();
    //     $employees = Employee::where('status', 'Active')->pluck('employee_name', 'id');
    //     $status_employee = ['PKWT', 'DW', 'PKWTT', 'On Job Training'];
    //     $child = ['0', '1', '2', '3', '4', '5'];
    //     $marriage = ['Yes', 'No'];
    //     $bloodtypes = Employee::getBloodTypeOptions();

    //     $gender = ['Male', 'Female', 'MD'];
    //     $status = ['Pending', 'On Leave', 'Mutation', 'Active', 'Resign'];
    //     $banks = Banks::get();
    //     $religion = ['Buddha', 'Catholic Christian', 'Christian', 'Confucian', 'Hindu', 'Islam'];
    //     $last_education = ['Elementary School', 'Junior High School', 'Senior High School', 'Diploma I', 'Diploma II', 'Diploma III', 'Diploma IV', 'Bachelor Degree', 'Masters degree', 'Vocational School', 'Lord'];
    //     return view('pages.Employee.show', [
    //         'employee' => $employee,
    //         'status_employee' => $status_employee,
    //         'child' => $child,
    //         'employees' => $employees,
    //         'bloodtypes' => $bloodtypes,
    //         'companys' => $companys,
    //         'marriage' => $marriage,
    //         'status' => $status,
    //         'gender' => $gender,
    //         'gradings' => $gradings,
    //         'groups' => $groups,
    //         'banks' => $banks,
    //         'religion' => $religion,
    //         'last_education' => $last_education,
    //         'allStores' => $allStores,
    //         'allPositions' => $allPositions,
    //         'allDepartments' => $allDepartments,
    //         'selectedStores' => $selectedStores,
    //         'selectedDepartments' => $selectedDepartments,
    //         'selectedPositions' => $selectedPositions,
    //         'primaryStoreId' => $primaryStoreId,
    //         'primaryDepartmentId' => $primaryDepartmentId,
    //         'primaryPositionId' => $primaryPositionId,
    //         'hashedId' => $hashedId,
    //     ]);
    // }
// public function getEmployeesall(Request $request)
    // {
    //     /** @var \App\Models\User|null $user */
    //     $user = auth()->user();
    //     $isHeadHR = $user->hasAnyRole(['HeadHR', 'HR', 'Admin']);

    //     $query = User::query()
    //         ->with([
    //             'employee',
    //             'employee.position',
    //             'employee.department',
    //             'employee.grading',
    //             'employee.group',
    //             'employee.store',
    //             'employee.bank',
    //             'employee.company'
    //         ])
    //         ->leftJoin('employees_tables', 'users.employee_id', '=', 'employees_tables.id')
    //         ->leftJoin('position_tables', 'position_tables.id', '=', 'employees_tables.position_id')
    //         ->leftJoin('grading', 'grading.id', '=', 'employees_tables.grading_id')
    //         ->leftJoin('groups_tables', 'groups_tables.id', '=', 'employees_tables.group_id')
    //         ->leftJoin('company_tables', 'company_tables.id', '=', 'employees_tables.company_id')
    //         ->leftJoin('banks_tables', 'banks_tables.id', '=', 'employees_tables.banks_id')
    //         ->select([
    //             'users.*',
    //             'employees_tables.employee_name',
    //             'employees_tables.employee_pengenal',
    //             'employees_tables.bank_account_number',
    //             'employees_tables.join_date',
    //             'employees_tables.end_date',
    //             'employees_tables.created_at',
    //             'employees_tables.marriage',
    //             'employees_tables.child',
    //             'employees_tables.telp_number',
    //             'employees_tables.nik',
    //             'employees_tables.gender',
    //             'employees_tables.date_of_birth',
    //             'employees_tables.place_of_birth',
    //             'employees_tables.biological_mother_name',
    //             'employees_tables.religion',
    //             'employees_tables.current_address',
    //             'employees_tables.id_card_address',
    //             'employees_tables.last_education',
    //             'employees_tables.institution',
    //             'employees_tables.npwp',
    //             'employees_tables.bpjs_kes',
    //             'employees_tables.bpjs_ket',
    //             'employees_tables.email',
    //             'employees_tables.company_email',
    //             'employees_tables.emergency_contact_name',
    //             'employees_tables.pin',
    //             'employees_tables.pending_email',
    //             'employees_tables.pending_telp_number',
    //             'employees_tables.status_employee',
    //             'employees_tables.status',
    //             'employees_tables.join_date',
    //             'position_tables.name as position_name',
    //             'groups_tables.remark as remark',
    //             'stores_tables.name as name',
    //             'banks_tables.name as bank_name',
    //             'departments_tables.department_name',
    //             'grading.grading_name',
    //             'company_tables.name as name_company',
    //         ]);
    //     $query->when(
    //         $request->filled('filter_company'),
    //         fn($q) =>
    //         $q->where('company_tables.name', $request->filter_company)
    //     );
    //     $query->when(
    //         $request->filled('filter_department'),
    //         fn($q) =>
    //         $q->where('departments_tables.department_name', $request->filter_department)
    //     );
    //     $query->when(
    //         $request->filled('filter_group'),
    //         fn($q) =>
    //         $q->where('groups_tables.remark', $request->filter_group)
    //     );
    //     $query->when(
    //         $request->filled('filter_grading'),
    //         fn($q) =>
    //         $q->where('grading.grading_name', $request->filter_grading)
    //     );
    //     $query->when(
    //         $request->filled('filter_store'),
    //         fn($q) =>
    //         $q->where('stores_tables.name', $request->filter_store)
    //     );
    //     $query->when(
    //         $request->filled('filter_emp_status'),
    //         fn($q) =>
    //         $q->where('employees_tables.status_employee', $request->filter_emp_status)
    //     );
    //     $query->when(
    //         $request->filled('filter_status'),
    //         fn($q) =>
    //         $q->where('employees_tables.status', $request->filter_status)
    //     );
    //     $query->when(
    //         $request->filled('filter_religion'),
    //         fn($q) =>
    //         $q->where('employees_tables.religion', $request->filter_religion)
    //     );
    //     $query->when(
    //         $request->filled('filter_marriage'),
    //         fn($q) =>
    //         $q->where('employees_tables.marriage', $request->filter_marriage)
    //     );
    //     $query->when(
    //         $request->filled('filter_last_education'),
    //         fn($q) =>
    //         $q->where('employees_tables.last_education', $request->filter_last_education)
    //     );
    //     $query->when(
    //         $request->filled('filter_gender'),
    //         fn($q) =>
    //         $q->where('employees_tables.gender', $request->filter_gender)
    //     );
    //     $query->when(
    //         $request->filled('filter_bank'),
    //         fn($q) =>
    //         $q->where('banks_tables.name', $request->filter_bank)
    //     );
    //     $query->when($request->filled('filter_los'), function ($q) use ($request) {
    //         $los = $request->filter_los;

    //         if ($los === 'under3months') {
    //             // Khusus kurang dari 3 bulan, operator berbeda
    //             $q->where('employees_tables.join_date', '>=', Carbon::now()->subMonths(3));
    //         } else {
    //             $date = match ($los) {
    //                 '1year'  => Carbon::now()->subYear(),
    //                 '3years' => Carbon::now()->subYears(3),
    //                 '5years' => Carbon::now()->subYears(5),
    //                 default  => null,
    //             };
    //             if ($date) {
    //                 $q->where('employees_tables.join_date', '<=', $date);
    //             }
    //         }
    //     });
    //     return DataTables::of($query)
    //         ->addColumn('length_of_service', function ($e) {
    //             if (!$e->join_date) return 'Empty';
    //             $diff = Carbon::parse($e->join_date)->diff(Carbon::now());
    //             return sprintf('%d year %d month %d days', $diff->y, $diff->m, $diff->d);
    //         })
    //         ->addColumn('action', function ($e) use ($isHeadHR) {
    //             if (!$isHeadHR) return '';
    //             $id_hashed = substr(hash('sha256', $e->id . env('APP_KEY')), 0, 8);
    //             return '
    //             <a href="' . route('Employee.edit', $id_hashed) . '" class="mx-2">
    //                 <i class="fas fa-user-edit text-secondary"></i>
    //             </a>
    //             <a href="' . route('Employee.show', $id_hashed) . '" class="mx-2">
    //                 <i class="fas fa-eye text-secondary"></i>
    //             </a>';
    //         })
    //         // Daftarkan kolom yang bisa di-search
    //         ->filterColumn('employee_name', fn($q, $k) => $q->where('employees_tables.employee_name', 'like', "%$k%"))
    //         ->filterColumn('employee_pengenal', fn($q, $k) => $q->where('employees_tables.employee_pengenal', 'like', "%$k%"))
    //         ->filterColumn('bank_account_number', fn($q, $k) => $q->where('employees_tables.bank_account_number', 'like', "%$k%"))
    //         ->filterColumn('join_date', fn($q, $k) => $q->where('employees_tables.join_date', 'like', "%$k%"))
    //         ->filterColumn('end_date', fn($q, $k) => $q->where('employees_tables.end_date', 'like', "%$k%"))
    //         ->filterColumn('marriage', fn($q, $k) => $q->where('employees_tables.marriage', 'like', "%$k%"))
    //         ->filterColumn('child', fn($q, $k) => $q->where('employees_tables.child', 'like', "%$k%"))
    //         ->filterColumn('telp_number', fn($q, $k) => $q->where('employees_tables.telp_number', 'like', "%$k%"))
    //         ->filterColumn('nik', fn($q, $k) => $q->where('employees_tables.nik', 'like', "%$k%"))
    //         ->filterColumn('gender', fn($q, $k) => $q->where('employees_tables.gender', 'like', "%$k%"))
    //         ->filterColumn('date_of_birth', fn($q, $k) => $q->where('employees_tables.date_of_birth', 'like', "%$k%"))
    //         ->filterColumn('place_of_birth', fn($q, $k) => $q->where('employees_tables.place_of_birth', 'like', "%$k%"))
    //         ->filterColumn('biological_mother_name', fn($q, $k) => $q->where('employees_tables.biological_mother_name', 'like', "%$k%"))
    //         ->filterColumn('religion', fn($q, $k) => $q->where('employees_tables.religion', 'like', "%$k%"))
    //         ->filterColumn('current_address', fn($q, $k) => $q->where('employees_tables.current_address', 'like', "%$k%"))
    //         ->filterColumn('id_card_address', fn($q, $k) => $q->where('employees_tables.id_card_address', 'like', "%$k%"))
    //         ->filterColumn('last_education', fn($q, $k) => $q->where('employees_tables.last_education', 'like', "%$k%"))
    //         ->filterColumn('institution', fn($q, $k) => $q->where('employees_tables.institution', 'like', "%$k%"))
    //         ->filterColumn('npwp', fn($q, $k) => $q->where('employees_tables.npwp', 'like', "%$k%"))
    //         ->filterColumn('bpjs_kes', fn($q, $k) => $q->where('employees_tables.bpjs_kes', 'like', "%$k%"))
    //         ->filterColumn('bpjs_ket', fn($q, $k) => $q->where('employees_tables.bpjs_ket', 'like', "%$k%"))
    //         ->filterColumn('email', fn($q, $k) => $q->where('employees_tables.email', 'like', "%$k%"))
    //         ->filterColumn('company_email', fn($q, $k) => $q->where('employees_tables.company_email', 'like', "%$k%"))
    //         ->filterColumn('emergency_contact_name', fn($q, $k) => $q->where('employees_tables.emergency_contact_name', 'like', "%$k%"))
    //         ->filterColumn('pin', fn($q, $k) => $q->where('employees_tables.pin', 'like', "%$k%"))
    //         ->filterColumn('pending_email', fn($q, $k) => $q->where('employees_tables.pending_email', 'like', "%$k%"))
    //         ->filterColumn('pending_telp_number', fn($q, $k) => $q->where('employees_tables.pending_telp_number', 'like', "%$k%"))
    //         ->filterColumn('position_name', fn($q, $k) => $q->where('position_tables.name', 'like', "%$k%"))
    //         ->filterColumn('bank_name', fn($q, $k) => $q->where('banks_tables.name', 'like', "%$k%"))
    //         ->filterColumn('remark', fn($q, $k) => $q->where('groups_tables.remark', 'like', "%$k%"))
    //         ->filterColumn('department_name', fn($q, $k) => $q->where('departments_tables.department_name', 'like', "%$k%"))
    //         ->filterColumn('name', fn($q, $k) => $q->where('stores_tables.name', 'like', "%$k%"))
    //         ->filterColumn('name_company', fn($q, $k) => $q->where('company_tables.name', 'like', "%$k%"))
    //         ->filterColumn('grading_name', fn($q, $k) => $q->where('grading.grading_name', 'like', "%$k%"))
    //         ->filterColumn('status_employee', fn($q, $k) => $q->where('employees_tables.status_employee', 'like', "%$k%"))
    //         ->filterColumn('status', fn($q, $k) => $q->where('employees_tables.status', 'like', "%$k%"))
    //         ->editColumn('created_at', function ($e) {
    //             return optional($e->created_at)
    //                 ->timezone('Asia/Makassar')
    //                 ->translatedFormat('d F Y H:i');
    //         })

    //         ->editColumn('join_date', function ($e) {
    //             return $e->join_date
    //                 ? Carbon::parse($e->join_date)
    //                 ->timezone('Asia/Makassar')
    //                 ->translatedFormat('d F Y')
    //                 : '-';
    //         })
    //         ->editColumn('end_date', function ($e) {
    //             return $e->end_date
    //                 ? Carbon::parse($e->end_date)
    //                 ->timezone('Asia/Makassar')
    //                 ->translatedFormat('d F Y')
    //                 : '-';
    //         })
    //         ->rawColumns(['action'])
    //         ->make(true);
    // }
    // public function update(Request $request, $hashedId)
    // {
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
    //         'photos' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:512'],
    //         'kk_photos' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:512'],
    //         'ktp_photos' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:512'],
    //         'signature_file' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:512'],
    //         'join_date' => ['required', 'date_format:Y-m-d', new NoXSSInput()],
    //         'end_date' => ['nullable', 'date_format:Y-m-d', new NoXSSInput()],
    //         'date_of_birth' => ['required', 'date_format:Y-m-d', new NoXSSInput()],

    //         'employee_name' => [
    //             'required',
    //             'string',
    //             'max:255',
    //             Rule::unique('employees_tables', 'employee_name')->ignore($user->Employee->id),
    //             new NoXSSInput()
    //         ],

    //         'grading_id' => ['nullable', 'exists:grading,id', new NoXSSInput()],
    //         'group_id' => ['nullable', 'exists:groups_tables,id', new NoXSSInput()],
    //         'bpjs_kes' => ['required', 'string', 'max:255'],
    //         'blood_type' => ['nullable', 'string', 'max:255'],
    //         'bpjs_ket' => ['required', 'string', 'max:255'],
    //         'email' => ['required', 'string', 'max:255', 'not_regex:/[\r\n]/'],
    //         'company_email' => ['nullable', 'string', 'max:255', 'not_regex:/[\r\n]/',],
    //         'emergency_contact_name' => ['required', 'string', 'max:255', new NoXSSInput()],
    //         'marriage' => ['required', 'string', 'max:255', new NoXSSInput()],
    //         'notes' => ['nullable', 'string', 'max:255', new NoXSSInput()],
    //         'child' => ['required', 'string', 'max:255', new NoXSSInput()],
    //         'gender' => ['required', 'string', 'max:255', new NoXSSInput()],
    //         'telp_number' => [
    //             'required',
    //             'numeric',
    //             'digits_between:10,13',
    //             Rule::unique('employees_tables', 'telp_number')->ignore($user->Employee->id),
    //             new NoXSSInput()
    //         ],
    //         'status_employee' => ['required', 'string', 'max:255', new NoXSSInput()],
    //         'nik' => [
    //             'required',
    //             'max:20',
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
    //             'required',
    //             'string',
    //             'max:50',
    //             Rule::unique('employees_tables', 'pin')->ignore($user->Employee->id),
    //             new NoXSSInput()
    //         ],
    //         // 'position_id' => ['nullable', 'exists:position_tables,id', new NoXSSInput()],
    //         // 'store_id' => ['nullable', 'exists:stores_tables,id', new NoXSSInput()],
    //         'company_id' => ['nullable', 'exists:company_tables,id', new NoXSSInput()],
    //         // 'department_id' => ['nullable', 'exists:departments_tables,id', new NoXSSInput()],
    //         'banks_id' => ['required', 'exists:banks_tables,id', new NoXSSInput()],
    //     ]);
    //     /*
    // |--------------------------------------------------------------------------
    // | HELPER: Upload file ke S3 dengan aman
    // |--------------------------------------------------------------------------
    // */
    //     $uploadToS3 = function ($file, string $safeName, string $suffix, string $folder) {
    //         $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    //         $ext = strtolower($file->getClientOriginalExtension());

    //         if (!in_array($ext, $allowedExtensions)) {
    //             abort(400, 'File type not allowed');
    //         }

    //         $fileName = $safeName . '-' . now()->timestamp . '-' . $suffix . '.' . $ext;

    //         Storage::disk('s3')->putFileAs($folder, $file, $fileName);

    //         return $folder . '/' . $fileName;
    //     };

    //     // $filePath = $user->Employee->photos;
    //     $safeName = Str::slug($request->employee_name);
    //     $employee = $user->Employee;

    //     // Simpan path lama untuk rollback jika gagal
    //     $oldPaths = [
    //         'photos'         => $employee->photos,
    //         'kk_photos'      => $employee->kk_photos,
    //         'ktp_photos'     => $employee->ktp_photos,
    //         'signature'      => $employee->signature,
    //     ];

    //     // Path baru hasil upload (untuk rollback jika DB gagal)
    //     $newPaths = [];

    //     /*
    // |--------------------------------------------------------------------------
    // | PHOTO
    // |--------------------------------------------------------------------------
    // */
    //     if ($request->hasFile('photos')) {
    //         $newPaths['photos'] = $uploadToS3(
    //             $request->file('photos'),
    //             $safeName,
    //             'photos',
    //             'employees-photos'
    //         );
    //         $validatedData['photos'] = $newPaths['photos'];
    //     }

    //     /*
    // |--------------------------------------------------------------------------
    // | KK PHOTO
    // |--------------------------------------------------------------------------
    // */
    //     if ($request->hasFile('kk_photos')) {
    //         $newPaths['kk_photos'] = $uploadToS3(
    //             $request->file('kk_photos'),
    //             $safeName,
    //             'kk',
    //             'employees-kk-photos'
    //         );
    //         $validatedData['kk_photos'] = $newPaths['kk_photos'];
    //     }

    //     /*
    // |--------------------------------------------------------------------------
    // | KTP PHOTO
    // |--------------------------------------------------------------------------
    // */
    //     if ($request->hasFile('ktp_photos')) {
    //         $newPaths['ktp_photos'] = $uploadToS3(
    //             $request->file('ktp_photos'),
    //             $safeName,
    //             'ktp',
    //             'employees-ktp-photos'
    //         );
    //         $validatedData['ktp_photos'] = $newPaths['ktp_photos'];
    //     }

    //     /*
    // |--------------------------------------------------------------------------
    // | SIGNATURE FILE
    // |--------------------------------------------------------------------------
    // */
    //     if ($request->hasFile('signature_file')) {
    //         $newPaths['signature'] = $uploadToS3(
    //             $request->file('signature_file'),
    //             $safeName,
    //             'signature',
    //             'employees-signatures-photos'
    //         );
    //         $validatedData['signature'] = $newPaths['signature'];
    //     }

    //     try {
    //         // DB::transaction(function () use ($user, &$validatedData, $request, &$filePath) {
    //         DB::transaction(function () use ($user, &$validatedData, $oldPaths, $newPaths) {
    //             /** --------------------------
    //              *  Lock employee row
    //              * -------------------------*/
    //             $employee = $user->Employee()->lockForUpdate()->first();
    //             $oldStructureId = $employee->structure_id;
    //             $statusEmployee = $validatedData['status'];
    //             $inactiveStatus = ['Resign', 'On Leave'];
    //             /** --------------------------
    //              *  Handle Status Non-Aktif
    //              * -------------------------*/
    //             if (in_array($statusEmployee, $inactiveStatus)) {
    //                 $validatedData['structure_id'] = null;
    //                 if ($oldStructureId) {
    //                     Structuresnew::where('id', $oldStructureId)
    //                         ->lockForUpdate()
    //                         ->update(['status' => 'vacant']);
    //                 }
    //             }
    //             /** --------------------------
    //              *  Handle Structure Baru
    //              * -------------------------*/
    //             if (!empty($validatedData['structure_id'])) {
    //                 $newStructure = Structuresnew::with('submissionposition')
    //                     ->where('id', $validatedData['structure_id'])
    //                     ->lockForUpdate()
    //                     ->first();
    //                 if ($newStructure && $newStructure->submissionposition) {
    //                     $submission = $newStructure->submissionposition;
    //                     $validatedData['company_id'] = $submission->company_id;
    //                     // $validatedData['department_id'] = $submission->department_id;
    //                     // if (empty($validatedData['store_id'])) {
    //                     //     $validatedData['store_id'] = $submission->store_id;
    //                     // }

    //                     // $validatedData['position_id'] = $submission->position_id;
    //                     if (empty($validatedData['position_id'])) {
    //                         $validatedData['position_id'] = $submission->position_id;
    //                     }

    //                     $validatedData['is_manager'] = $submission->is_manager;
    //                 }
    //                 $newStructure->update(['status' => 'active']);
    //             }
    //             $employee->update($validatedData);
    //             /*
    //         |--------------------------------------------------------------------------
    //         | Hapus file lama di S3 setelah DB berhasil update
    //         |--------------------------------------------------------------------------
    //         */
    //             $fileMap = [
    //                 'photos'    => $oldPaths['photos'],
    //                 'kk_photos' => $oldPaths['kk_photos'],
    //                 'ktp_photos' => $oldPaths['ktp_photos'],
    //                 'signature' => $oldPaths['signature'],
    //             ];

    //             foreach ($fileMap as $key => $oldPath) {
    //                 if (isset($newPaths[$key]) && $oldPath && Storage::disk('s3')->exists($oldPath)) {
    //                     Storage::disk('s3')->delete($oldPath);
    //                 }
    //             }
    //             if ($oldStructureId && $oldStructureId != ($validatedData['structure_id'] ?? null)) {
    //                 Structuresnew::where('id', $oldStructureId)
    //                     ->lockForUpdate()
    //                     ->update(['status' => 'vacant']);
    //             }
    //         });

    //         return redirect()->route('pages.Employee')->with('success', 'Employee Updated Successfully.');
    //     } catch (\Throwable $th) {

    //         foreach ($newPaths as $path) {
    //             if ($path && Storage::disk('s3')->exists($path)) {
    //                 Storage::disk('s3')->delete($path);
    //             }
    //         }

    //         Log::error('Employee update failed', [
    //             'error'       => $th->getMessage(),
    //             'employee_id' => $user->Employee->id ?? null,
    //         ]);

    //         return redirect()->route('pages.Employee')
    //             ->with('error', 'Update failed: ' . $th->getMessage());
    //     }
    // }