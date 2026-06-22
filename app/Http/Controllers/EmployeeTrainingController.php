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
use App\Exports\EmployeesTrainingExport;
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

class EmployeeTrainingController extends Controller
{
     public function index()
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();

    if (!$user->hasPermissionTo('ManageEmployeeTraining')) {
        abort(403);
    }

    $companies      = Company::pluck('name', 'id');
    $stores         = Stores::get();
    $departments    = Departments::get();
    $statuses       = Employee::getStatusOptions();

    return view('pages.employee-training.index', compact(
        'companies',
        'stores',
        'departments',
        'statuses'
    ));
}
   public function getEmployeeTrainings(Request $request)
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();

    $canManageTraining = $user->hasPermissionTo('ManageEmployeeTraining');

    if (!$canManageTraining) {
        abort(403);
    }

    $withRelations = [
        'employee' => fn($q) => $q->select('id', 'employee_name', 'employee_pengenal', 'status', 'company_id'),
        'employee.position' => fn($q) => $q->wherePivot('is_primary', true)->select('position_tables.id', 'position_tables.name'),
        'employee.department' => fn($q) => $q->wherePivot('is_primary', true)->select('departments_tables.id', 'departments_tables.department_name'),
        'employee.store' => fn($q) => $q->wherePivot('is_primary', true)->select('stores_tables.id', 'stores_tables.name'),
        'employee.company' => fn($q) => $q->select('id', 'name'),
    ];

    $query = User::query()
        ->with($withRelations)
        ->leftJoin('employees_tables', 'users.employee_id', '=', 'employees_tables.id')
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

    $query->select([
        'users.id',
        'users.employee_id',
        'employees_tables.employee_name',
        'employees_tables.employee_pengenal',
        'employees_tables.status',
        'employees_tables.telp_number',
        'position_tables.name as position_name',
        'stores_tables.name as store_name',
        'departments_tables.department_name',
        'company_tables.name as name_company',
    ]);

    // ← Tidak ada filter company/store/department, tampilkan semua
    $query->when($request->filled('filter_company'), fn($q) => $q->where('company_tables.name', $request->filter_company));
    $query->when($request->filled('filter_department'), fn($q) => $q->where('departments_tables.department_name', $request->filter_department));
    $query->when($request->filled('filter_store'), fn($q) => $q->where('stores_tables.name', $request->filter_store));
    $query->when($request->filled('filter_status'), fn($q) => $q->where('employees_tables.status', $request->filter_status));

    $sensitiveFields = [
        'nik', 'email', 'bank_account_number', 'bpjs_kes', 'bpjs_ket',
        'npwp', 'pin', 'date_of_birth', 'place_of_birth', 'biological_mother_name',
        'current_address', 'id_card_address', 'marriage', 'child', 'gender',
        'religion', 'last_education', 'institution', 'kk_photos', 'ktp_photos',
        'signature', 'join_date', 'end_date', 'can_approve', 'grading_id',
        'banks_id', 'structure_id', 'group_id', 'level_id', 'is_manager',
        'is_manager_store', 'photos', 'remaining', 'approved', 'pending',
        'total', 'position_id', 'store_id', 'department_id',
    ];

    $dt = DataTables::of($query);

    $dt->addColumn('employee', function ($row) use ($sensitiveFields) {
        if (!$row->employee) return null;
        return $row->employee->makeHidden($sensitiveFields);
    });

    // $dt->filterColumn('employee_name', fn($q, $k) => $q->where('employees_tables.employee_name', 'like', "%$k%"))
    $dt->filterColumn('employee_name', function ($q, $k) {
    $keywords = array_filter(explode(' ', trim($k)));
    $q->where(function ($sub) use ($keywords) {
        foreach ($keywords as $word) {
            $sub->where('employees_tables.employee_name', 'like', "%{$word}%");
        }
    });
})
        ->filterColumn('employee_pengenal', fn($q, $k) => $q->where('employees_tables.employee_pengenal', 'like', "%$k%"))
        ->filterColumn('position_name', fn($q, $k) => $q->where('position_tables.name', 'like', "%$k%"))
        ->filterColumn('department_name', fn($q, $k) => $q->where('departments_tables.department_name', 'like', "%$k%"))
        ->filterColumn('store_name', fn($q, $k) => $q->where('stores_tables.name', 'like', "%$k%"))
        ->filterColumn('name_company', fn($q, $k) => $q->where('company_tables.name', 'like', "%$k%"))
        ->filterColumn('status', fn($q, $k) => $q->where('employees_tables.status', 'like', "%$k%"))
        ->filterColumn('telp_number', fn($q, $k) => $q->where('employees_tables.telp_number', 'like', "%$k%"));

    return $dt->make(true);
}
    public function exportTraingingEmployees(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        if (!$user->hasPermissionTo('ManageEmployeeTraining')) {
            abort(403);
        }
        // ✅ Ambil manual dari query string
        $filters = [
            'filter_company'    => $request->query('filter_company'),
            'filter_department' => $request->query('filter_department'),
            'filter_store'      => $request->query('filter_store'),
            'filter_status'     => $request->query('filter_status'),
             ];

        // dd($filters); // cek dulu, hapus setelah confirmed

        $fileName = 'employees_manage_training_' . Carbon::now()->format('Ymd_His');

        if ($request->query('type') === 'csv') {
            return Excel::download(new EmployeesTrainingExport($filters), $fileName . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download(new EmployeesTrainingExport($filters), $fileName . '.xlsx');
    }
}
