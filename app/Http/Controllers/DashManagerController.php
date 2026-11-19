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


    public function index(Request $request, \App\Services\EmployeeDashboardData $dashboard, \App\Services\TeamDashboardData $teams)
    {
        // Bagian pribadi: sama persis dengan dashboard karyawan
        $viewData = $dashboard->build($request);
        $viewData['calendarUrl'] = url('/dashboardManager');

        if ($request->ajax()) {
            return view('pages.dashboardHuman._calendar', $viewData);
        }

        $employee = Auth::user()->employee;

        // Bawahan langsung (approval cuti)
        $team = $teams->subordinates($employee);
        $teamToday = $teams->today($team);
        $pending = $teams->pendingLeaves($team->pluck('id')->all());

        // Seluruh karyawan di store utama manager
        $stores = $employee ? $employee->primaryStore()->get(['stores_tables.id', 'stores_tables.name']) : collect();
        $storeToday = $teams->today($teams->storeEmployees($stores->pluck('id')->all()));

        return view('pages.dashboardManager.dashboardManager', array_merge($viewData, [
            'teamToday'     => $teamToday['rows'],
            'teamStats'     => $teamToday['stats'],
            'pendingLeaves' => $pending['items'],
            'pendingCount'  => $pending['count'],
            'stores'        => $stores,
            'storeToday'    => $storeToday['rows'],
            'storeStats'    => $storeToday['stats'],
        ]));
    }

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

        $totalEmployees = Employee::whereIn('status', ['Active', 'Pending','On Leave'])
            ->where('company_id', $companyId)
            ->whereIn('store_id', $storeIds)
            ->whereIn('department_id', $departmentIds)
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

        // ── Pending Leave Approvals dari bawahan (employee_atasans) ──
$subordinateIds = $employee->bawahanList()->pluck('employees_tables.id')->toArray();
$balanceIds = \App\Models\Leavebalance::whereIn('employee_id', $subordinateIds)->pluck('id');

$pendingLeaves = \App\Models\Leaverequest::whereIn('leave_balance_id', $balanceIds)
    ->where('status', 'Pending')
    ->with(['leavebalance.employees', 'leavebalance.leaves'])
    ->latest()
    ->get()
    ->map(function ($leave) {
        $emp       = $leave->leavebalance?->employees;
        $leaveType = $leave->leavebalance?->leaves;
        $start     = \Carbon\Carbon::parse($leave->start_date);
        $end       = \Carbon\Carbon::parse($leave->end_date);
        $dateLabel = $start->isSameDay($end)
            ? $start->format('d M Y')
            : $start->format('d M Y') . ' – ' . $end->format('d M Y');

        return (object) [
            'id'             => $leave->id,
            'employeeName'   => $emp?->employee_name ?? 'Unknown',
            'leaveTypeName'  => $leaveType?->name ?? 'Leave',
            'dateLabel'      => $dateLabel,
            'totalDays'      => $leave->total_days,
            'ago'            => \Carbon\Carbon::parse($leave->created_at)->diffForHumans(),
            'employeeReason' => $leave->employee_reason,
        ];
    });

        return view('pages.dashboardTeam.dashboardTeam', compact(
            'absentToday',
            'presentToday',
            'announcements',
            'totalEmployees',
            'totalEmployeespending',
            'submissions'
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
        $subordinateIds = $loggedEmployee->bawahanList()->pluck('employees_tables.id')->toArray();

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
            ->addColumn('name_company',      fn($e) => $e->Employee->company->name ?? 'Empty')
            ->addColumn('grading_name',      fn($e) => $e->Employee->grading->grading_name ?? 'Empty')
            ->addColumn('name',              fn($e) => $e->Employee->primaryStore->first()?->name ?? 'Empty')
            ->addColumn('position_name',     fn($e) => $e->Employee->primaryPosition->first()?->name ?? 'Empty')
            ->addColumn('department_name',   fn($e) => $e->Employee->primaryDepartment->first()?->department_name ?? 'Empty')
            ->addColumn('status_employee',   fn($e) => $e->Employee->status_employee ?? 'Empty')
            ->addColumn('employee_name',     fn($e) => $e->Employee->employee_name ?? 'Empty')
            ->addColumn('employee_pengenal', fn($e) => $e->Employee->employee_pengenal ?? 'Empty')
            ->addColumn('created_at',        fn($e) => $e->Employee->created_at ?? 'Empty')
            ->addColumn('status',            fn($e) => $e->Employee->status ?? 'Empty')
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
            'Elementary School',
            'Junior High School',
            'Senior High School',
            'Diploma I',
            'Diploma II',
            'Diploma III',
            'Diploma IV',
            'Bachelor Degree',
            'Masters degree',
            'Vocational School',
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

        $data = $bawahan->map(fn($e) => [
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
        $subordinateIds = $employeeLogin->bawahanList()->pluck('employees_tables.id')->toArray();

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
            ->map(
                fn($items) => $items
                    ->pluck('scan_date')
                    ->map(fn($d) => Carbon::parse($d)->toDateString())
                    ->unique()
                    ->count()
            );

        $grouped = $fingerprints
            ->groupBy(fn($f) => $f->pin . '_' . Carbon::parse($f->scan_date)->toDateString());

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
                $times   = $sorted->pluck('scan_date')->map(fn($d) => Carbon::parse($d)->format('H:i:s'))->implode(', ');
                $devices = $sorted->map(fn($i) => optional($i->devicefingerprints)->device_name ?? '')->implode(', ');

                $row["in_$mode"]      = $times;
                $row["device_$mode"]  = $devices;
                $row["combine_$mode"] = trim($times . ' ' . $devices);
            });

            $times = collect(range(1, 10))
                ->flatMap(function ($i) use ($row) {
                    if (!$row["in_$i"]) return [];
                    return explode(', ', $row["in_$i"]);
                })
                ->map(fn($t) => Carbon::parse($t))
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
