<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Company;
use App\Models\Stores;
use App\Models\User;
use App\Models\Departments;
use App\Models\EmployeeOvertimeRate;
use App\Models\Grading;
use App\Models\Groups;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use Yajra\DataTables\DataTables;



class OvertimeRateController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        if (!$user->hasPermissionTo('ManageOvertimeRate')) {
            abort(403, 'Unauthorized');
        }
        // ManageEmployee: semua data
        $stores       = Stores::orderBy('name')->get();
           $companies        = Company::pluck('name', 'id');

        $departments  = Departments::get();
        $gradings     = Grading::pluck('grading_name', 'id');
        $groups       = Groups::pluck('remark', 'id');
    $employeestatuses = Employee::getStatusEmployeeOptions();
    $statuses         = Employee::getStatusOptions();


        return view('pages.overtime-rate.index', compact(
            'companies',
            'stores',
            'departments',
            'employeestatuses',
            'statuses',
            'gradings',
            'groups'
        ));
    }

    // public function getOvertimeRates(Request $request)
    // {
    //     /** @var \App\Models\User|null $user */
    //     $user = auth()->user();

    //     $canManage     = $user->hasPermissionTo('ManageOvertimeRate');

    //     if (!$canManage) {
    //         abort(403);
    //     }
    //     // ← with berbeda berdasarkan permission
    //     if ($canManage) {
    //         $withRelations = [
    //             'employee',
    //             'employee.position'   => fn($q) => $q->wherePivot('is_primary', true),
    //             'employee.department' => fn($q) => $q->wherePivot('is_primary', true),
    //             'employee.grading',
    //             'employee.group',
    //             'employee.store'      => fn($q) => $q->wherePivot('is_primary', true),
    //             'employee.company'
    //         ];
    //     }

    //     $query = User::query()
    //         ->with($withRelations)
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
    //         ->leftJoin('departments_tables', 'departments_tables.id', '=', 'employee_departments.department_id');

    //     if ($canManage) {
    //         $query->select([
    //             'users.*',
    //             'employees_tables.employee_name',
    //             'employees_tables.employee_pengenal',
    //             'employees_tables.status_employee',
    //             'employees_tables.status',
    //             'position_tables.name as position_name',
    //             'groups_tables.remark as remark',
    //             'stores_tables.name as store_name',
    //             'departments_tables.department_name',
    //             'grading.grading_name',
    //             'company_tables.name as name_company',
    //         ]);
    //     } else {
    //         $query->select([
    //             'users.id',
    //             'users.employee_id',
    //             'employees_tables.employee_name',
    //             'employees_tables.employee_pengenal',
    //             'employees_tables.status',
    //             'position_tables.name as position_name',
    //             'stores_tables.name as store_name',
    //             'departments_tables.department_name',
    //             'company_tables.name as name_company',
    //         ]);
    //     }
    //     if (!$canManage) {
    //         $employee  = $user->employee;
    //         $companyId = $employee->company_id;
    //     }
    //     if ($canManage) {
    //         $query->when($request->filled('filter_group'), fn($q) => $q->where('groups_tables.remark', $request->filter_group));
    //         $query->when($request->filled('filter_grading'), fn($q) => $q->where('grading.grading_name', $request->filter_grading));
    //         $query->when($request->filled('filter_emp_status'), fn($q) => $q->where('employees_tables.status_employee', $request->filter_emp_status));
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
    //                 if ($date) $q->where('employees_tables.join_date', '<=', $date);
    //             }
    //         });
    //     }
    //     $query->when($request->filled('filter_company'), fn($q) => $q->where('company_tables.name', $request->filter_company));
    //     $query->when($request->filled('filter_department'), fn($q) => $q->where('departments_tables.department_name', $request->filter_department));
    //     $query->when($request->filled('filter_store'), fn($q) => $q->where('stores_tables.name', $request->filter_store));
    //     $query->when($request->filled('filter_status'), fn($q) => $q->where('employees_tables.status', $request->filter_status));
    //     $dt = DataTables::of($query);
    //     $dt->filterColumn('employee_name', function ($q, $k) {
    //         $keywords = array_filter(explode(' ', trim($k)));
    //         $q->where(function ($sub) use ($keywords) {
    //             foreach ($keywords as $word) {
    //                 $sub->where('employees_tables.employee_name', 'like', "%{$word}%");
    //             }
    //         });
    //     })
    //         ->filterColumn('employee_pengenal', fn($q, $k) => $q->where('employees_tables.employee_pengenal', 'like', "%$k%"))
    //         ->filterColumn('position_name', fn($q, $k) => $q->where('position_tables.name', 'like', "%$k%"))
    //         ->filterColumn('department_name', fn($q, $k) => $q->where('departments_tables.department_name', 'like', "%$k%"))
    //         ->filterColumn('store_name', fn($q, $k) => $q->where('stores_tables.name', 'like', "%$k%"))
    //         ->filterColumn('name_company', fn($q, $k) => $q->where('company_tables.name', 'like', "%$k%"))
    //         ->filterColumn('status', fn($q, $k) => $q->where('employees_tables.status', 'like', "%$k%"));
    //     if ($canManage) {
    //         $dt->filterColumn('remark', fn($q, $k) => $q->where('groups_tables.remark', 'like', "%$k%"))
    //             ->filterColumn('grading_name', fn($q, $k) => $q->where('grading.grading_name', 'like', "%$k%"))
    //             ->filterColumn('status_employee', fn($q, $k) => $q->where('employees_tables.status_employee', 'like', "%$k%"));
    //     }
    //     return $dt->rawColumns(['action'])->make(true);
    // }
    public function getOvertimeRates(Request $request)
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();
    $canManage = $user->hasPermissionTo('ManageOvertimeRate');

    if (!$canManage) abort(403);

    $withRelations = [
        'employee.position'   => fn($q) => $q->wherePivot('is_primary', true),
        'employee.department' => fn($q) => $q->wherePivot('is_primary', true),
        'employee.grading',
        'employee.group',
        'employee.store'      => fn($q) => $q->wherePivot('is_primary', true),
        'employee.company',
        'employee.overtimeRate', // ← relasi rate
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
        ->leftJoin('departments_tables', 'departments_tables.id', '=', 'employee_departments.department_id')
        // ← Join overtime rate
        ->leftJoin('employee_overtime_rates', 'employee_overtime_rates.employee_id', '=', 'employees_tables.id')
        ->select([
            'users.*',
            'employees_tables.employee_name',
            'employees_tables.employee_pengenal',
            'employees_tables.status_employee',
            'employees_tables.status',
            'position_tables.name as position_name',
            'groups_tables.remark as remark',
            'stores_tables.name as store_name',
            'departments_tables.department_name',
            'grading.grading_name',
            'company_tables.name as name_company',
            'employee_overtime_rates.rate_per_hour', // ← tambah ini
            'employee_overtime_rates.id as rate_id', // ← untuk update
        ]);

    // ── Filters ──
    $query->when($request->filled('filter_group'),      fn($q) => $q->where('groups_tables.remark', $request->filter_group));
    $query->when($request->filled('filter_grading'),    fn($q) => $q->where('grading.grading_name', $request->filter_grading));
    $query->when($request->filled('filter_emp_status'), fn($q) => $q->where('employees_tables.status_employee', $request->filter_emp_status));
    $query->when($request->filled('filter_company'),    fn($q) => $q->where('company_tables.name', $request->filter_company));
    $query->when($request->filled('filter_department'), fn($q) => $q->where('departments_tables.department_name', $request->filter_department));
    $query->when($request->filled('filter_store'),      fn($q) => $q->where('stores_tables.name', $request->filter_store));
    $query->when($request->filled('filter_status'),     fn($q) => $q->where('employees_tables.status', $request->filter_status));

    return DataTables::of($query)
        ->addColumn('rate_display', fn($row) =>
            $row->rate_per_hour
                ? 'Rp ' . number_format($row->rate_per_hour, 0, ',', '.')
                : '<span class="text-muted">Belum diset</span>'
        )
        ->addColumn('action', fn($row) =>
            '<button class="btn btn-sm btn-warning btn-edit-rate"
                data-employee-id="' . $row->employee_id . '"
                data-employee-name="' . htmlspecialchars($row->employee_name) . '"
                data-rate="' . ($row->rate_per_hour ?? '') . '"
                data-rate-id="' . ($row->rate_id ?? '') . '">
                <i class="fas fa-edit"></i>
            </button>'
        )
        ->filterColumn('employee_name',   fn($q, $k) => $q->where('employees_tables.employee_name', 'like', "%$k%"))
        ->filterColumn('employee_pengenal', fn($q, $k) => $q->where('employees_tables.employee_pengenal', 'like', "%$k%"))
        ->filterColumn('position_name',   fn($q, $k) => $q->where('position_tables.name', 'like', "%$k%"))
        ->filterColumn('department_name', fn($q, $k) => $q->where('departments_tables.department_name', 'like', "%$k%"))
        ->filterColumn('store_name',      fn($q, $k) => $q->where('stores_tables.name', 'like', "%$k%"))
        ->filterColumn('name_company',    fn($q, $k) => $q->where('company_tables.name', 'like', "%$k%"))
        ->filterColumn('remark',          fn($q, $k) => $q->where('groups_tables.remark', 'like', "%$k%"))
        ->filterColumn('grading_name',    fn($q, $k) => $q->where('grading.grading_name', 'like', "%$k%"))
        ->filterColumn('status_employee', fn($q, $k) => $q->where('employees_tables.status_employee', 'like', "%$k%"))
            ->filterColumn('status', fn($q, $k) => $q->where('employees_tables.status', 'like', "%$k%"))

        ->rawColumns(['rate_display', 'action'])
        ->make(true);
}
// public function store(Request $request)
// {
//     /** @var \App\Models\User|null $user */
//     $user = auth()->user();
//     if (!$user->hasPermissionTo('ManageOvertimeRate')) abort(403);

