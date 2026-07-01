<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Stores;
use App\Models\Position;
use App\Models\PayrollDetail;
use App\Models\Grading;
use App\Models\PayrollPeriod;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\PayrollExport;
use App\Imports\AttendanceImport;
use App\Models\Company;
use App\Models\Departments;
use Maatwebsite\Excel\Facades\Excel;
use App\Mail\PayrollSlipMail;
use App\Services\PayrollSlipService;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendPayrollSlipJob;


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
        $companies = Company::orderBy('name')->get();
        $departments = Departments::orderBy('department_name')->get();
        $positions = Position::orderBy('name')->get();
        // $statuses = Employee::orderBy('status')->get();
        $statuses = Employee::getStatusOptions();

        $gradings = Grading::orderBy('grading_name')->get();

        return view('pages.Payroll.index', compact('statuses', 'gradings', 'positions', 'departments', 'companies', 'period', 'stats', 'stores'));
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

        $query = Payroll::with([
            'employee:id,employee_name,employee_pengenal,status_employee,status,company_id,grading_id',
            'employee.store' => fn($q) => $q->wherePivot('is_primary', true),
            'employee.department' => fn($q) => $q->wherePivot('is_primary', true),
            'employee.position' => fn($q) => $q->wherePivot('is_primary', true),
            'employee.company:id,name',
            'employee.grading:id,grading_name',
            'details.component',
        ])
            ->where('payroll_period_id', $period->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('status_type')) {
            $query->whereHas(
                'employee',
                fn($q) =>
                $q->where('status_employee', $request->status_type)
            );
        }
        if ($request->filled('company_name')) {
            $query->whereHas(
                'employee.company',
                fn($q) =>
                $q->where('name', $request->company_name)
            );
        }
        if ($request->filled('status_employee')) {
            $query->whereHas(
                'employee',
                fn($q) =>
                $q->where('status', $request->status_employee)
            );
        }

        if ($request->filled('grading_name')) {
            $query->whereHas(
                'employee.grading',
                fn($q) =>
                $q->where('grading_name', $request->grading_name)
            );
        }


        if ($request->filled('store_name')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->whereHas(
                    'store',
                    fn($sq) =>
                    $sq->where('stores_tables.name', $request->store_name)
                        ->where('employee_stores.is_primary', true)
                );
            });
        }
        if ($request->filled('department_name')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->whereHas(
                    'department',
                    fn($sq) =>
                    $sq->where('departments_tables.department_name', $request->department_name)
                        ->where('employee_departments.is_primary', true)
                );
            });
        }
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->whereHas('employee', function ($eq) use ($search) {
                    $eq->where('employee_name', 'like', '%' . $search . '%')
                        ->orWhere('employee_pengenal', 'like', '%' . $search . '%');
                })
                    ->orWhereHas(
                        'employee.store',
                        fn($sq) =>
                        $sq->where('stores_tables.name', 'like', '%' . $search . '%')
                            ->where('employee_stores.is_primary', true)
                    )
                    ->orWhereHas(
                        'employee.department',
                        fn($sq) =>
                        $sq->where('departments_tables.department_name', 'like', '%' . $search . '%')
                            ->where('employee_departments.is_primary', true)
                    );
            });
        }

        return DataTables::of($query)
            ->addColumn('employee_name', fn($row) => $row->employee->employee_name ?? '-')
            ->addColumn('employee_pengenal', fn($row) => $row->employee->employee_pengenal ?? '-')
            ->addColumn('status_type', fn($row) => $row->employee->status_employee ?? '-')
            ->addColumn('status_employee', fn($row) => $row->employee->status ?? '-')
            ->addColumn('company_name', fn($row) => $row->employee->company->name ?? '-')
            ->addColumn('grading_name', fn($row) => $row->employee->grading->grading_name ?? '-')
            ->addColumn('store_name', fn($row) => $row->employee->store->first()?->name ?? '-')
            ->addColumn('department_name', fn($row) => $row->employee->department->first()?->department_name ?? '-')
            ->addColumn('position_name', fn($row) => $row->employee->position->first()?->name ?? '-')
            ->addColumn('status_badge', fn($row) => match ($row->status) {
                'draft'    => '<span class="status-badge" style="background:#f1f5f9;color:#475569">Draft</span>',
                'approved' => '<span class="status-badge" style="background:#f0fdf4;color:#166534">Approved</span>',
                'paid'     => '<span class="status-badge" style="background:#eff6ff;color:#1e40af">Paid</span>',
                default    => $row->status,
            })
            ->addColumn('status_type_badge', fn($row) => match ($row->employee->status_employee ?? '') {
                'PKWT'           => '<span class="status-badge" style="background:#eff6ff;color:#1e40af">PKWT</span>',
                'On Job Training' => '<span class="status-badge" style="background:#fdf4ff;color:#6b21a8">OJT</span>',
                'DW'             => '<span class="status-badge" style="background:#fffbeb;color:#92400e">DW</span>',
                default          => '-',
            })
            ->addColumn('status_employee_badge', fn($row) => match ($row->employee->status ?? '') {
                'Active'           => '<span class="status-badge" style="background:#eff6ff;color:#1e40af">PKWT</span>',
                'Inactive' => '<span class="status-badge" style="background:#fdf4ff;color:#6b21a8">OJT</span>',
                'On Leave'             => '<span class="status-badge" style="background:#fffbeb;color:#92400e">DW</span>',
                'Resign'             => '<span class="status-badge" style="background:#fffbeb;color:#92400e">DW</span>',
                'Mutation'             => '<span class="status-badge" style="background:#fffbeb;color:#92400e">DW</span>',
                default          => '-',
            })
            ->addColumn(
                'basic_salary_fmt',
                fn($row) =>
                number_format($row->basic_salary, 0, ',', '.')
            )
            ->addColumn(
                'position_allowance_fmt',
                fn($row) =>
                number_format($row->position_allowance, 0, ',', '.')
            )
            ->addColumn(
                'daily_rate_fmt',
                fn($row) =>
                number_format($row->daily_rate, 0, ',', '.')
            )
            ->addColumn(
                'gross_salary_fmt',
                fn($row) =>
                number_format($row->gross_salary, 0, ',', '.')
            )
            ->addColumn(
                'meal_allowance_fmt',
                fn($row) =>
                number_format(
                    $row->details->where('component.component_name', 'MEAL ALLOWANCE')->first()?->amount ?? 0,
                    0,
                    ',',
                    '.'
                )
            )
            ->addColumn(
                'transport_allowance_fmt',
                fn($row) =>
                number_format(
                    $row->details->where('component.component_name', 'TRANSPORT ALLOWANCE')->first()?->amount ?? 0,
                    0,
                    ',',
                    '.'
                )
            )
            ->addColumn(
                'house_allowance_fmt',
                fn($row) =>
                number_format(
                    $row->details->where('component.component_name', 'HOUSE ALLOWANCE')->first()?->amount ?? 0,
                    0,
                    ',',
                    '.'
                )
            )
            ->addColumn(
                'overtime_amount_fmt',
                fn($row) =>
                number_format($row->overtime_amount, 0, ',', '.')
            )
            ->addColumn(
                'reimburse_amount_fmt',
                fn($row) =>
                number_format($row->reimburse_amount, 0, ',', '.')
            )
            ->addColumn(
                'total_income_fmt',
                fn($row) =>
                number_format($row->total_income, 0, ',', '.')
            )
            ->addColumn(
                'bpjs_kesehatan_fmt',
                fn($row) =>
                number_format(
                    $row->details->where('component.component_name', 'BPJS KESEHATAN')->first()?->amount ?? 0,
                    0,
                    ',',
                    '.'
                )
            )
            ->addColumn(
                'bpjs_ketenagakerjaan_fmt',
                fn($row) =>
                number_format(
                    $row->details->where('component.component_name', 'BPJS KETENAGAKERJAAN')->first()?->amount ?? 0,
                    0,
                    ',',
                    '.'
                )
            )
            ->addColumn(
                'punishment_fmt',
                fn($row) =>
                number_format($row->punishment, 0, ',', '.')
            )
            ->addColumn(
                'punishment_so_fmt',
                fn($row) =>
                number_format($row->punishment_so, 0, ',', '.')
            )
            ->addColumn(
                'debt_fmt',
                fn($row) =>
                number_format($row->debt, 0, ',', '.')
            )
            ->addColumn(
                'tax_fmt',
                fn($row) =>
                number_format($row->tax, 0, ',', '.')
            )
            ->addColumn(
                'total_deduction_fmt',
                fn($row) =>
                number_format($row->total_deduction, 0, ',', '.')
            )
            ->addColumn(
                'net_salary_fmt',
                fn($row) =>
                number_format($row->net_salary, 0, ',', '.')
            )
            ->addColumn(
                'prorate_info',
                fn($row) =>
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
                    $actions .= '<button
            class="act-btn act-danger btn-delete-single"
            data-id="' . $row->id . '"
            title="Delete">
            <i class="fas fa-trash"></i>
        </button>';
                }
                if (in_array($row->status, ['approved', 'paid'])) {
        $actions .= '<a href="' . route('payroll.slip', $row->id) . '"
            class="act-btn act-info" title="Download Slip" target="_blank">
            <i class="fas fa-file-pdf"></i>
        </a>';
        $actions .= '<button class="act-btn act-primary btn-send-slip" data-id="' . $row->id . '" title="Kirim Email Slip">
            <i class="fas fa-paper-plane"></i>
        </button>';
    }
                return '<div class="action-wrap">' . $actions . '</div>';
            })
            ->rawColumns(['status_badge', 'status_type_badge', 'status_employee_badge', 'prorate_info', 'action'])
            ->make(true);
    }
    public function destroy(string $id)
{
    $user = auth()->user();
    /** @var \App\Models\User|null $user */
    if (!$user->hasPermissionTo('ManagePayroll')) {
        abort(403, 'Unauthorized');
    }

    $payroll = Payroll::findOrFail($id);

    if ($payroll->status !== 'draft') {
        return response()->json([
            'success' => false,
            'message' => 'Hanya payroll berstatus draft yang bisa dihapus.',
        ], 422);
    }

    DB::beginTransaction();
    try {
        $payroll->details()->delete(); // ← hapus PayrollDetail dulu
        $payroll->delete();            // ← baru hapus Payroll
        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Payroll berhasil dihapus.',
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('PayrollController destroy error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Gagal menghapus payroll.',
        ], 500);
    }
}

