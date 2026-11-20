<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Departments;
use App\Models\Employee;
use App\Models\Leavebalance;
use App\Models\Leaverequest;
use App\Models\Leavetypes;
use App\Models\Stores;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

/**
 * Daftar saldo & pengajuan cuti (menggantikan SummaryController).
 *
 * Cakupan data per permission (yang paling luas menang):
 *  - leave.list.view          → semua karyawan
 *  - leave.list.view.manager  → diri sendiri + bawahan langsung (employee_atasans)
 *  - leave.list.view.human    → diri sendiri
 *
 * Catatan saldo: balance_days = SISA. Saldo dipotong saat pengajuan dibuat (Pending)
 * dan dikembalikan saat ditolak (LeaverequestController), jadi
 * hak = sisa + pending + disetujui.
 */
class LeaveController extends Controller
{
    private const ACTIVE = ['Active', 'Pending', 'Mutation', 'On Leave'];

    /** 'all' | 'team' | 'self' | null (tidak berhak) */
    private function scope(): ?string
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        return match (true) {
            $user?->can('leave.list.view')         => 'all',
            $user?->can('leave.list.view.manager') => 'team',
            $user?->can('leave.list.view.human')   => 'self',
            default                                => null,
        };
    }

    /** ID karyawan yang boleh dilihat. null = semua. */
    private function allowedEmployeeIds(string $scope): ?array
    {
        if ($scope === 'all') {
            return null;
        }

        $me = auth()->user()->employee;
        if (!$me) {
            return [];
        }

        if ($scope === 'self') {
            return [$me->id];
        }

        return $me->bawahanList()->pluck('employees_tables.id')->push($me->id)->unique()->values()->all();
    }

    private function applyScope(Builder $query, string $scope, string $column = 'employees_tables.id'): Builder
    {
        $ids = $this->allowedEmployeeIds($scope);

        return $ids === null ? $query : $query->whereIn($column, $ids ?: ['00000000-0000-0000-0000-000000000000']);
    }

    private function year(Request $request): int
    {
        $y = (int) $request->input('year', now()->year);

        return $y >= 2000 && $y <= 2100 ? $y : (int) now()->year;
    }

    // ── Halaman ───────────────────────────────────────────────────

    public function index(Request $request)
    {
        $scope = $this->scope();
        abort_if(!$scope, 403, 'Unauthorized');

        $year  = $this->year($request);
        $years = Leavebalance::query()->distinct()->orderByDesc('year')->pluck('year')->push((int) now()->year)->unique()->sortDesc()->values();
        $types = Leavetypes::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        // Ringkasan dalam cakupan
        $balances = Leavebalance::query()->where('year', $year)
            ->whereHas('employees', fn($q) => $this->applyScope($q->whereIn('status', self::ACTIVE), $scope));
        $balanceIds = (clone $balances)->pluck('id');
        $reqs = Leaverequest::whereIn('leave_balance_id', $balanceIds);

        $remaining = (float) (clone $balances)->sum('balance_days');
        $pending   = (float) (clone $reqs)->where('status', 'Pending')->sum('total_days');
        $approved  = (float) (clone $reqs)->where('status', 'Approved')->sum('total_days');

        $employeesInScope = $this->applyScope(Employee::query()->whereIn('status', self::ACTIVE), $scope)->count();
        $withBalance = (clone $balances)->distinct('employee_id')->count('employee_id');

        $stats = [
            'employees'      => $employeesInScope,
            'without'        => max(0, $employeesInScope - $withBalance),
            'entitled'       => $remaining + $pending + $approved,
            'approved'       => $approved,
            'pending'        => $pending,
            'pending_count'  => (clone $reqs)->where('status', 'Pending')->count(),
            'remaining'      => $remaining,
            'upcoming'       => (clone $reqs)->where('status', 'Approved')->whereDate('start_date', '>=', today())->count(),
            'on_leave_today' => (clone $reqs)->where('status', 'Approved')->whereDate('start_date', '<=', today())->whereDate('end_date', '>=', today())->count(),
        ];

        // Saldo saya (kalau user punya data karyawan)
        $me = auth()->user()->employee;
        $myBalances = $me
            ? Leavebalance::with('leaves:id,name')->where('employee_id', $me->id)->where('year', $year)->get()
                ->map(function ($b) {
                    $p = (float) Leaverequest::where('leave_balance_id', $b->id)->where('status', 'Pending')->sum('total_days');
                    $a = (float) Leaverequest::where('leave_balance_id', $b->id)->where('status', 'Approved')->sum('total_days');
                    return ['type' => $b->leaves?->name ?? '-', 'remaining' => (float) $b->balance_days, 'pending' => $p, 'approved' => $a, 'entitled' => (float) $b->balance_days + $p + $a];
                })
            : collect();

        return view('pages.leave-list.leave-list', [
            'scope'       => $scope,
            'year'        => $year,
            'years'       => $years,
            'types'       => $types,
            'stats'       => $stats,
            'myBalances'  => $myBalances,
            'me'          => $me,
            'companies'   => $scope === 'all' ? Company::orderBy('name')->pluck('name') : collect(),
            'stores'      => $scope === 'all' ? Stores::orderBy('name')->pluck('name') : collect(),
            'departments' => $scope === 'all' ? Departments::orderBy('department_name')->pluck('department_name') : collect(),
        ]);
    }

    /** DataTables: saldo cuti per karyawan untuk satu tahun. */
    public function getBalances(Request $request)
    {
        $scope = $this->scope();
        abort_if(!$scope, 403, 'Unauthorized');
        $year = $this->year($request);

        // Agregat per karyawan: sisa (balance_days), pending & approved (dari pengajuan)
        $agg = Leavebalance::query()
            ->leftJoin('leave_requests as r', 'r.leave_balance_id', '=', 'leave_balances_tables.id')
            ->where('leave_balances_tables.year', $year)
            ->when($request->filled('leave_type'), fn($q) => $q->where('leave_balances_tables.leave_type_id', $request->leave_type))
            ->groupBy('leave_balances_tables.employee_id')
            ->selectRaw('leave_balances_tables.employee_id AS emp_id')
            ->selectRaw('COUNT(DISTINCT leave_balances_tables.id) AS types')
            ->selectRaw("COALESCE(SUM(CASE WHEN r.status = 'Pending' THEN r.total_days END), 0) AS pending_days")
            ->selectRaw("COALESCE(SUM(CASE WHEN r.status = 'Approved' THEN r.total_days END), 0) AS approved_days")
            ->selectRaw("SUM(CASE WHEN r.status = 'Pending' THEN 1 ELSE 0 END) AS pending_count")
            ->selectRaw("MIN(CASE WHEN r.status = 'Approved' AND r.start_date >= CURDATE() THEN r.start_date END) AS next_leave");
        $remaining = Leavebalance::query()->where('year', $year)
            ->when($request->filled('leave_type'), fn($q) => $q->where('leave_type_id', $request->leave_type))
            ->groupBy('employee_id')->selectRaw('employee_id AS emp_id, SUM(balance_days) AS remaining_days');

        $query = Employee::query()
            ->whereIn('employees_tables.status', self::ACTIVE)
            ->leftJoinSub($agg, 'a', 'a.emp_id', '=', 'employees_tables.id')
            ->leftJoinSub($remaining, 'rm', 'rm.emp_id', '=', 'employees_tables.id')
            ->leftJoin('company_tables as c', 'c.id', '=', 'employees_tables.company_id')
            ->leftJoin('employee_stores as es', fn($j) => $j->on('es.employee_id', '=', 'employees_tables.id')->where('es.is_primary', true))
            ->leftJoin('stores_tables as s', 's.id', '=', 'es.store_id')
            ->leftJoin('employee_departments as ed', fn($j) => $j->on('ed.employee_id', '=', 'employees_tables.id')->where('ed.is_primary', true))
            ->leftJoin('departments_tables as d', 'd.id', '=', 'ed.department_id')
            ->select([
                'employees_tables.id', 'employees_tables.employee_name', 'employees_tables.employee_pengenal',
                'employees_tables.status_employee', 'employees_tables.join_date',
                'c.name as company_name', 's.name as store_name', 'd.department_name',
                DB::raw('COALESCE(rm.remaining_days, 0) AS remaining_days'),
                DB::raw('COALESCE(a.pending_days, 0) AS pending_days'),
                DB::raw('COALESCE(a.approved_days, 0) AS approved_days'),
                DB::raw('COALESCE(a.pending_count, 0) AS pending_count'),
                DB::raw('COALESCE(a.types, 0) AS types'),
                'a.next_leave',
                DB::raw('(COALESCE(rm.remaining_days, 0) + COALESCE(a.pending_days, 0) + COALESCE(a.approved_days, 0)) AS entitled_days'),
            ]);
        $this->applyScope($query, $scope);

        $query->when($request->filled('company'), fn($q) => $q->where('c.name', $request->company));
        $query->when($request->filled('store'), fn($q) => $q->where('s.name', $request->store));
        $query->when($request->filled('department'), fn($q) => $q->where('d.department_name', $request->department));
        $query->when($request->filled('emp_type'), fn($q) => $q->where('employees_tables.status_employee', $request->emp_type));
        match ($request->input('flag')) {
            'none'    => $query->whereNull('a.emp_id'),
            'low'     => $query->whereNotNull('a.emp_id')->whereRaw('COALESCE(rm.remaining_days, 0) <= 2'),
            'pending' => $query->where('a.pending_count', '>', 0),
            default   => null,
        };

        $num = fn($v) => rtrim(rtrim(number_format((float) $v, 1, ',', '.'), '0'), ',');

        return DataTables::of($query)
            ->addColumn('employee_cell', fn($e) => '<span class="pr-cell-name">' . e($e->employee_name) . '</span>'
                . '<span class="pr-cell-sub">' . e(collect([$e->employee_pengenal, $e->status_employee])->filter()->implode(', ')) . '</span>')
            ->addColumn('place', fn($e) => '<span class="pr-cell-name">' . e($e->store_name ?? '-') . '</span><span class="pr-cell-sub">' . e(collect([$e->department_name, $e->company_name])->filter()->implode(', ') ?: '-') . '</span>')
            ->addColumn('entitled_fmt', fn($e) => $e->types ? $num($e->entitled_days) : '<span class="status-badge is-draft">Belum ada saldo</span>')
            ->addColumn('approved_fmt', fn($e) => $e->types ? $num($e->approved_days) : '<span class="pr-muted">-</span>')
            ->addColumn('pending_fmt', fn($e) => (float) $e->pending_days > 0
                ? '<span class="status-badge is-pending">' . $num($e->pending_days) . ' hari</span>'
                : '<span class="pr-muted">-</span>')
            ->addColumn('remaining_fmt', function ($e) use ($num) {
                if (!$e->types) return '<span class="pr-muted">-</span>';
                $r = (float) $e->remaining_days;
                $pct = (float) $e->entitled_days > 0 ? round($r / (float) $e->entitled_days * 100) : 0;
                $cls = $r <= 0 ? 'is-empty' : ($r <= 2 ? 'is-low' : '');
                return '<span class="lv-left ' . $cls . '"><strong>' . $num($r) . '</strong> hari</span>'
                    . '<span class="lv-meter" aria-hidden="true"><span style="width:' . max(0, min(100, $pct)) . '%"></span></span>';
            })
            ->addColumn('next_fmt', fn($e) => $e->next_leave
                ? '<span class="pr-cell-name">' . e(Carbon::parse($e->next_leave)->locale('id')->translatedFormat('d M Y')) . '</span><span class="pr-cell-sub">' . e(Carbon::parse($e->next_leave)->locale('id')->diffForHumans()) . '</span>'
                : '<span class="pr-muted">-</span>')
            ->filterColumn('employee_cell', fn($q, $k) => $q->where(fn($w) => $w->where('employees_tables.employee_name', 'like', "%{$k}%")->orWhere('employees_tables.employee_pengenal', 'like', "%{$k}%")))
            ->filterColumn('place', fn($q, $k) => $q->where(fn($w) => $w->where('s.name', 'like', "%{$k}%")->orWhere('d.department_name', 'like', "%{$k}%")))
            ->orderColumn('employee_cell', 'employees_tables.employee_name $1')
            ->orderColumn('place', 's.name $1')
            ->orderColumn('entitled_fmt', 'entitled_days $1')
            ->orderColumn('approved_fmt', 'approved_days $1')
            ->orderColumn('pending_fmt', 'pending_days $1')
            ->orderColumn('remaining_fmt', 'remaining_days $1')
            ->orderColumn('next_fmt', 'a.next_leave $1')
            ->rawColumns(['employee_cell', 'place', 'entitled_fmt', 'approved_fmt', 'pending_fmt', 'remaining_fmt', 'next_fmt'])
            ->make(true);
    }

    /** DataTables: daftar pengajuan cuti dalam cakupan. */
    public function getRequests(Request $request)
    {
        $scope = $this->scope();
        abort_if(!$scope, 403, 'Unauthorized');
        $year = $this->year($request);

        $query = Leaverequest::query()
            ->join('leave_balances_tables as b', 'b.id', '=', 'leave_requests.leave_balance_id')
            ->join('employees_tables', 'employees_tables.id', '=', 'b.employee_id')
            ->leftJoin('leave_types_tables as t', 't.id', '=', 'b.leave_type_id')
            ->where('b.year', $year)
            ->select([
                'leave_requests.*', 'employees_tables.employee_name', 'employees_tables.employee_pengenal', 't.name as type_name',
            ]);
        $this->applyScope($query, $scope);

        $query->when($request->filled('status'), fn($q) => $q->where('leave_requests.status', $request->status));
        $query->when($request->filled('leave_type'), fn($q) => $q->where('b.leave_type_id', $request->leave_type));
        $query->when($request->input('when') === 'upcoming', fn($q) => $q->whereDate('leave_requests.end_date', '>=', today()));

        $d = fn($v) => Carbon::parse($v)->locale('id')->translatedFormat('d M Y');
        $statusCls = ['Approved' => 'is-approved', 'Pending' => 'is-pending', 'Rejected' => 'is-rejected'];
        $statusLbl = ['Approved' => 'Disetujui', 'Pending' => 'Menunggu', 'Rejected' => 'Ditolak'];

        return DataTables::of($query)
            ->addColumn('employee_cell', fn($r) => '<span class="pr-cell-name">' . e($r->employee_name) . '</span><span class="pr-cell-sub">' . e($r->employee_pengenal) . '</span>')
            ->addColumn('type_fmt', fn($r) => e($r->type_name ?? '-'))
            ->addColumn('date_fmt', function ($r) use ($d) {
                $same = Carbon::parse($r->start_date)->isSameDay(Carbon::parse($r->end_date));
                $now = today()->betweenIncluded(Carbon::parse($r->start_date), Carbon::parse($r->end_date)) && $r->status === 'Approved';
                return '<span class="pr-cell-name">' . e($same ? $d($r->start_date) : $d($r->start_date) . ' sampai ' . $d($r->end_date)) . '</span>'
                    . '<span class="pr-cell-sub">' . e(rtrim(rtrim(number_format((float) $r->total_days, 1, ',', ''), '0'), ',')) . ' hari' . ($now ? ', sedang cuti' : '') . '</span>';
            })
            ->addColumn('status_fmt', fn($r) => '<span class="status-badge ' . ($statusCls[$r->status] ?? 'is-draft') . '">' . e($statusLbl[$r->status] ?? $r->status) . '</span>')
            ->addColumn('reason_fmt', fn($r) => '<span class="lv-reason">' . e(\Illuminate\Support\Str::limit($r->employee_reason ?? '-', 120)) . '</span>'
                . ($r->approver_reason ? '<span class="pr-cell-sub">Atasan: ' . e(\Illuminate\Support\Str::limit($r->approver_reason, 100)) . '</span>' : ''))
            ->addColumn('created_fmt', fn($r) => '<span class="pr-cell-sub">' . e(Carbon::parse($r->created_at)->locale('id')->translatedFormat('d M Y')) . '</span>')
            ->filterColumn('employee_cell', fn($q, $k) => $q->where(fn($w) => $w->where('employees_tables.employee_name', 'like', "%{$k}%")->orWhere('employees_tables.employee_pengenal', 'like', "%{$k}%")))
            ->filterColumn('reason_fmt', fn($q, $k) => $q->where('leave_requests.employee_reason', 'like', "%{$k}%"))
            ->orderColumn('employee_cell', 'employees_tables.employee_name $1')
            ->orderColumn('type_fmt', 't.name $1')
            ->orderColumn('date_fmt', 'leave_requests.start_date $1')
            ->orderColumn('status_fmt', 'leave_requests.status $1')
            ->orderColumn('created_fmt', 'leave_requests.created_at $1')
            ->rawColumns(['employee_cell', 'date_fmt', 'status_fmt', 'reason_fmt', 'created_fmt'])
            ->make(true);
    }
}