//     $request->validate([
//         'employee_ids'   => 'required|array|min:1',
//         'employee_ids.*' => 'exists:employees_tables,id',
//         'rate_per_hour'  => 'required|numeric|min:0',
//     ]);

//     $count = 0;
//     foreach ($request->employee_ids as $empId) {
//         EmployeeOvertimeRate::updateOrCreate(
//             ['employee_id' => $empId],
//             ['rate_per_hour' => $request->rate_per_hour]
//         );
//         $count++;
//     }

//     return response()->json([
//         'success' => true,
//         'message' => "Rate berhasil diset untuk {$count} karyawan.",
//     ]);
// }
// public function store(Request $request)
// {
//     /** @var \App\Models\User|null $user */
//     $user = auth()->user();
//     if (!$user->hasPermissionTo('ManageOvertimeRate')) abort(403);

//     Log::info('OvertimeRate store dipanggil', [
//         'user_id'      => $user->id,
//         'request_all'  => $request->all(),
//     ]);

//     $request->validate([
//         'employee_ids'   => 'required|array|min:1',
//         'employee_ids.*' => 'exists:employees_tables,id',
//         'rate_per_hour'  => 'required|numeric|min:0',
//     ]);

//     Log::info('OvertimeRate validasi passed', [
//         'employee_ids'  => $request->employee_ids,
//         'rate_per_hour' => $request->rate_per_hour,
//     ]);