public function destroyBulk(Request $request)
{
    $user = auth()->user();
    /** @var \App\Models\User|null $user */
    if (!$user->hasPermissionTo('ManagePayroll')) {
        abort(403, 'Unauthorized');
    }

    $request->validate([
        'ids'   => 'required|array|min:1',
        'ids.*' => 'exists:payrolls,id',
    ]);

    $payrolls = Payroll::whereIn('id', $request->ids)
        ->where('status', 'draft') // ← hanya draft
        ->get();

    if ($payrolls->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'Tidak ada payroll draft yang bisa dihapus.',
        ], 422);
    }

    DB::beginTransaction();
    try {
        $ids = $payrolls->pluck('id')->toArray();

        // Hapus PayrollDetail dulu
        PayrollDetail::whereIn('payroll_id', $ids)->delete();

        // Baru hapus Payroll
        Payroll::whereIn('id', $ids)->delete();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => count($ids) . ' payroll berhasil dihapus.',
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('PayrollController destroyBulk error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Gagal menghapus payroll.',
        ], 500);
    }
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


    public function update(Request $request, string $id)
    {
        $user = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePayroll')) {
            abort(403, 'Unauthorized');
        }

        $payroll = Payroll::findOrFail($id);

        if ($payroll->status !== 'draft') {
            return back()->with('error', 'Hanya payroll berstatus draft yang bisa diedit.');
        }

        foreach (['overtime_amount', 'reimburse_amount', 'punishment', 'punishment_so', 'debt', 'tax'] as $field) {
            if ($request->filled($field)) {
                $request->merge([$field => str_replace('.', '', $request->input($field))]);
            }
        }

        $request->validate([
            'attendance_days'  => 'nullable|numeric|min:0',
            'working_days'  => 'nullable|numeric|min:0',
            'overtime_amount'  => 'nullable|numeric|min:0',
            'reimburse_amount' => 'nullable|numeric|min:0',
            'punishment'       => 'nullable|numeric|min:0',
            'punishment_so'    => 'nullable|numeric|min:0',
            'debt'             => 'nullable|numeric|min:0',
            'tax'              => 'nullable|numeric|min:0',
            'note'             => 'nullable|string|max:500',
        ]);
        try {
            $payroll->update([
                'attendance_days'  => $request->attendance_days  ?? 0,
                'working_days'  => $request->working_days  ?? 0,
                'overtime_amount'  => $request->overtime_amount  ?? 0,
                'reimburse_amount' => $request->reimburse_amount ?? 0,
                'punishment'       => $request->punishment       ?? 0,
                'punishment_so'    => $request->punishment_so    ?? 0,
                'debt'             => $request->debt             ?? 0,
                'tax'              => $request->tax              ?? 0,
                'note'             => $request->note,
            ]);

            $this->payrollService->recalculateNet($payroll);

            return redirect()
                ->route('payroll.show', $id)
                ->with('success', 'Payroll berhasil diupdate.');
        } catch (\Exception $e) {
            Log::error('PayrollController update error: ' . $e->getMessage());
            return back()->with('error', 'Gagal update payroll.')->withInput();
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
                    'approved_by' => $user->employee_id,
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
                $request->department_name,
                $request->company_name,
                $request->grading_name,
            ),
            'payroll_' . $period->period_label . '_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function importAttendance(Request $request, string $periodId)
    {
        $period = PayrollPeriod::findOrFail($periodId);

        if (!$period->isOpen()) {
            return back()->with('error', 'Periode bukan berstatus open.');
        }

        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            $import = new AttendanceImport($periodId, $this->payrollService);
            Excel::import($import, $request->file('file'));

            $message = 'Import selesai. '
                . 'Berhasil: ' . count($import->updated) . ' | '
                . 'Dilewati: ' . count($import->skipped);

            return back()
                ->with('success', $message)
                ->with('skipped', $import->skipped);
        } catch (\Exception $e) {
            Log::error('AttendanceImport error: ' . $e->getMessage());
            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }
    public function downloadAttendanceTemplate(string $periodId)
{
    $period = PayrollPeriod::findOrFail($periodId);

    $payrolls = Payroll::with('employee:id,employee_name,employee_pengenal,status_employee')
        ->where('payroll_period_id', $periodId)
        ->where('status', 'draft')
        ->get()
        ->sortBy(fn($p) => match (strtoupper($p->employee->status_employee ?? '')) {
            'PKWT'            => 1,
            'ON JOB TRAINING' => 2,
            'DW'              => 3,
            default           => 4,
        });

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet       = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Attendance');

    // Header
    $sheet->fromArray([[
        'employee_pengenal',
        'employee_name',
        'status_employee',
        'working_days',
        'attendance_days',
        'overtime_amount',
        'reimburse_amount',
        'punishment',
        'punishment_so',
        'debt',
        'tax',
    ]], null, 'A1');

    // Style header A1:K1
    $sheet->getStyle('A1:K1')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => [
            'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => '1d4ed8'],
        ],
    ]);

    // Data per employee
    $row = 2;
    foreach ($payrolls as $payroll) {
        $statusEmp = strtoupper($payroll->employee->status_employee ?? '');
        $isDW      = $statusEmp === 'DW';

        $sheet->fromArray([[
            $payroll->employee->employee_pengenal ?? '-',
            $payroll->employee->employee_name     ?? '-',
            $payroll->employee->status_employee   ?? '-',
            $isDW ? 0 : $payroll->working_days,    // ← DW = 0
            $payroll->attendance_days,
            $payroll->overtime_amount  ?? 0,
            $payroll->reimburse_amount ?? 0,
            $payroll->punishment       ?? 0,
            $payroll->punishment_so    ?? 0,
            $payroll->debt             ?? 0,
            $payroll->tax              ?? 0,
        ]], null, 'A' . $row);

        $rowColor = match ($statusEmp) {
            'PKWT'            => 'eff6ff',
            'ON JOB TRAINING' => 'fdf4ff',
            'DW'              => 'fffbeb',
            default           => 'ffffff',
        };

        $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => $rowColor],
            ],
        ]);
        $row++;
    }
    // Lock kolom A, B, C
    $sheet->getStyle('A2:C' . ($row - 1))->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'e2e8f0'],
        ],
    ]);
    // Border A1:K
    $sheet->getStyle('A1:K' . ($row - 1))->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color'       => ['rgb' => 'cbd5e1'],
            ],
        ],
    ]);

    // Auto width A sampai K
    foreach (range('A', 'K') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $sheet->freezePane('A2');

    $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $filename = tempnam(sys_get_temp_dir(), 'attendance_template');
    $writer->save($filename);

    return response()->download(
        $filename,
        'attendance_' . $period->period_label . '.xlsx'
    )->deleteFileAfterSend(true);
}

