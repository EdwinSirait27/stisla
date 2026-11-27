<?php
namespace App\Http\Controllers;

use App\Models\PayrollMobilePeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class PayrollMobilePeriodController extends Controller
{
    public function index()
    {
      $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('payroll.mobile.period.view')) {
            abort(403, 'Unauthorized');
        }   
        $stats = [
            'open'   => PayrollMobilePeriod::where('status', 'open')->count(),
            'closed' => PayrollMobilePeriod::where('status', 'closed')->count(),
            'locked' => PayrollMobilePeriod::where('status', 'locked')->count(),
        ];

        // Periode yang masih berjalan (open/closed) beserta progres payroll-nya
        $active = PayrollMobilePeriod::whereIn('status', ['open', 'closed'])
            ->withPayrollSummary()
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->get();

        $existing = PayrollMobilePeriod::get(['period_month', 'period_year'])
            ->map(fn($p) => $p->period_year . '-' . $p->period_month)
            ->values();

        return view('pages.PayrollMobilePeriod.index', compact('stats', 'active', 'existing'));
    }

    public function getPayrollPeriod(Request $request)
    {
           $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('payroll.mobile.period.view')) {
            abort(403, 'Unauthorized');
        }   
        $query = PayrollMobilePeriod::with(['createdBy', 'lockedBy'])
            ->select('payroll_mobile_periods.*')
            ->withPayrollSummary();

        if ($request->filled('year')) {
            $query->where('period_year', $request->year);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addColumn('period_label', fn($row) => $row->period_label)
            ->addColumn('period_range', fn($row) =>
                $row->period_start->locale('id')->translatedFormat('d M Y') . ' sampai ' . $row->period_end->locale('id')->translatedFormat('d M Y'))
            ->addColumn('status_badge', fn($row) => match ($row->status) {
                'open'   => '<span class="status-badge is-open">Open</span>',
                'closed' => '<span class="status-badge is-closed">Closed</span>',
                'locked' => '<span class="status-badge is-locked">Locked</span>',
                default  => e($row->status),
            })
            ->addColumn('payroll_total', fn($row) => (int) $row->payrolls_count)
            ->addColumn('draft_count', fn($row) => (int) $row->draft_count)
            ->addColumn('approved_count', fn($row) => (int) $row->approved_count)
            ->addColumn('paid_count', fn($row) => (int) $row->paid_count)
            ->addColumn('net_total_fmt', fn($row) => number_format((float) $row->net_total, 0, ',', '.'))
            ->addColumn('next_step', fn($row) => $row->nextStep())
            ->addColumn('created_by_name', fn($row) => $row->createdBy?->employee_name ?? '-')
            ->addColumn('locked_by_name', fn($row) =>
                $row->lockedBy
                    ? $row->lockedBy->employee_name . ', ' . $row->locked_at?->locale('id')->translatedFormat('d M Y H:i')
                    : '-')
            ->addColumn('action', function ($row) use ($user) {
                $actions = $user->can('payroll.mobile.view')
                    ? '<a href="' . route('payroll.mobile.index', $row->id) . '" class="act-btn" title="Lihat payroll" aria-label="Lihat payroll">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                    </a>'
                    : '';

                if ($row->isOpen() && $user->can('payroll.mobile.period.close')) {
                    $actions .= '<a href="' . route('payrollmobileperiod.close', $row->id) . '"
                        class="act-btn act-warning" title="Close periode" aria-label="Close periode"
                        onclick="return confirm(\'Close periode ini?\')">
                        <i class="fas fa-lock-open" aria-hidden="true"></i>
                    </a>';
                }

                if ($row->isClosed() && $user->can('payroll.mobile.period.lock')) {
                    $actions .= '<a href="' . route('payrollmobileperiod.lock', $row->id) . '"
                        class="act-btn act-danger" title="Lock periode" aria-label="Lock periode"
                        onclick="return confirm(\'Lock periode ini? Tidak bisa dibuka kembali.\')">
                        <i class="fas fa-lock" aria-hidden="true"></i>
                    </a>';
                }

                return '<div class="action-wrap">' . $actions . '</div>';
            })
            ->orderColumn('period_label', fn($q, $order) =>
                $q->orderBy('period_year', $order)->orderBy('period_month', $order))
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
           $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('payroll.mobile.period.create')) {
            abort(403, 'Unauthorized');
        }   
        $request->validate([
            'period_month' => 'required|integer|min:1|max:12',
            'period_year'  => 'required|integer|min:2020',
            'note'         => 'nullable|string|max:255',
        ]);

        // Cek duplikat
        $exists = PayrollMobilePeriod::where('period_month', $request->period_month)
            ->where('period_year', $request->period_year)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Periode ' . $request->period_month . '/' . $request->period_year . ' sudah ada.');
        }

        try {
            $period = PayrollMobilePeriod::generatePeriod($request->period_month, $request->period_year);

            PayrollMobilePeriod::create(array_merge($period, [
                'status'     => 'open',
                'note'       => $request->note,
                'created_by' => $user->employee_id,
            ]));

            return back()->with('success', 'Periode berhasil dibuat.');

        } catch (\Exception $e) {
            Log::error('PayrollMobilePeriod store error: ' . $e->getMessage());
            return back()->with('error', 'Gagal membuat periode.');
        }
    }

    public function close(string $id)
    {
           $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('payroll.mobile.period.close')) {
            abort(403, 'Unauthorized');
        }   
        $period = PayrollMobilePeriod::findOrFail($id);

        if (!$period->isOpen()) {
            return back()->with('error', 'Periode bukan berstatus open.');
        }

        // Semua payroll harus sudah di-approve sebelum periode ditutup
        $draftCount = $period->payrolls()->where('status', 'draft')->count();
        if ($draftCount > 0) {
            return back()->with('error', "Masih ada {$draftCount} payroll draft. Approve atau hapus dulu sebelum close periode.");
        }

        try {
            $period->update(['status' => 'closed']);
            return back()->with('success', 'Periode berhasil di-close.');
        } catch (\Exception $e) {
            Log::error('PayrollMobilePeriod close error: ' . $e->getMessage());
            return back()->with('error', 'Gagal close periode.');
        }
    }

    public function lock(string $id)
    {
           $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('payroll.mobile.period.lock')) {
            abort(403, 'Unauthorized');
        }   
        $period = PayrollMobilePeriod::findOrFail($id);

        if (!$period->isClosed()) {
            return back()->with('error', 'Periode harus berstatus closed sebelum di-lock.');
        }

        // Semua payroll harus sudah paid sebelum periode dikunci permanen
        $unpaidCount = $period->payrolls()->where('status', '!=', 'paid')->count();
        if ($unpaidCount > 0) {
            return back()->with('error', "Masih ada {$unpaidCount} payroll yang belum paid. Tandai paid dulu sebelum lock periode.");
        }

        try {
            $period->update([
                'status'    => 'locked',
                'locked_by' => $user->employee_id,
                'locked_at' => now(),
            ]);
            return back()->with('success', 'Periode berhasil di-lock.');
        } catch (\Exception $e) {
            Log::error('PayrollMobilePeriod lock error: ' . $e->getMessage());
            return back()->with('error', 'Gagal lock periode.');
        }
    }
}