//     $count = 0;
//     foreach ($request->employee_ids as $empId) {
//         $result = EmployeeOvertimeRate::updateOrCreate(
//             ['employee_id' => $empId],
//             ['rate_per_hour' => $request->rate_per_hour]
//         );

//         Log::info('OvertimeRate upsert', [
//             'employee_id'    => $empId,
//             'rate_per_hour'  => $request->rate_per_hour,
//             'wasRecentlyCreated' => $result->wasRecentlyCreated,
//             'result_id'      => $result->id,
//         ]);

//         $count++;
//     }

//     Log::info('OvertimeRate store selesai', ['count' => $count]);

//     return response()->json([
//         'success' => true,
//         'message' => "Rate berhasil diset untuk {$count} karyawan.",
//     ]);
// }
public function store(Request $request)
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();
    if (!$user->hasPermissionTo('ManageOvertimeRate')) abort(403);

    $request->validate([
        'employee_ids'   => 'required|array|min:1',
        'employee_ids.*' => 'exists:employees_tables,id',
        'rate_per_hour'  => 'required|numeric|min:0',
    ]);

    $count = 0;
    foreach ($request->employee_ids as $empId) {
        EmployeeOvertimeRate::updateOrCreate(
            ['employee_id' => $empId],
            ['rate_per_hour' => $request->rate_per_hour]
        );
        $count++;
    }

    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'message' => "Rate berhasil diset untuk {$count} karyawan.",
        ]);
    }

    return redirect()->route('overtime-rate.index')
        ->with('success', "Rate berhasil diset untuk {$count} karyawan.");
}
}
