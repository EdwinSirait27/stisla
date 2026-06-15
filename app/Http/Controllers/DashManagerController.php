<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Position;
use App\Models\Company;
use App\Models\Announcement;
use App\Models\Employee;
use App\Models\Departments;
use App\Models\Stores;
use App\Models\Leavebalance;
use App\Models\Fingerprints;
use App\Models\Leaverequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Banks;
use App\Models\Grading;
use Yajra\DataTables\DataTables;

class DashManagerController extends Controller
{
    public function indexteam()
    {
        $announcements = Announcement::with('user')
            ->orderBy('publish_date', 'desc')
            ->paginate(10);

        $employee   = Auth::user()->employee;
        $companyId  = $employee->company_id;

        // Pakai primaryStore() dari Employee.php
        $storeIds = $employee->primaryStore()->pluck('stores_tables.id')->toArray();
        if (empty($storeIds)) {
            $storeIds = [$employee->store_id];
        }

        // Pakai primaryDepartment() dari Employee.php
        $departmentIds = $employee->primaryDepartment()->pluck('departments_tables.id')->toArray();
        $departmentId  = $departmentIds[0] ?? $employee->department_id;

        $totalEmployees = Employee::whereIn('status', ['Active', 'Pending'])
            ->where('company_id', $companyId)
            ->whereIn('store_id', $storeIds)
            ->count();

        $totalEmployeespending = Employee::where('status', 'Pending')
            ->where('company_id', $companyId)
            ->whereIn('store_id', $storeIds)
            ->count();

        $employeesPins = Employee::where('company_id', $companyId)
            ->whereIn('store_id', $storeIds)
            ->pluck('pin')
            ->toArray();

        $today = now()->format('Y-m-d');

        $presentToday = Fingerprints::whereDate('scan_date', $today)
            ->whereIn('inoutmode', [1])
            ->whereIn('pin', $employeesPins)
            ->distinct('pin')
            ->count('pin');

        $absentToday = count($employeesPins) - $presentToday;

        $submissions = \App\Models\Overtimesubmissions::with('employees:id,employee_name')
            ->where('approver_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('pages.dashboardTeam.dashboardTeam', compact(
            'absentToday',
            'presentToday',
            'announcements',
            'totalEmployees',
            'totalEmployeespending',
            'submissions'
        ));
    }

    // public function index(Request $request)
    // {
    //     $announcements = Announcement::with('user')
    //         ->orderBy('publish_date', 'desc')
    //         ->paginate(10);

    //     $employee = Auth::user()->employee;
    //     $companyId = $employee->company_id;
    //     $departmentId = $employee->department_id;

    //     // Ambil multi store user
    //     $multiStores = $employee->structureNew?->stores?->pluck('id')->toArray();
    //     $storeIds = !empty($multiStores) ? $multiStores : [$employee->store_id];

    //     // Hitung total karyawan
    //     $totalEmployees = Employee::whereIn('status', ['Active', 'Pending'])
    //         ->where('company_id', $companyId)
    //         ->where('department_id', $departmentId)
    //         ->whereIn('store_id', $storeIds)
    //         ->count();

    //     $totalEmployeespending = Employee::where('status', 'Pending')
    //         ->where('company_id', $companyId)
    //         ->where('department_id', $departmentId)
    //         ->whereIn('store_id', $storeIds)
    //         ->count();

    //     $employeeLogin = auth()->user()->employee;
    //     $pin = $employeeLogin->pin;
    //     $today = now()->format('Y-m-d');
    //     $lastWeek = now()->subDays(7)->format('Y-m-d');

    //     $presentCount = Fingerprints::whereBetween('scan_date', [$lastWeek, $today])
    //         ->where('inoutmode', 1)
    //         ->where('pin', $pin)
    //         ->distinct('scan_date')
    //         ->count();

    //     $employeelogin = $employee->id;
    //     $leavebalance = Leavebalance::with(['employees', 'leaves'])
    //         ->where('employee_id', $employeelogin)
    //         ->get();

    //     // ─── Annual Leave untuk card Leave Balance (manager sendiri) ───
    //     $annualLeave = Leavebalance::with('leaves')
    //         ->where('employee_id', $employee->id)
    //         ->where('year', date('Y'))
    //         ->whereHas('leaves', fn($q) => $q->where('name', 'Annual Leave'))
    //         ->first();

    //     $isNewbie = $employee->join_date
    //         ? \Carbon\Carbon::parse($employee->join_date)->diffInYears(now()) < 1
    //         : false;

    //     // Total hari pengajuan PENDING milik manager sendiri
    //     $myPendingDays = Leaverequest::whereHas('leavebalance', fn($q) =>
    //     $q->where('employee_id', $employee->id))
    //         ->where('status', 'Pending')
    //         ->sum('total_days');

    //     // Saldo tampil = balance_days − hari pending
    //     $displayBalance = $annualLeave
    //         ? max(0, (float) $annualLeave->balance_days - (float) $myPendingDays)
    //         : 0;

    //     // Ambil pending leave dari bawahan berdasarkan tree struktur
    //     $myStructureId           = $employee->structure_id;
    //     $subordinateStructureIds = $myStructureId
    //         ? $this->getSubStructureIds($myStructureId)
    //         : [];

    //     $subordinateEmployeeIds = Employee::whereIn('structure_id', $subordinateStructureIds)
    //         ->pluck('id')
    //         ->toArray();

    //     $subordinateBalanceIds = Leavebalance::whereIn('employee_id', $subordinateEmployeeIds)
    //         ->pluck('id')
    //         ->toArray();

    //     // ─── Query $pendingLeaves sebelum di-map ───
    //     $pendingLeaves = Leaverequest::whereIn('leave_balance_id', $subordinateBalanceIds)
    //         ->where('status', 'Pending')
    //         ->with(['leavebalance.employees', 'leavebalance.leaves'])
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     $colors = ['#4CAF50', '#FF9800', '#2196F3', '#9C27B0', '#F44336', '#00BCD4'];
    //     $typeMap = [
    //         'sakit'      => ['bg' => '#FFF3CD', 'text' => '#856404'],
    //         'tahunan'    => ['bg' => '#D1ECF1', 'text' => '#0C5460'],
    //         'annual'     => ['bg' => '#D1ECF1', 'text' => '#0C5460'],
    //         'melahirkan' => ['bg' => '#F8D7DA', 'text' => '#721C24'],
    //         'darurat'    => ['bg' => '#F8D7DA', 'text' => '#721C24'],
    //         'toil'       => ['bg' => '#E2D9F3', 'text' => '#4A235A'],
    //     ];

    //     $pendingLeaves = $pendingLeaves->map(function ($leave) use ($colors, $typeMap) {
    //         $balance   = $leave->leavebalance;
    //         $employee  = $balance?->employees;
    //         $leaveType = $balance?->leaves;

    //         $employeeName  = $employee?->employee_name ?? $employee?->name ?? '-';
    //         $leaveTypeName = $leaveType?->name ?? $leaveType?->leave_type_name ?? 'Cuti';

    //         $bgColor = $colors[abs(crc32($employeeName)) % count($colors)];
    //         $initial = strtoupper(substr($employeeName, 0, 1));

    //         $key       = strtolower($leaveTypeName);
    //         $typeStyle = collect($typeMap)->first(fn($v, $k) => str_contains($key, $k))
    //             ?? ['bg' => '#E2E3E5', 'text' => '#383D41'];

    //         $start = \Carbon\Carbon::parse($leave->start_date);
    //         $dateLabel = $start->format('d M Y');
    //         if ($leave->end_date && $leave->end_date !== $leave->start_date) {
    //             $dateLabel .= ' – ' . \Carbon\Carbon::parse($leave->end_date)->format('d M Y');
    //         }

    //         return [
    //             'id'             => $leave->id,
    //             'employeeName'   => $employeeName,
    //             'leaveTypeName'  => $leaveTypeName,
    //             'bgColor'        => $bgColor,
    //             'initial'        => $initial,
    //             'typeBg'         => $typeStyle['bg'],
    //             'typeText'       => $typeStyle['text'],
    //             'dateLabel'      => $dateLabel,
    //             'ago'            => \Carbon\Carbon::parse($leave->created_at)->diffForHumans(),
    //             'employeeReason' => $leave->employee_reason,
    //             'approveUrl'     => route('leaverequest.approve', $leave->id),
    //             'rejectUrl'      => route('leaverequest.reject', $leave->id),
    //         ];
    //     });

    //     // ─── Roster manager (yang login) untuk Attendance History ───
    //     $employeeId = $employee->id;

    //     $month  = (int) $request->query('month', now()->month);
    //     $year   = (int) $request->query('year', now()->year);
    //     $cursor = \Carbon\Carbon::createFromDate($year, $month, 1);

    //     $rosters = \App\Models\Roster::with('shift')
    //         ->where('employee_id', $employeeId)
    //         ->whereYear('date', $year)
    //         ->whereMonth('date', $month)
    //         ->get()
    //         ->keyBy(fn ($r) => \Carbon\Carbon::parse($r->date)->day);

    //     $today     = \Carbon\Carbon::today();
    //     $daysInMon = $cursor->daysInMonth;
    //     $leadBlank = $cursor->copy()->startOfMonth()->dayOfWeek;

    //     $calendarDays = [];

    //     for ($i = 0; $i < $leadBlank; $i++) {
    //         $calendarDays[] = ['empty' => true];
    //     }

    //     for ($d = 1; $d <= $daysInMon; $d++) {
    //         $dateObj = $cursor->copy()->day($d);
    //         $dow     = $dateObj->dayOfWeek;
    //         $roster  = $rosters[$d] ?? null;

    //         if ($roster) {
    //             $type = strtolower($roster->day_type);
    //             $cssClass = match (true) {
    //                 str_contains($type, 'work')    => 'present',
    //                 str_contains($type, 'off')     => 'weekend',
    //                 str_contains($type, 'holiday') => 'leave',
    //                 str_contains($type, 'leave')   => 'absent',
    //                 str_contains($type, 'melahirkan')    => 'absent',
    //                 default                         => '',
    //             };
    //             $label = $roster->day_type;
    //             $remark  = (str_contains($type, 'holiday') || str_contains($type, 'toil')) ? ($roster->notes ?? '') : '';
    //         } else {
    //             $cssClass = in_array($dow, [0, 6]) ? 'weekend' : '';
    //             $label    = '';
    //             $remark   = '';
    //         }

    //         $calendarDays[] = [
    //             'empty'    => false,
    //             'day'      => $d,
    //             'cssClass' => $cssClass,
    //             'label'    => $label,
    //             'remark'   => $remark,
    //             'isToday'  => $dateObj->isSameDay($today),
    //         ];
    //     }

    //     $prev = $cursor->copy()->subMonth();
    //     $next = $cursor->copy()->addMonth();

    //     $calendarLabel = $cursor->translatedFormat('F Y');
    //     $prevMonth     = ['month' => $prev->month, 'year' => $prev->year];
    //     $nextMonth     = ['month' => $next->month, 'year' => $next->year];

    //     // ─── Cabang AJAX untuk navigasi panah (ganti bulan tanpa reload) ───
    //     if ($request->ajax()) {
    //         return view('pages.dashboardManager.calendar', compact(
    //             'calendarDays',
    //             'calendarLabel',
    //             'prevMonth',
    //             'nextMonth',
    //         ));
    //     }

    //     return view('pages.dashboardManager.dashboardManager', compact(
    //         'announcements',
    //         'totalEmployees',
    //         'totalEmployeespending',
    //         'presentCount',
    //         'leavebalance',
    //         'annualLeave',
    //         'isNewbie',
    //         'displayBalance',
    //         'pendingLeaves',
    //         'calendarDays',
    //         'calendarLabel',
    //         'prevMonth',
    //         'nextMonth',
    //     ));
    // }
    public function index(Request $request)
{
    $announcements = Announcement::with('user')
        ->orderBy('publish_date', 'desc')
        ->paginate(10);

    $employee  = Auth::user()->employee;
    $companyId = $employee->company_id;

    // ← Ambil primary store dan department lewat pivot
    $primaryStore      = $employee->primaryStore()->first();
    $primaryDepartment = $employee->primaryDepartment()->first();
    $storeId           = $primaryStore?->id;
    $departmentId      = $primaryDepartment?->id;

    // ← Hitung total karyawan lewat pivot
    $totalEmployees = Employee::whereIn('status', ['Active', 'Pending'])
        ->where('company_id', $companyId)
        ->whereExists(function ($q) use ($storeId) {
            $q->select(\DB::raw(1))
                ->from('employee_stores')
                ->whereColumn('employee_stores.employee_id', 'employees_tables.id')
                ->where('employee_stores.store_id', $storeId);
        })
        ->whereExists(function ($q) use ($departmentId) {
            $q->select(\DB::raw(1))
                ->from('employee_departments')
                ->whereColumn('employee_departments.employee_id', 'employees_tables.id')
                ->where('employee_departments.department_id', $departmentId);
        })
        ->count();

    $totalEmployeespending = Employee::where('status', 'Pending')
        ->where('company_id', $companyId)
        ->whereExists(function ($q) use ($storeId) {
            $q->select(\DB::raw(1))
                ->from('employee_stores')
                ->whereColumn('employee_stores.employee_id', 'employees_tables.id')
                ->where('employee_stores.store_id', $storeId);
        })
        ->whereExists(function ($q) use ($departmentId) {
            $q->select(\DB::raw(1))
                ->from('employee_departments')
                ->whereColumn('employee_departments.employee_id', 'employees_tables.id')
                ->where('employee_departments.department_id', $departmentId);
        })
        ->count();

    $pin           = $employeeLogin->pin;
    $today         = now()->format('Y-m-d');
    $lastWeek      = now()->subDays(7)->format('Y-m-d');


    $presentCount = Fingerprints::whereBetween('scan_date', [$lastWeek, $today])
        ->where('inoutmode', 1)
        ->where('pin', $pin)
        ->distinct('scan_date')
        ->count();

    $employeelogin = $employee->id;
    $leavebalance  = Leavebalance::with(['employees', 'leaves'])
        ->where('employee_id', $employeelogin)
        ->get();

    $annualLeave = Leavebalance::with('leaves')
        ->where('employee_id', $employee->id)
        ->where('year', date('Y'))
        ->whereHas('leaves', fn($q) => $q->where('name', 'Annual Leave'))
        ->first();

    $isNewbie = $employee->join_date
        ? \Carbon\Carbon::parse($employee->join_date)->diffInYears(now()) < 1
        : false;

    $myPendingDays = Leaverequest::whereHas('leavebalance', fn($q) =>
        $q->where('employee_id', $employee->id))
        ->where('status', 'Pending')
        ->sum('total_days');

    $displayBalance = $annualLeave
        ? max(0, (float) $annualLeave->balance_days - (float) $myPendingDays)
        : 0;

    // ← Bawahan lewat pivot atasanList
    $subordinateEmployeeIds = $employee->bawahan()->pluck('id')->toArray();

    $subordinateBalanceIds = Leavebalance::whereIn('employee_id', $subordinateEmployeeIds)
        ->pluck('id')
        ->toArray();

    $pendingLeaves = Leaverequest::whereIn('leave_balance_id', $subordinateBalanceIds)
        ->where('status', 'Pending')
        ->with(['leavebalance.employees', 'leavebalance.leaves'])
        ->orderBy('created_at', 'desc')
        ->get();

    $colors  = ['#4CAF50', '#FF9800', '#2196F3', '#9C27B0', '#F44336', '#00BCD4'];
    $typeMap = [
        'sakit'      => ['bg' => '#FFF3CD', 'text' => '#856404'],
        'tahunan'    => ['bg' => '#D1ECF1', 'text' => '#0C5460'],
        'annual'     => ['bg' => '#D1ECF1', 'text' => '#0C5460'],
        'melahirkan' => ['bg' => '#F8D7DA', 'text' => '#721C24'],
        'darurat'    => ['bg' => '#F8D7DA', 'text' => '#721C24'],
        'toil'       => ['bg' => '#E2D9F3', 'text' => '#4A235A'],
    ];

    $pendingLeaves = $pendingLeaves->map(function ($leave) use ($colors, $typeMap) {
        $balance       = $leave->leavebalance;
        $employee      = $balance?->employees;
        $leaveType     = $balance?->leaves;
        $employeeName  = $employee?->employee_name ?? $employee?->name ?? '-';
        $leaveTypeName = $leaveType?->name ?? $leaveType?->leave_type_name ?? 'Cuti';
        $bgColor       = $colors[abs(crc32($employeeName)) % count($colors)];
        $initial       = strtoupper(substr($employeeName, 0, 1));
        $key           = strtolower($leaveTypeName);
        $typeStyle     = collect($typeMap)->first(fn($v, $k) => str_contains($key, $k))
            ?? ['bg' => '#E2E3E5', 'text' => '#383D41'];

        $start     = \Carbon\Carbon::parse($leave->start_date);
        $dateLabel = $start->format('d M Y');
        if ($leave->end_date && $leave->end_date !== $leave->start_date) {
            $dateLabel .= ' – ' . \Carbon\Carbon::parse($leave->end_date)->format('d M Y');
        }

        return [
            'id'             => $leave->id,
            'employeeName'   => $employeeName,
            'leaveTypeName'  => $leaveTypeName,
            'bgColor'        => $bgColor,
            'initial'        => $initial,
            'typeBg'         => $typeStyle['bg'],
            'typeText'       => $typeStyle['text'],
            'dateLabel'      => $dateLabel,
            'ago'            => \Carbon\Carbon::parse($leave->created_at)->diffForHumans(),
            'employeeReason' => $leave->employee_reason,
            'approveUrl'     => route('leaverequest.approve', $leave->id),
            'rejectUrl'      => route('leaverequest.reject', $leave->id),

        ];
    });


    $employeeId = $employee->id;
    $month      = (int) $request->query('month', now()->month);
    $year       = (int) $request->query('year', now()->year);
    $cursor     = \Carbon\Carbon::createFromDate($year, $month, 1);

    $rosters = \App\Models\Roster::with('shift')
        ->where('employee_id', $employeeId)
        ->whereYear('date', $year)
        ->whereMonth('date', $month)
        ->get()
        ->keyBy(fn($r) => \Carbon\Carbon::parse($r->date)->day);

    $today      = \Carbon\Carbon::today();
    $daysInMon  = $cursor->daysInMonth;
    $leadBlank  = $cursor->copy()->startOfMonth()->dayOfWeek;
    $calendarDays = [];

    for ($i = 0; $i < $leadBlank; $i++) {
        $calendarDays[] = ['empty' => true];
    }

    for ($d = 1; $d <= $daysInMon; $d++) {
        $dateObj = $cursor->copy()->day($d);
        $dow     = $dateObj->dayOfWeek;
        $roster  = $rosters[$d] ?? null;

        if ($roster) {
            $type     = strtolower($roster->day_type);
            $cssClass = match (true) {
                str_contains($type, 'work')      => 'present',
                str_contains($type, 'off')       => 'weekend',
                str_contains($type, 'holiday')   => 'leave',
                str_contains($type, 'leave')     => 'absent',
                str_contains($type, 'melahirkan') => 'absent',
                default                          => '',
            };
            $label  = $roster->day_type;
            $remark = (str_contains($type, 'holiday') || str_contains($type, 'toil'))
                ? ($roster->notes ?? '')
                : '';
        } else {
            $cssClass = in_array($dow, [0, 6]) ? 'weekend' : '';
            $label    = '';
            $remark   = '';

        }

        $calendarDays[] = [
            'empty'    => false,
            'day'      => $d,
            'cssClass' => $cssClass,
            'label'    => $label,
            'remark'   => $remark,
            'isToday'  => $dateObj->isSameDay($today),
        ];
    }

    $prev          = $cursor->copy()->subMonth();
    $next          = $cursor->copy()->addMonth();
    $calendarLabel = $cursor->translatedFormat('F Y');
    $prevMonth     = ['month' => $prev->month, 'year' => $prev->year];
    $nextMonth     = ['month' => $next->month, 'year' => $next->year];

    if ($request->ajax()) {
        return view('pages.dashboardManager.calendar', compact(
            'calendarDays',
            'calendarLabel',
            'prevMonth',
            'nextMonth',
        ));
    }

    return view('pages.dashboardManager.dashboardManager', compact(
        'announcements',
        'totalEmployees',
        'totalEmployeespending',
        'presentCount',
        'leavebalance',
        'annualLeave',
        'isNewbie',
        'displayBalance',
        'pendingLeaves',
        'calendarDays',
        'calendarLabel',
        'prevMonth',
        'nextMonth',
    ));
}

    public function team()
    {
        return view('pages.Team.Team');
    }

    public function getTeams(Request $request, DataTables $dataTables)
    {
        $loggedEmployee = auth()->user()->employee;

        // Pakai bawahanList() dari Employee.php
        $subordinateIds = $loggedEmployee->bawahanList()->pluck('id')->toArray();

        $employees = User::with([
            'Employee.company',
            'Employee.primaryStore',
            'Employee.primaryPosition',
            'Employee.primaryDepartment',
            'Employee.grading',
        ])
            ->whereHas('Employee', function ($q) use ($subordinateIds) {
                $q->whereIn('id', $subordinateIds)
                  ->whereIn('status', ['Active', 'Pending']);
            })
            ->select(['id', 'employee_id'])
            ->get()
            ->map(function ($employee) {
                $employee->id_hashed = substr(
                    hash('sha256', $employee->id . env('APP_KEY')),
                    0,
                    8
                );
                $employeeName       = optional($employee->Employee)->employee_name;
                $employee->action   = '
                    <a href="' . route('Team.show', $employee->id_hashed) . '" class="mx-3"
                       data-bs-toggle="tooltip"
                       title="Show Employee: ' . e($employeeName) . '">
                       <i class="fas fa-eye text-secondary"></i>
                    </a>';
                return $employee;
            });

        return DataTables::of($employees)
            ->addColumn('name_company',      fn ($e) => $e->Employee->company->name ?? 'Empty')
            ->addColumn('grading_name',      fn ($e) => $e->Employee->grading->grading_name ?? 'Empty')
            ->addColumn('name',              fn ($e) => $e->Employee->primaryStore->first()?->name ?? 'Empty')
            ->addColumn('position_name',     fn ($e) => $e->Employee->primaryPosition->first()?->name ?? 'Empty')
            ->addColumn('department_name',   fn ($e) => $e->Employee->primaryDepartment->first()?->department_name ?? 'Empty')
            ->addColumn('status_employee',   fn ($e) => $e->Employee->status_employee ?? 'Empty')
            ->addColumn('employee_name',     fn ($e) => $e->Employee->employee_name ?? 'Empty')
            ->addColumn('employee_pengenal', fn ($e) => $e->Employee->employee_pengenal ?? 'Empty')
            ->addColumn('created_at',        fn ($e) => $e->Employee->created_at ?? 'Empty')
            ->addColumn('status',            fn ($e) => $e->Employee->status ?? 'Empty')
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show($hashedId)
    {
        $employee = User::with([
            'Employee',
            'Employee.store',
            'Employee.grading',
            'Employee.department',
            'Employee.position',
            'Employee.bank',
        ])->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$employee) {
            abort(404, 'Employee not found.');
        }

        $positions       = Position::get();
        $companys        = Company::get();
        $employees       = Employee::where('status', 'Active')->pluck('employee_name', 'id');
        $departments     = Departments::with('user.Employee')->get();
        $stores          = Stores::with('user.Employee')->get();
        $status_employee = ['PKWT', 'DW', 'PKWTT', 'On Job Training'];
        $child           = ['0', '1', '2', '3', '4', '5'];
        $marriage        = ['Yes', 'No'];
        $gender          = ['Male', 'Female', 'MD'];
        $status          = ['Pending', 'Inactive', 'On Leave', 'Mutation', 'Active', 'Resign'];
        $banks           = Banks::get();
        $gradings        = Grading::get();
        $religion        = ['Buddha', 'Catholic Christian', 'Christian', 'Confucian', 'Hindu', 'Islam'];
        $last_education  = [
            'Elementary School', 'Junior High School', 'Senior High School',
            'Diploma I', 'Diploma II', 'Diploma III', 'Diploma IV',
            'Bachelor Degree', 'Masters degree', 'Vocational School',
        ];

        return view('pages.Team.show', compact(
            'employee',
            'employees',
            'status_employee',
            'child',
            'companys',
            'stores',
            'marriage',
            'gender',
            'gradings',
            'status',
            'banks',
            'religion',
            'last_education',
            'positions',
            'departments',
            'hashedId',
        ));
    }

    public function getOrgChartDataTeam()
    {
        $employee = auth()->user()->employee;

        // Pakai bawahanList() dari Employee.php
        $bawahan = $employee->bawahanList()
            ->with(['grading', 'primaryStore', 'primaryPosition'])
            ->get();

        $data = $bawahan->map(fn ($e) => [
            'id'       => $e->id,
            'pid'      => $employee->id,
            'Employee' => $e->employee_name,
            'Position' => $e->primaryPosition()->first()?->name ?? '-',
            'Grading'  => $e->grading?->grading_name ?? '-',
            'Location' => $e->primaryStore()->first()?->name ?? '-',
            'status'   => $e->status,
            'photo'    => $e->photos ?? '/default-avatar.png',
        ]);

        return response()->json($data);
    }

    public function indexteamfingerprint()
    {
        $employee = Auth::user()->employee;

        // Pakai primaryStore() dari Employee.php
        $stores = $employee->primaryStore()->pluck('name', 'stores_tables.id');

        if ($stores->isEmpty()) {
            $stores = Stores::orderBy('name')->pluck('name', 'id');
        }

        return view('pages.Teamfingerprint.Teamfingerprint', compact('stores'));
    }

    public function getTeamfingerprints(Request $request)
    {
        ini_set('memory_limit', '1024M');

        $employeeLogin = auth()->user()->employee;

        // Pakai bawahanList() dari Employee.php
        $subordinateIds = $employeeLogin->bawahanList()->pluck('id')->toArray();

        $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()))->startOfDay();
        $endDate   = Carbon::parse($request->input('end_date', now()))->endOfDay();

        $employeesQuery = Employee::with([
            'primaryStore:id,name',
            'primaryPosition:id,name',
            'primaryDepartment:id,department_name',
        ])
            ->select('pin', 'employee_name', 'employee_pengenal', 'position_id', 'department_id', 'store_id', 'status_employee')
            ->whereNotNull('pin')
            ->whereIn('id', $subordinateIds);

        // Filter store dari dropdown (opsional)
        if ($storeName = $request->input('store_name')) {
            $storeIds = Stores::where('name', $storeName)->pluck('id');
            $employeesQuery->whereIn('store_id', $storeIds);
        }

        $employees = $employeesQuery->get()->keyBy('pin');
        $pinList   = $employees->keys();

        if ($pinList->isEmpty()) {
            return DataTables::of(collect())->make(true);
        }

        $fingerprints = Fingerprints::with('devicefingerprints:device_name,sn')
            ->select(['sn', 'scan_date', 'pin', 'inoutmode'])
            ->whereIn('pin', $pinList)
            ->whereBetween('scan_date', [$startDate, $endDate])
            ->orderBy('scan_date')
            ->get();

        $totalHariPerPin = $fingerprints->groupBy('pin')
            ->map(fn ($items) => $items
                ->pluck('scan_date')
                ->map(fn ($d) => Carbon::parse($d)->toDateString())
                ->unique()
                ->count()
            );

        $grouped = $fingerprints
            ->groupBy(fn ($f) => $f->pin . '_' . Carbon::parse($f->scan_date)->toDateString());

        $result = $grouped->map(function ($group) use ($employees, $totalHariPerPin) {
            $first    = $group->first();
            $pin      = $first->pin;
            $scanDate = Carbon::parse($first->scan_date)->toDateString();

            $employee = $employees[$pin] ?? null;
            if (!$employee) return null;

            $row = [
                'pin'               => $pin,
                'employee_name'     => $employee->employee_name ?? '-',
                'employee_pengenal' => $employee->employee_pengenal ?? '-',
                'status_employee'   => $employee->status_employee ?? '-',
                'name'              => $employee->primaryStore->first()?->name ?? '-',
                'position_name'     => $employee->primaryPosition->first()?->name ?? '-',
                'department_name'   => $employee->primaryDepartment->first()?->department_name ?? '-',
                'device_name'       => $first->devicefingerprints->device_name ?? '-',
                'scan_date'         => $scanDate,
                'total_hari'        => $totalHariPerPin[$pin] ?? 0,
            ];

            for ($i = 1; $i <= 10; $i++) {
                $row["in_$i"] = $row["device_$i"] = $row["combine_$i"] = null;
            }

            $group->groupBy('inoutmode')->each(function ($items, $mode) use (&$row) {
                if ($mode < 1 || $mode > 10) return;

                $sorted  = $items->sortBy('scan_date');
                $times   = $sorted->pluck('scan_date')->map(fn ($d) => Carbon::parse($d)->format('H:i:s'))->implode(', ');
                $devices = $sorted->map(fn ($i) => optional($i->devicefingerprints)->device_name ?? '')->implode(', ');

                $row["in_$mode"]      = $times;
                $row["device_$mode"]  = $devices;
                $row["combine_$mode"] = trim($times . ' ' . $devices);
            });

            $times = collect(range(1, 10))
                ->flatMap(function ($i) use ($row) {
                    if (!$row["in_$i"]) return [];
                    return explode(', ', $row["in_$i"]);
                })
                ->map(fn ($t) => Carbon::parse($t))
                ->sort()
                ->values();

            if ($times->count() >= 2) {
                $start   = $times->first();
                $end     = $times->last();
                $minutes = $start->diffInMinutes($end);
                $row['duration'] = sprintf(
                    '%d hour%s %d minute%s',
                    intdiv($minutes, 60),
                    intdiv($minutes, 60) !== 1 ? 's' : '',
                    $minutes % 60,
                    $minutes % 60 !== 1 ? 's' : ''
                );
            } else {
                $row['duration'] = 'invalid';
            }

            return $row;
        })->filter()->values();

        return DataTables::of($result)->make(true);
    }
}