public function downloadSlip(string $id)
{
    $payroll = Payroll::with(['employee.company', 'details.component'])->findOrFail($id);
    $pdf = app(PayrollSlipService::class)->generateSingle($payroll);

    return $pdf->download('Slip_Gaji_' . $payroll->employee->employee_pengenal . '_' . $payroll->period_month . $payroll->period_year . '.pdf');
}
// public function downloadSlipBulk(Request $request, string $periodId)
// {
//     $period = PayrollPeriod::findOrFail($periodId);

//     $payrolls = Payroll::with(['employee.company', 'details.component'])
//         ->where('payroll_period_id', $periodId)
//         ->whereIn('status', ['approved', 'paid']) // ← hanya yang sudah final
//         ->get();

//     if ($request->filled('ids')) {
//         $payrolls = $payrolls->whereIn('id', $request->ids);
//     }

//     $pdf = app(PayrollSlipService::class)->generateBulk($payrolls);

//     return $pdf->download('Slip_Gaji_Bulk_' . $period->period_label . '.pdf');
// }
public function downloadSlipBulk(Request $request, string $periodId)
{
    $period = PayrollPeriod::findOrFail($periodId);

    $query = Payroll::with(['employee.company', 'employee.bank', 'details.component'])
        ->where('payroll_period_id', $periodId)
        ->whereIn('status', ['approved', 'paid']);

    if ($request->filled('ids')) {
        $query->whereIn('id', $request->ids);
    }

    $payrolls = $query->get();

    if ($payrolls->isEmpty()) {
        return back()->with('error', 'Tidak ada payroll approved/paid untuk didownload.');
    }

    $service   = app(\App\Services\PayrollSlipService::class);
    $tempDir   = storage_path('app/temp-slips');
    $zipPath   = $tempDir . '/Slip_Gaji_' . $period->period_label . '_' . time() . '.zip';

    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0755, true);
    }

    $zip = new \ZipArchive();
    $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

    foreach ($payrolls as $payroll) {
        $pdf      = $service->generateSingle($payroll);
        $filename = 'Slip_' . $payroll->employee->employee_pengenal . '_' . $payroll->period_month . $payroll->period_year . '.pdf';
        $tempPdf  = $tempDir . '/' . uniqid() . '_' . $filename;

        $pdf->save($tempPdf);
        $zip->addFile($tempPdf, $filename);
    }

    $zip->close();

    // Hapus file PDF temp setelah di-zip
    foreach (glob($tempDir . '/????????????????????_Slip_*.pdf') as $file) {
        unlink($file);
    }

    return response()->download($zipPath, 'Slip_Gaji_' . $period->period_label . '.zip')
        ->deleteFileAfterSend(true);
}

