<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Stores;
use App\Models\PayrollPeriod;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\PayrollExport;
use Maatwebsite\Excel\Facades\Excel;

class PayrollController extends Controller
{
    protected PayrollService $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    // ── Index per periode ──
    // public function index(string $periodId)
    // {
    //      $user     = auth()->user();
    //     /** @var \App\Models\User|null $user */
    //     if (!$user->hasPermissionTo('ManagePayroll')) {
    //         abort(403, 'Unauthorized');
    //     }        
    //     $period = PayrollPeriod::findOrFail($periodId);
    //     return view('payroll.index', compact('period'));
    // }
    public function index(string $periodId)
{
          $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePayroll')) {
            abort(403, 'Unauthorized');
        }        
    $period = PayrollPeriod::findOrFail($periodId);
    $stats = [
        'draft'     => Payroll::where('payroll_period_id', $periodId)->where('status', 'draft')->count(),
        'approved'  => Payroll::where('payroll_period_id', $periodId)->where('status', 'approved')->count(),
        'paid'      => Payroll::where('payroll_period_id', $periodId)->where('status', 'paid')->count(),
        'total_net' => Payroll::where('payroll_period_id', $periodId)->sum('net_salary'),
    ];
    $stores = Stores::orderBy('name')->get();

    return view('pages.Payroll.index', compact('period', 'stats', 'stores'));
}

    // ── DataTable ──
    public function getPayroll(Request $request, string $periodId)
    {
        $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePayroll')) {
            abort(403, 'Unauthorized');
        }        
        $period = PayrollPeriod::findOrFail($periodId);

        $query = Payroll::with(['employee:id,employee_name,employee_pengenal,status_employee,store_id', 'employee.store:id,name'])
            ->where('payroll_period_id', $period->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('status_employee')) {
            $query->whereHas('employee', fn($q) =>
                $q->where('status_employee', $request->status_employee)
            );
        }

        if ($request->filled('store_name')) {
            $query->whereHas('employee.store', fn($q) =>
                $q->where('name', $request->store_name)
            );
        }

        return DataTables::of($query)
            ->addColumn('employee_name', fn($row) => $row->employee->employee_name ?? '-')
            ->addColumn('employee_pengenal', fn($row) => $row->employee->employee_pengenal ?? '-')
            ->addColumn('status_employee', fn($row) => $row->employee->status_employee ?? '-')
            ->addColumn('store_name', fn($row) => $row->employee->store->name ?? '-')
            ->addColumn('status_badge', fn($row) => match($row->status) {
                'draft'    => '<span class="status-badge" style="background:#f1f5f9;color:#475569">Draft</span>',
                'approved' => '<span class="status-badge" style="background:#f0fdf4;color:#166534">Approved</span>',
                'paid'     => '<span class="status-badge" style="background:#eff6ff;color:#1e40af">Paid</span>',
                default    => $row->status,
            })
            ->addColumn('status_employee_badge', fn($row) => match($row->employee->status_employee ?? '') {
                'PKWT'           => '<span class="status-badge" style="background:#eff6ff;color:#1e40af">PKWT</span>',
                'On Job Training'=> '<span class="status-badge" style="background:#fdf4ff;color:#6b21a8">OJT</span>',
                'DW'             => '<span class="status-badge" style="background:#fffbeb;color:#92400e">DW</span>',
                default          => '-',
            })
            ->addColumn('gross_salary_fmt', fn($row) =>
                number_format($row->gross_salary, 0, ',', '.')
            )
            ->addColumn('total_deduction_fmt', fn($row) =>
                number_format($row->total_deduction, 0, ',', '.')
            )
            ->addColumn('net_salary_fmt', fn($row) =>
                number_format($row->net_salary, 0, ',', '.')
            )
            ->addColumn('prorate_info', fn($row) =>
                $row->is_prorate
                    ? '<span class="status-badge" style="background:#fff7ed;color:#c2410c">Prorate ' . ($row->prorate_ratio * 100) . '%</span>'
                    : '-'
            )
            ->addColumn('action', function ($row) {
                $actions = '<a href="' . route('payroll.show', $row->id) . '"
                    class="act-btn" title="Detail">
                    <i class="fas fa-eye"></i>
                </a>';

                if ($row->status === 'draft') {
                    $actions .= '<a href="' . route('payroll.edit', $row->id) . '"
                        class="act-btn act-warning" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>';

                    $actions .= '<button
                        class="act-btn act-success btn-approve"
                        data-id="' . $row->id . '"
                        title="Approve">
                        <i class="fas fa-check"></i>
                    </button>';
                }

                return '<div class="action-wrap">' . $actions . '</div>';
            })
            ->rawColumns(['status_badge', 'status_employee_badge', 'prorate_info', 'action'])
            ->make(true);
    }

    // ── Generate semua employee ──
    public function generate(string $periodId)
    {
        $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePayroll')) {
            abort(403, 'Unauthorized');
        }        
        $period = PayrollPeriod::findOrFail($periodId);

        if (!$period->isOpen()) {
            return back()->with('error', 'Periode bukan berstatus open.');
        }

        try {
            $results = $this->payrollService->generateAll($period);

            $message = "Generate selesai. "
                . "Berhasil: " . count($results['success']) . " | "
                . "Dilewati: " . count($results['skipped']) . " | "
                . "Gagal: " . count($results['failed']);

            // Log failed employees
            if (!empty($results['failed'])) {
                Log::warning('PayrollController: failed employees', $results['failed']);
            }

            return redirect()
                ->route('payroll.index', $periodId)
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('PayrollController generate error: ' . $e->getMessage());
            return back()->with('error', 'Gagal generate payroll: ' . $e->getMessage());
        }
    }

    // ── Generate per employee ──
    public function generateOne(Request $request, string $periodId)
    {
        $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePayroll')) {
            abort(403, 'Unauthorized');
        }        
        $period   = PayrollPeriod::findOrFail($periodId);
        $employee = Employee::findOrFail($request->employee_id);

        if (!$period->isOpen()) {
            return back()->with('error', 'Periode bukan berstatus open.');
        }

        try {
            $result = $this->payrollService->generateForEmployee($period, $employee);

            if ($result === 'skipped') {
                return back()->with('error', "Employee {$employee->employee_name} dilewati. Cek salary atau roster.");
            }

            return back()->with('success', "Payroll {$employee->employee_name} berhasil di-generate.");

        } catch (\Exception $e) {
            Log::error('PayrollController generateOne error: ' . $e->getMessage());
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    // ── Show detail ──
    public function show(string $id)
    {
        $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePayroll')) {
            abort(403, 'Unauthorized');
        }        
        $payroll = Payroll::with([
            'employee',
            'employee.store',
            'details.component',
        ])->findOrFail($id);

        return view('pages.Payroll.show', compact('payroll'));
    }

    // ── Edit (koreksi manual) ──
    public function edit(string $id)
    {
        $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePayroll')) {
            abort(403, 'Unauthorized');
        }        
        $payroll = Payroll::with([
            'employee',
            'details.component',
        ])->findOrFail($id);

        if ($payroll->status !== 'draft') {
            return back()->with('error', 'Hanya payroll berstatus draft yang bisa diedit.');
        }

        return view('pages.Payroll.edit', compact('payroll'));
    }

    // ── Update (koreksi manual) ──
    public function update(Request $request, string $id)
    {
        $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePayroll')) {
            abort(403, 'Unauthorized');
        }        
        $payroll = Payroll::findOrFail($id);

        if ($payroll->status !== 'draft') {
            return back()->with('error', 'Hanya payroll berstatus draft yang bisa diedit.');
        }

        $request->validate([
            'overtime_amount'  => 'nullable|numeric|min:0',
            'reimburse_amount' => 'nullable|numeric|min:0',
            'is_prorate'       => 'nullable|boolean',
            'prorate_days'     => 'nullable|integer|min:0',
            'note'             => 'nullable|string|max:500',
        ]);

        try {
            $payroll->update([
                'overtime_amount'  => $request->overtime_amount  ?? 0,
                'reimburse_amount' => $request->reimburse_amount ?? 0,
                'is_prorate'       => $request->boolean('is_prorate'),
                'prorate_days'     => $request->prorate_days,
                'prorate_ratio'    => $payroll->working_days > 0
                    ? round($request->prorate_days / $payroll->working_days, 4)
                    : null,
                'note'             => $request->note,
            ]);

            // Recalculate gross & net setelah edit
            $this->payrollService->recalculateNet($payroll);

            return redirect()
                ->route('payroll.show', $id)
                ->with('success', 'Payroll berhasil diupdate.');

        } catch (\Exception $e) {
            Log::error('PayrollController update error: ' . $e->getMessage());
            return back()->with('error', 'Gagal update payroll.');
        }
    }

    // ── Approve ──
    public function approve(string $id)
    {
        $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePayroll')) {
            abort(403, 'Unauthorized');
        }        
        $payroll = Payroll::findOrFail($id);

        if ($payroll->status !== 'draft') {
            return response()->json(['error' => 'Hanya draft yang bisa di-approve.'], 422);
        }

        try {
            $payroll->update([
                'status'      => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Payroll approved.']);

        } catch (\Exception $e) {
            Log::error('PayrollController approve error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal approve.'], 500);
        }
    }

    // ── Approve Bulk ──
    public function approveBulk(Request $request)
    {
        $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePayroll')) {
            abort(403, 'Unauthorized');
        }        
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:payrolls,id',
        ]);

        try {
            Payroll::whereIn('id', $request->ids)
                ->where('status', 'draft')
                ->update([
                    'status'      => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);

            return response()->json(['success' => true, 'message' => 'Bulk approve berhasil.']);

        } catch (\Exception $e) {
            Log::error('PayrollController approveBulk error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal bulk approve.'], 500);
        }
    }

    // ── Paid ──
    public function paid(string $id)
    {
        $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePayroll')) {
            abort(403, 'Unauthorized');
        }        
        $payroll = Payroll::findOrFail($id);

        if ($payroll->status !== 'approved') {
            return response()->json(['error' => 'Hanya approved yang bisa di-paid.'], 422);
        }

        try {
            $payroll->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Payroll paid.']);

        } catch (\Exception $e) {
            Log::error('PayrollController paid error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal paid.'], 500);
        }
    }
    public function export(Request $request, string $periodId)
{
    $period = PayrollPeriod::findOrFail($periodId);

    return Excel::download(
        new PayrollExport(
            $period,
            $request->status,
            $request->status_employee,
            $request->store_name,
        ),
        'payroll_' . $period->period_label . '_' . now()->format('Ymd_His') . '.xlsx'
    );
}
}