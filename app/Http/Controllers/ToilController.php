<?php

namespace App\Http\Controllers;

use App\Models\Departments;
use App\Models\Stores;
use App\Models\Toilbalances;
use App\Models\ToilLeaveRequests;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Saldo & riwayat TOIL karyawan, plus monitoring semua saldo (HR).
 *
 * Catatan status: saldo TOIL "active" yang tanggalnya sudah lewat ditampilkan & dihitung
 * sebagai expired (Toilbalances::effectiveStatus), sama dengan yang akan dilakukan command
 * harian toil:expire-balances. Saldo Cash tidak hangus: dibayar lewat payroll lalu jadi "paid".
 */
class ToilController extends Controller
{
    // ── Karyawan: saldo saya ──────────────────────────────────────

    public function index()
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return redirect()->back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        return view('pages.Toil.balance', [
            'employee'      => $employee,
            'hoursPerDay'   => \App\Models\ToilSetting::current()->hours_per_day,
            'isDw'          => strtoupper($employee->status_employee ?? '') === 'DW',
        ]);
    }

    public function getDataActive(Request $request)
    {
        $employee = Auth::user()->employee;
        if (!$employee) {
            return response()->json(['data' => [], 'summary' => $this->emptySummary()]);
        }

        $all = Toilbalances::with('overtimeSubmission')
            ->where('employee_id', $employee->id)
            ->orderBy('expires_at')
            ->get();

        $rows = $all->map(fn($b) => $this->balanceRow($b));

        $filtered = $rows->filter(function ($r) use ($request) {
            if ($request->filled('start_date') && $r['date_raw'] && $r['date_raw'] < $request->start_date) return false;
            if ($request->filled('end_date') && $r['date_raw'] && $r['date_raw'] > $request->end_date) return false;
            if ($request->filled('compensation_type') && $r['compensation_type'] !== $request->compensation_type) return false;
            if ($request->filled('status') && $r['status'] !== $request->status) return false;
            return true;
        })->values();

        return response()->json([
            'data'    => $filtered,
            'summary' => $this->summarize($all),
        ]);
    }

    // ── Karyawan: riwayat ─────────────────────────────────────────

    public function history()
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return redirect()->back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        return view('pages.Toil.history', compact('employee'));
    }

    public function getHistoryAssignments(Request $request)
    {
        $employee = Auth::user()->employee;
        if (!$employee) {
            return response()->json(['data' => []]);
        }

        $query = Toilbalances::with(['overtimeSubmission', 'overtimeSubmission.approver'])
            ->where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereHas('overtimeSubmission', fn($q) => $q->whereBetween('date', [$request->start_date, $request->end_date]));
        }

        $data = $query->get()->map(function ($balance) {
            $sub = $balance->overtimeSubmission;
            return [
                'id'                => $balance->id,
                'date'              => $sub ? Carbon::parse($sub->date)->format('d M Y') : '-',
                'date_raw'          => $sub ? Carbon::parse($sub->date)->toDateString() : null,
                'time_range'        => $sub && $sub->start_time ? substr($sub->start_time, 0, 5) . '–' . substr((string) $sub->end_time, 0, 5) : null,
                'compensation_type' => $sub->compensation_type ?? '-',
                'earned_hours'      => (float) $balance->earned_hours,
                'used_hours'        => (float) $balance->used_hours,
                'status'            => $balance->effectiveStatus($sub?->compensation_type),
                'expires_at'        => $balance->expires_at?->format('d M Y'),
                'paid_at'           => $balance->paid_at?->format('d M Y'),
                'paid_period'       => $balance->paid_period,
                'approver_name'     => $sub->approver->employee_name ?? '-',
                'reason'            => $sub->reason ?? '-',
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function getHistoryLeaveRequests(Request $request)
    {
        $employee = Auth::user()->employee;
        if (!$employee) {
            return response()->json(['data' => []]);
        }

        $data = ToilLeaveRequests::with(['approver'])
            ->where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($req) => [
                'id'              => $req->id,
                'leave_date'      => $req->leave_date?->format('d M Y'),
                'date_raw'        => $req->leave_date?->toDateString(),
                'hours_used'      => (float) $req->hours_used,
                'status'          => $req->status,
                'approver_name'   => $req->approver->employee_name ?? '-',
                'approved_at'     => $req->approved_at?->format('d M Y H:i'),
                'rejected_reason' => $req->rejected_reason,
                'reason'          => $req->reason,
                'created_at'      => $req->created_at->format('d M Y H:i'),
            ]);

        return response()->json(['data' => $data]);
    }

    // ── HR: semua saldo ───────────────────────────────────────────

    public function allBalances(Request $request)
    {
        $stores = Stores::select('id', 'name')->whereNotNull('name')->orderBy('name')->get();
        $departments = Departments::select('id', 'department_name')->orderBy('department_name')->get();

        return view('pages.Toil.allbalances', compact('stores', 'departments'));
    }

    public function getAllBalancesData(Request $request)
    {
        $query = Toilbalances::query()->with([
            'employee:id,employee_name,employee_pengenal,pin,status_employee',
            'employee.primaryStore:id,name',
            'employee.primaryDepartment:id,department_name',
            'overtimeSubmission:id,date,compensation_type,approver_id,reason',
            'overtimeSubmission.approver:id,employee_name',
        ]);

        // Store & departemen dari pivot "utama" (kolom denormalisasi di tabel karyawan bisa tertinggal)
        if ($request->filled('store_id')) {
            $query->whereHas('employee.store', fn($q) => $q->where('stores_tables.id', $request->store_id)->where('employee_stores.is_primary', true));
        }
        if ($request->filled('department_id')) {
            $query->whereHas('employee.department', fn($q) => $q->where('departments_tables.id', $request->department_id)->where('employee_departments.is_primary', true));
        }
        if ($request->filled('employee_search')) {
            $k = $request->employee_search;
            $query->whereHas('employee', fn($q) => $q->where('employee_name', 'like', "%{$k}%")->orWhere('employee_pengenal', 'like', "%{$k}%"));
        }
        if ($request->filled('compensation_type')) {
            $query->whereHas('overtimeSubmission', fn($q) => $q->where('compensation_type', $request->compensation_type));
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereHas('overtimeSubmission', fn($q) => $q->whereBetween('date', [$request->start_date, $request->end_date]));
        }

        $balances = $query->orderBy('created_at', 'desc')->get();

        $rows = $balances->map(function ($b) {
            $emp = $b->employee;
            return array_merge($this->balanceRow($b), [
                'employee_name'     => $emp->employee_name ?? '-',
                'employee_pengenal' => $emp->employee_pengenal ?? '-',
                'employee_pin'      => $emp->pin ?? '-',
                'employee_type'     => $emp->status_employee ?? '-',
                'store'             => $emp?->primaryStore->first()?->name ?? '-',
                'department'        => $emp?->primaryDepartment->first()?->department_name ?? '-',
                'approver_name'     => $b->overtimeSubmission?->approver?->employee_name ?? '-',
            ]);
        });

        // Status difilter setelah effective status dihitung (expired yang belum diproses command ikut)
        $filtered = $request->filled('status')
            ? $rows->where('status', $request->status)->values()
            : $rows->values();

        $summary = $this->summarize($balances);
        $summary['total_records']    = $filtered->count();
        $summary['active_employees'] = $balances->pluck('employee_id')->unique()->count();
        $summary['expiring_soon']    = $rows->where('status', 'active')->where('compensation_type', 'Toil')->filter(fn($r) => $r['days_left'] !== null && $r['days_left'] <= 7)->count();
        // Nama lama tetap dikirim untuk kompatibilitas
        $summary['total_cash_pending'] = $summary['total_cash'];
        $summary['total_toil_active']  = $summary['total_toil_remaining'];

        return response()->json(['data' => $filtered, 'summary' => $summary]);
    }

    // ── Helper ────────────────────────────────────────────────────

    /** Satu baris saldo untuk tabel (dipakai saldo saya & semua saldo). */
    private function balanceRow(Toilbalances $b): array
    {
        $sub    = $b->overtimeSubmission;
        $type   = $sub->compensation_type ?? '-';
        $status = $b->effectiveStatus($type);

        return [
            'id'                => $b->id,
            'date'              => $sub ? Carbon::parse($sub->date)->format('d M Y') : '-',
            'date_raw'          => $sub ? Carbon::parse($sub->date)->toDateString() : null,
            'compensation_type' => $type,
            'earned_hours'      => (float) $b->earned_hours,
            'used_hours'        => (float) $b->used_hours,
            'remaining_hours'   => (float) $b->remaining_hours,
            'expires_at'        => $b->expires_at?->format('d M Y'),
            'expires_raw'       => $b->expires_at?->toDateString(),
            // Sisa hari hanya bermakna untuk saldo TOIL yang masih berlaku
            'days_left'         => $status === 'active' && $type === 'Toil' && $b->expires_at ? $b->days_until_expired : null,
            'status'            => $status,
            'paid_at'           => $b->paid_at?->format('d M Y'),
            'paid_period'       => $b->paid_period,
            'reason'            => $sub->reason ?? '-',
        ];
    }

    private function emptySummary(): array
    {
        return ['total_cash' => 0, 'total_toil_remaining' => 0, 'total_used' => 0, 'total_expired' => 0, 'total_paid' => 0, 'next_expiry' => null];
    }

    /**
     * Ringkasan dari koleksi saldo:
     * - total_cash: jam lembur Cash yang belum dibayar (status active, tidak hangus)
     * - total_toil_remaining: sisa jam TOIL yang masih berlaku
     * - total_used: jam TOIL yang sudah dipakai libur
     * - total_expired: sisa jam TOIL yang hangus
     * - total_paid: jam Cash yang sudah dibayar payroll
     */
    private function summarize($balances): array
    {
        $rows = $balances->map(fn($b) => ['b' => $b, 'type' => $b->overtimeSubmission?->compensation_type, 'status' => $b->effectiveStatus($b->overtimeSubmission?->compensation_type)]);

        $toilActive = $rows->where('type', 'Toil')->where('status', 'active');
        $next = $toilActive->sortBy(fn($r) => $r['b']->expires_at)->first();

        return [
            'total_cash'           => (float) $rows->where('type', 'Cash')->where('status', 'active')->sum(fn($r) => (float) $r['b']->remaining_hours),
            'total_toil_remaining' => (float) $toilActive->sum(fn($r) => (float) $r['b']->remaining_hours),
            'total_used'           => (float) $rows->where('type', 'Toil')->sum(fn($r) => (float) $r['b']->used_hours),
            'total_expired'        => (float) $rows->where('type', 'Toil')->where('status', 'expired')->sum(fn($r) => (float) $r['b']->remaining_hours),
            'total_paid'           => (float) $rows->where('type', 'Cash')->where('status', 'paid')->sum(fn($r) => (float) $r['b']->earned_hours),
            'next_expiry'          => $next ? [
                'date'  => $next['b']->expires_at->format('d M Y'),
                'days'  => $next['b']->days_until_expired,
                'hours' => (float) $next['b']->remaining_hours,
            ] : null,
        ];
    }
}