public function sendSlipEmail(string $id)
{
    $payroll = Payroll::with('employee')->findOrFail($id);
    if (!$payroll->employee->email) {
        return back()->with('error', 'Email karyawan tidak tersedia.');
    }
    SendPayrollSlipJob::dispatch($payroll->id)->onQueue('payrollslip');
    return back()->with('success', "Slip gaji {$payroll->employee->employee_name} sedang dikirim di background.");
}

public function sendSlipEmailBulk(Request $request, string $periodId)
{
    $request->validate(['ids' => 'required|array|min:1']);
    $payrolls = Payroll::with('employee')
        ->whereIn('id', $request->ids)
        ->where('payroll_period_id', $periodId)
        ->whereIn('status', ['approved', 'paid'])
        ->get();
    $dispatched = 0;
    $skipped    = [];
    foreach ($payrolls as $payroll) {
        if (!$payroll->employee->email) {
            $skipped[] = $payroll->employee->employee_name;
            continue;
        }

        SendPayrollSlipJob::dispatch($payroll->id)->onQueue('payrollslip');
        $dispatched++;
    }

    return response()->json([
        'success' => true,
        'message' => "{$dispatched} slip masuk antrian pengiriman."
            . (count($skipped) ? ' Dilewati (no email): ' . implode(', ', $skipped) : ''),
    ]);
}


