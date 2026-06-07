<?php
namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class PayrollPeriodController extends Controller
{
    public function index()
    {
      $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePayrollPeriod')) {
            abort(403, 'Unauthorized');
        }   
         $stats = [
        'open'   => PayrollPeriod::where('status', 'open')->count(),
        'closed' => PayrollPeriod::where('status', 'closed')->count(),
        'locked' => PayrollPeriod::where('status', 'locked')->count(),
    ];
        return view('pages.PayrollPeriod.index',compact('stats'));
    }

    public function getPayrollPeriod(Request $request)
    {
           $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePayrollPeriod')) {
            abort(403, 'Unauthorized');
        }   
        $query = PayrollPeriod::with(['createdBy', 'lockedBy'])
            ->select('payroll_periods.*');

        if ($request->filled('year')) {
            $query->where('period_year', $request->year);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addColumn('period_label', fn($row) => $row->period_label)
            ->addColumn('period_range', fn($row) =>
                $row->period_start->format('d/m/Y') . ' — ' . $row->period_end->format('d/m/Y')
            )
            ->addColumn('status_badge', fn($row) => match($row->status) {
                'open'   => '<span class="status-badge" style="background:#f0fdf4;color:#166534">Open</span>',
                'closed' => '<span class="status-badge" style="background:#fffbeb;color:#92400e">Closed</span>',
                'locked' => '<span class="status-badge" style="background:#fef2f2;color:#991b1b"><i class="fas fa-lock" style="font-size:.65rem"></i> Locked</span>',
                default  => $row->status,
            })
            ->addColumn('created_by_name', fn($row) => $row->createdBy?->employee_name ?? '-')
            ->addColumn('locked_by_name', fn($row) =>
                $row->lockedBy
                    ? $row->lockedBy->employee_name . ' (' . $row->locked_at?->format('d/m/Y H:i') . ')'
                    : '-'
            )
            // ->addColumn('action', function ($row) {
            //     $actions = '';

            //     if ($row->isOpen()) {
            //         $actions .= '<a href="' . route('payrollperiod.close', $row->id) . '"
            //             class="act-btn act-warning" title="Close Period"
            //             onclick="return confirm(\'Close periode ini?\')">
            //             <i class="fas fa-lock-open"></i>
            //         </a>';
            //     }

            //     if ($row->isClosed()) {
            //         $actions .= '<a href="' . route('payrollperiod.lock', $row->id) . '"
            //             class="act-btn act-danger" title="Lock Period"
            //             onclick="return confirm(\'Lock periode ini? Tidak bisa dibuka kembali.\')">
            //             <i class="fas fa-lock"></i>
            //         </a>';
            //     }

            //     return '<div class="action-wrap">' . $actions . '</div>';
            // })
//             ->addColumn('action', function ($row) {
//     $actions = '';

//     if ($row->isOpen()) {
//         // Tambah tombol Generate Payroll
//         $actions .= '<a href="' . route('payroll.generate', $row->id) . '"
//             class="act-btn" title="Generate Payroll" style="background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe">
//             <i class="fas fa-cog"></i>
//         </a> ';

//         // Tombol Close
//         $actions .= '<a href="' . route('payrollperiod.close', $row->id) . '"
//             class="act-btn act-warning" title="Close Period"
//             onclick="return confirm(\'Close periode ini?\')">
//             <i class="fas fa-lock-open"></i>
//         </a>';
//     }

//     if ($row->isClosed()) {
//         $actions .= '<a href="' . route('payrollperiod.lock', $row->id) . '"
//             class="act-btn act-danger" title="Lock Period"
//             onclick="return confirm(\'Lock periode ini? Tidak bisa dibuka kembali.\')">
//             <i class="fas fa-lock"></i>
//         </a>';
//     }

//     return '<div class="action-wrap">' . $actions . '</div>';
// })
// ->addColumn('action', function ($row) {
//     $actions = '';

//     if ($row->isOpen()) {
//         // ← tambah tombol Generate di sini
//         $actions .= '<a href="' . route('payroll.generate', $row->id) . '"
//             class="act-btn" title="Generate Payroll"
//             style="background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe"
//             onclick="return confirm(\'Generate payroll untuk periode ini?\')">
//             <i class="fas fa-cog"></i>
//         </a> ';

//         // Tombol Close
//         $actions .= '<a href="' . route('payrollperiod.close', $row->id) . '"
//             class="act-btn act-warning" title="Close Period"
//             onclick="return confirm(\'Close periode ini?\')">
//             <i class="fas fa-lock-open"></i>
//         </a>';
//     }

//     if ($row->isClosed()) {
//         $actions .= '<a href="' . route('payrollperiod.lock', $row->id) . '"
//             class="act-btn act-danger" title="Lock Period"
//             onclick="return confirm(\'Lock periode ini? Tidak bisa dibuka kembali.\')">
//             <i class="fas fa-lock"></i>
//         </a>';
//     }

//     return '<div class="action-wrap">' . $actions . '</div>';
// })
->addColumn('action', function ($row) {
    $actions = '';

    if ($row->isOpen()) {
        // Tombol View Payroll → ke index payroll
        $actions .= '<a href="' . route('payroll.index', $row->id) . '"
            class="act-btn" title="Lihat Payroll"
            style="background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe">
            <i class="fas fa-eye"></i>
        </a> ';

        // Tombol Close
        $actions .= '<a href="' . route('payrollperiod.close', $row->id) . '"
            class="act-btn act-warning" title="Close Period"
            onclick="return confirm(\'Close periode ini?\')">
            <i class="fas fa-lock-open"></i>
        </a>';
    }

    if ($row->isClosed()) {
        // Tombol View Payroll → ke index payroll
        $actions .= '<a href="' . route('payroll.index', $row->id) . '"
            class="act-btn" title="Lihat Payroll"
            style="background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe">
            <i class="fas fa-eye"></i>
        </a> ';

        // Tombol Lock
        $actions .= '<a href="' . route('payrollperiod.lock', $row->id) . '"
            class="act-btn act-danger" title="Lock Period"
            onclick="return confirm(\'Lock periode ini? Tidak bisa dibuka kembali.\')">
            <i class="fas fa-lock"></i>
        </a>';
    }

    if ($row->isLocked()) {
        // Hanya view
        $actions .= '<a href="' . route('payroll.index', $row->id) . '"
            class="act-btn" title="Lihat Payroll"
            style="background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe">
            <i class="fas fa-eye"></i>
        </a>';
    }

    return '<div class="action-wrap">' . $actions . '</div>';
})
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
           $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePayrollPeriod')) {
            abort(403, 'Unauthorized');
        }   
        $request->validate([
            'period_month' => 'required|integer|min:1|max:12',
            'period_year'  => 'required|integer|min:2020',
            'note'         => 'nullable|string|max:255',
        ]);

        // Cek duplikat
        $exists = PayrollPeriod::where('period_month', $request->period_month)
            ->where('period_year', $request->period_year)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Periode ' . $request->period_month . '/' . $request->period_year . ' sudah ada.');
        }

        try {
            $period = PayrollPeriod::generatePeriod($request->period_month, $request->period_year);

            PayrollPeriod::create(array_merge($period, [
                'status'     => 'open',
                'note'       => $request->note,
                'created_by' => $user->employee_id,
            ]));

            return back()->with('success', 'Periode berhasil dibuat.');

        } catch (\Exception $e) {
            Log::error('PayrollPeriod store error: ' . $e->getMessage());
            return back()->with('error', 'Gagal membuat periode.');
        }
    }

    public function close(string $id)
    {
           $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePayrollPeriod')) {
            abort(403, 'Unauthorized');
        }   
        $period = PayrollPeriod::findOrFail($id);

        if (!$period->isOpen()) {
            return back()->with('error', 'Periode bukan berstatus open.');
        }

        try {
            $period->update(['status' => 'closed']);
            return back()->with('success', 'Periode berhasil di-close.');
        } catch (\Exception $e) {
            Log::error('PayrollPeriod close error: ' . $e->getMessage());
            return back()->with('error', 'Gagal close periode.');
        }
    }

    public function lock(string $id)
    {
           $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePayrollPeriod')) {
            abort(403, 'Unauthorized');
        }   
        $period = PayrollPeriod::findOrFail($id);

        if (!$period->isClosed()) {
            return back()->with('error', 'Periode harus berstatus closed sebelum di-lock.');
        }
        try {
            $period->update([
                'status'    => 'locked',
                'locked_by' => $user->employee_id,
                'locked_at' => now(),
            ]);
            return back()->with('success', 'Periode berhasil di-lock.');
        } catch (\Exception $e) {
            Log::error('PayrollPeriod lock error: ' . $e->getMessage());
            return back()->with('error', 'Gagal lock periode.');
        }
    }
}