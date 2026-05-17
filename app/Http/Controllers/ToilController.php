<?php

namespace App\Http\Controllers;
use App\Models\Departments;
use App\Models\Stores;
use App\Models\Toilbalances;
use App\Models\Overtimesubmissions;
use App\Models\ToilLeaveRequests;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ToilController extends Controller
{
    /**
     * GET /toil/balance
     * Dashboard saldo TOIL untuk karyawan.
     */
    public function index()
    {
        $user     = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        // Saldo aktif Cash & TOIL
        $saldoCash = Toilbalances::calculateActiveBalance($employee->id, 'Cash');
        $saldoToil = Toilbalances::calculateActiveBalance($employee->id, 'Toil');

        // Saldo aktif yang akan expired terdekat
        $nextExpireCash = $this->getNextExpiringBalance($employee->id, 'Cash');
        $nextExpireToil = $this->getNextExpiringBalance($employee->id, 'Toil');

        return view('pages.Toil.balance', [
            'employee'        => $employee,
            'saldoCash'       => $saldoCash,
            'saldoToil'       => $saldoToil,
            'nextExpireCash'  => $nextExpireCash,
            'nextExpireToil'  => $nextExpireToil,
        ]);
    }

    /**
     * GET /toil/balance/data
     * AJAX: detail saldo aktif (untuk DataTable).
     */
    public function getDataActive(Request $request)
    {
        $user     = Auth::user();
        $employee = $user->employee;

        $balances = Toilbalances::with('overtimeSubmission')
            ->where('employee_id', $employee->id)
            ->where('status', 'active')
            ->where('expires_at', '>=', today())
            ->orderBy('expires_at', 'asc')
            ->get();

        $data = $balances->map(function ($balance) {
            $sub = $balance->overtimeSubmission;
            return [
                'id'                => $balance->id,
                'date'              => $sub ? Carbon::parse($sub->date)->format('d M Y') : '-',
                'compensation_type' => $sub->compensation_type ?? '-',
                'earned_hours'      => number_format($balance->earned_hours, 2),
                'used_hours'        => number_format($balance->used_hours, 2),
                'remaining_hours'   => number_format($balance->remaining_hours, 2),
                'expires_at'        => $balance->expires_at->format('d M Y'),
                'days_left'         => $balance->days_until_expired,
                'reason'            => $sub->reason ?? '-',
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * GET /toil/history
     * Halaman history TOIL (semua, termasuk expired/cancelled).
     */
    public function history()
    {
        $user     = Auth::user();
        $employee = $user->employee;

        return view('pages.Toil.history', compact('employee'));
    }

    /**
     * GET /toil/history/assignments
     * AJAX: history semua assignment (active/expired/fully_used/cancelled).
     */
    public function getHistoryAssignments(Request $request)
    {
        $user     = Auth::user();
        $employee = $user->employee;

        $query = Toilbalances::with(['overtimeSubmission', 'overtimeSubmission.approver'])
            ->where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereHas('overtimeSubmission', function ($q) use ($request) {
                $q->whereBetween('date', [$request->start_date, $request->end_date]);
            });
        }

        $balances = $query->get();

        $data = $balances->map(function ($balance) {
            $sub = $balance->overtimeSubmission;
            return [
                'id'                => $balance->id,
                'date'              => $sub ? Carbon::parse($sub->date)->format('d M Y') : '-',
                'compensation_type' => $sub->compensation_type ?? '-',
                'earned_hours'      => number_format($balance->earned_hours, 2),
                'used_hours'        => number_format($balance->used_hours, 2),
                'status'            => $balance->status,
                'expires_at'        => $balance->expires_at->format('d M Y'),
                'paid_at'           => $balance->paid_at?->format('d M Y'),
                'paid_period'       => $balance->paid_period,
                'approver_name'     => $sub->approver->employee_name ?? '-',
                'reason'            => $sub->reason ?? '-',
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * GET /toil/history/leave-requests
     * AJAX: history klaim Leave Request.
     */
    public function getHistoryLeaveRequests(Request $request)
    {
        $user     = Auth::user();
        $employee = $user->employee;

        $requests = ToilLeaveRequests::with(['approver', 'balance.overtimeSubmission'])
            ->where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $requests->map(function ($req) {
            return [
                'id'              => $req->id,
                'leave_date'      => $req->leave_date->format('d M Y'),
                'hours_used'      => number_format($req->hours_used, 2),
                'status'          => $req->status,
                'approver_name'   => $req->approver->employee_name ?? '-',
                'approved_at'     => $req->approved_at?->format('d M Y H:i'),
                'rejected_reason' => $req->rejected_reason,
                'reason'          => $req->reason,
                'created_at'      => $req->created_at->format('d M Y H:i'),
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Helper: dapatkan saldo yang paling dekat expired (FIFO).
     */
    private function getNextExpiringBalance(string $employeeId, string $compensationType): ?array
    {
        $balance = Toilbalances::ofType($compensationType)
            ->where('employee_id', $employeeId)
            ->where('status', 'active')
            ->where('expires_at', '>=', today())
            ->orderBy('expires_at', 'asc')
            ->first();

        if (!$balance) return null;

        return [
            'expires_at' => $balance->expires_at->format('d M Y'),
            'days_left'  => $balance->days_until_expired,
            'hours'      => $balance->remaining_hours,
        ];
    }
       
        // ════════════════════════════════════════════════════════════════
        //   HR/HeadHR — All Balances Monitoring
        // ════════════════════════════════════════════════════════════════

    /**
     * GET /toil/all-balances
     * Halaman monitoring semua saldo TOIL (untuk HR).
     */
    public function allBalances(Request $request)
    {
        // Ambil list store untuk filter dropdown
        $stores = Stores::select('id', 'name')
            ->whereNotNull('name')
            ->orderBy('name')
            ->get();

        // Ambil list department untuk filter dropdown
        $departments = Departments::select('id', 'department_name')
            ->orderBy('department_name')
            ->get();

        return view('pages.Toil.allbalances', compact('stores', 'departments'));
    }

    /**
     * GET /toil/all-balances/data
     * AJAX endpoint untuk DataTable All Balances dengan filter.
     */
    public function getAllBalancesData(Request $request)
    {
        $query = Toilbalances::query()
            ->with([
                'employee:id,employee_name,pin,store_id,department_id',
                'employee.store:id,name',
                'employee.department:id,department_name',
                'overtimeSubmission:id,date,compensation_type',
            ]);

        // ── Apply Filters ──

        // Filter by store
        if ($request->filled('store_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('store_id', $request->store_id);
            });
        }

        // Filter by department
        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        // Filter by employee name (search)
        if ($request->filled('employee_search')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('employee_name', 'like', '%' . $request->employee_search . '%');
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type (Cash/Toil) — via overtime submission
        if ($request->filled('compensation_type')) {
            $query->whereHas('overtimeSubmission', function ($q) use ($request) {
                $q->where('compensation_type', $request->compensation_type);
            });
        }

        // Filter by date range (overtime date)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereHas('overtimeSubmission', function ($q) use ($request) {
                $q->whereBetween('date', [$request->start_date, $request->end_date]);
            });
        }

        $balances = $query->orderBy('created_at', 'desc')->get();

        // ── Format data untuk DataTable ──
        $data = $balances->map(function ($balance) {
            $sub = $balance->overtimeSubmission;
            $emp = $balance->employee;

            $daysLeft = null;
            if ($balance->status === 'active' && $balance->expires_at) {
                $daysLeft = max(0, today()->diffInDays($balance->expires_at, false));
            }

            return [
                'id'                => $balance->id,
                'employee_name'     => $emp->employee_name ?? '-',
                'employee_pin'      => $emp->pin ?? '-',
                'store'             => $emp->store->name ?? '-',
                'department'        => $emp->department->department_name ?? '-',
                'work_date'         => $sub ? Carbon::parse($sub->date)->format('d M Y') : '-',
                'compensation_type' => $sub->compensation_type ?? '-',
                'earned_hours'      => number_format($balance->earned_hours, 2),
                'used_hours'        => number_format($balance->used_hours, 2),
                'remaining_hours'   => number_format($balance->remaining_hours, 2),
                'expires_at'        => $balance->expires_at?->format('d M Y') ?? '-',
                'days_left'         => $daysLeft,
                'status'            => $balance->status,
                'paid_at'           => $balance->paid_at?->format('d M Y') ?? null,
                'paid_period'       => $balance->paid_period ?? null,
            ];
        });

        // ── Hitung Summary ──
        $summary = [
            'total_cash_pending' => $balances
                ->filter(fn($b) => optional($b->overtimeSubmission)->compensation_type === 'Cash' && $b->status === 'active')
                ->sum('remaining_hours'),
            'total_toil_active' => $balances
                ->filter(fn($b) => optional($b->overtimeSubmission)->compensation_type === 'Toil' && $b->status === 'active')
                ->sum('remaining_hours'),
            'total_used' => $balances->sum('used_hours'),
            'total_expired' => $balances
                ->filter(fn($b) => $b->status === 'expired')
                ->sum('remaining_hours'),
            'total_records' => $balances->count(),
            'active_employees' => $balances->pluck('employee_id')->unique()->count(),
        ];

        return response()->json([
            'data'    => $data,
            'summary' => $summary,
        ]);
    }
}