//     public function downloadAttendanceTemplate(string $periodId)
//     {
//         $period = PayrollPeriod::findOrFail($periodId);

//         // Ambil semua employee yang sudah di-generate, urutkan berdasarkan status_employee
//         $payrolls = Payroll::with('employee:id,employee_name,employee_pengenal,status_employee')
//             ->where('payroll_period_id', $periodId)
//             ->where('status', 'draft')
//             ->get()
//             ->sortBy(fn($p) => match (strtoupper($p->employee->status_employee ?? '')) {
//                 'PKWT'            => 1,
//                 'ON JOB TRAINING' => 2,
//                 'DW'              => 3,
//                 default           => 4,
//             });

//         $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
//         $sheet       = $spreadsheet->getActiveSheet();
//         $sheet->setTitle('Attendance');

//         // Header
//         $sheet->fromArray([[
//             'employee_pengenal',
//             'employee_name',
//             'status_employee',
//              'working_days',
//     'attendance_days',
//     'overtime_amount',
//             'reimburse_amount',
//             'punishment',
//             'punishment_so',
//             'debt',
//             'tax',
//         ]], null, 'A1');

//         // Style header A1:K1
//         $sheet->getStyle('A1:H1')->applyFromArray([
//             'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
//             'fill' => [
//                 'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
//                 'startColor' => ['rgb' => '1d4ed8'],
//             ],
//         ]);

//         // Data per employee
//         $row = 2;
//         foreach ($payrolls as $payroll) {
//             $statusEmp = strtoupper($payroll->employee->status_employee ?? '');

          
//             $sheet->fromArray([[
//     $payroll->employee->employee_pengenal ?? '-',
//     $payroll->employee->employee_name     ?? '-',
//     $payroll->employee->status_employee   ?? '-',
//       $statusEmp === 'DW' ? '' : $payroll->working_days,
//     $payroll->attendance_days,
//     $payroll->overtime_amount  ?? 0,
//     $payroll->reimburse_amount ?? 0, 
//     $payroll->punishment       ?? 0,
//     $payroll->punishment_so    ?? 0,
//     $payroll->debt             ?? 0,
//     $payroll->tax              ?? 0,
// ]], null, 'A' . $row);

//             // Warna baris berdasarkan status_employee
//             $rowColor = match ($statusEmp) {
//                 'PKWT'            => 'eff6ff', // biru muda
//                 'ON JOB TRAINING' => 'fdf4ff', // ungu muda
//                 'DW'              => 'fffbeb', // kuning muda
//                 default           => 'ffffff',
//             };

//             $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
//                 'fill' => [
//                     'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
//                     'startColor' => ['rgb' => $rowColor],
//                 ],
//             ]);

//             $row++;
//         }

//         // Lock kolom A, B, C (penanda, tidak perlu diedit)
//         $sheet->getStyle('A2:C' . ($row - 1))->applyFromArray([
//             'font' => ['bold' => true],
//             'fill' => [
//                 'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
//                 'startColor' => ['rgb' => 'e2e8f0'],
//             ],
//         ]);

//         // Border semua data
//         $sheet->getStyle('A1:H' . ($row - 1))->applyFromArray([
//             'borders' => [
//                 'allBorders' => [
//                     'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
//                     'color'       => ['rgb' => 'cbd5e1'],
//                 ],
//             ],
//         ]);

//         // Auto width
//         foreach (range('A', 'H') as $col) {
//             $sheet->getColumnDimension($col)->setAutoSize(true);
//         }

//         // Freeze header row
//         $sheet->freezePane('A2');

//         $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
//         $filename = tempnam(sys_get_temp_dir(), 'attendance_template');
//         $writer->save($filename);

//         return response()->download(
//             $filename,
//             'attendance_' . $period->period_label . '.xlsx'
//         )->deleteFileAfterSend(true);
//     }
    // public function downloadAttendanceTemplate(string $periodId)
    // {
    //     $period = PayrollPeriod::findOrFail($periodId);

    //     // Ambil semua employee yang sudah di-generate, urutkan berdasarkan status_employee
    //     $payrolls = Payroll::with('employee:id,employee_name,employee_pengenal,status_employee')
    //         ->where('payroll_period_id', $periodId)
    //         ->where('status', 'draft')
    //         ->get()
    //         ->sortBy(fn($p) => match (strtoupper($p->employee->status_employee ?? '')) {
    //             'PKWT'            => 1,
    //             'ON JOB TRAINING' => 2,
    //             'DW'              => 3,
    //             default           => 4,
    //         });

    //     $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    //     $sheet       = $spreadsheet->getActiveSheet();
    //     $sheet->setTitle('Attendance');

    //     // Header
    //     $sheet->fromArray([[
    //         'employee_pengenal',
    //         'employee_name',
    //         'status_employee',
    //         'working_days',
    //         'attendance_days',
    //         'overtime_amount',
    //         'reimburse_amount',
    //         'punishment',
    //         'punishment_so',
    //         'debt',
    //         'tax',
    //     ]], null, 'A1');

    //     // Style header A1:K1
    //     $sheet->getStyle('A1:K1')->applyFromArray([
    //         'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    //         'fill' => [
    //             'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
    //             'startColor' => ['rgb' => '1d4ed8'],
    //         ],
    //     ]);

    //     // Data per employee
    //     $row = 2;
    //     foreach ($payrolls as $payroll) {
    //         $statusEmp = strtoupper($payroll->employee->status_employee ?? '');

    //         $sheet->fromArray([[
    //             $payroll->employee->employee_pengenal ?? '-',
    //             $payroll->employee->employee_name     ?? '-',
    //             $payroll->employee->status_employee   ?? '-',
    //             $statusEmp === 'DW' ? '' : $payroll->working_days,
    //             $payroll->attendance_days,
    //             $payroll->overtime_amount  ?? 0,
    //             $payroll->reimburse_amount ?? 0,
    //             $payroll->punishment       ?? 0,
    //             $payroll->punishment_so    ?? 0,
    //             $payroll->debt             ?? 0,
    //             $payroll->tax              ?? 0,
    //         ]], null, 'A' . $row);

    //         // Warna baris berdasarkan status_employee
    //         $rowColor = match ($statusEmp) {
    //             'PKWT'            => 'eff6ff', // biru muda
    //             'ON JOB TRAINING' => 'fdf4ff', // ungu muda
    //             'DW'              => 'fffbeb', // kuning muda
    //             default           => 'ffffff',
    //         };

    //         $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
    //             'fill' => [
    //                 'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
    //                 'startColor' => ['rgb' => $rowColor],
    //             ],
    //         ]);

    //         $row++;
    //     }

    //     // Lock kolom A, B, C (penanda, tidak perlu diedit)
    //     $sheet->getStyle('A2:C' . ($row - 1))->applyFromArray([
    //         'font' => ['bold' => true],
    //         'fill' => [
    //             'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
    //             'startColor' => ['rgb' => 'e2e8f0'],
    //         ],
    //     ]);

    //     // Border semua data
    //     $sheet->getStyle('A1:K' . ($row - 1))->applyFromArray([
    //         'borders' => [
    //             'allBorders' => [
    //                 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
    //                 'color'       => ['rgb' => 'cbd5e1'],
    //             ],
    //         ],
    //     ]);

    //     // Auto width
    //     foreach (range('A', 'K') as $col) {
    //         $sheet->getColumnDimension($col)->setAutoSize(true);
    //     }

    //     // Freeze header row
    //     $sheet->freezePane('A2');

    //     $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    //     $filename = tempnam(sys_get_temp_dir(), 'attendance_template');
    //     $writer->save($filename);

    //     return response()->download(
    //         $filename,
    //         'attendance_' . $period->period_label . '.xlsx'
    //     )->deleteFileAfterSend(true);
    // }
}
