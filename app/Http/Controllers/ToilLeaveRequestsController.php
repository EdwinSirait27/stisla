<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Roster;
use App\Models\Toilbalances;
use App\Models\ToilLeaveRequests;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ToilLeaveRequestsController extends Controller
{
    // ════════════════════════════════════════════════════════════════
    //   APPROVAL PAGE (MANAGER ONLY)
    //   - Halaman ini: Manager INPUT klaim TOIL Leave untuk bawahan
    //   - Status langsung 'Approved' (skip Pending)
    //   - Saldo dipotong + roster jadi Off
    // ════════════════════════════════════════════════════════════════

    /**
     * GET /toil/approval
     * Halaman manager untuk INPUT TOIL Leave + lihat history.
     */
    public function approvalIndex()
    {
        $user    = Auth::user();
        $manager = $user->employee;

        // Strict: Manager harus punya structure_id
        if (!$manager || !$manager->structure_id) {
            return redirect()->back()->with('error', 
                'Anda harus terdaftar di struktur organisasi (structuresnew) untuk akses halaman ini.'
            );
        }

        // Ambil daftar bawahan untuk dropdown form
        $subordinates = $this->getSubordinates($manager);

        return view('pages.Toil.approval', compact('manager', 'subordinates'));
    }

    /**
     * GET /toil/approval/data
     * AJAX: list semua TOIL Leave yang dibuat manager ini.
     */
    public function getApprovalData(Request $request)
    {
        $user    = Auth::user();
        $manager = $user->employee;

        $query = ToilLeaveRequests::with([
                'employee:id,employee_name,pin',
                'balance.overtimeSubmission',
            ])
            ->where('approver_id', $manager->id)
            ->orderBy('created_at', 'desc');

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('leave_date', [$request->start_date, $request->end_date]);
        }

        // Filter employee name
        if ($request->filled('employee_search')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('employee_name', 'like', '%' . $request->employee_search . '%');
            });
        }

        $requests = $query->get();

        $data = $requests->map(function ($req) {
            $sub = $req->balance->overtimeSubmission ?? null;
            return [
                'id'              => $req->id,
                'employee_name'   => $req->employee->employee_name ?? '-',
                'employee_pin'    => $req->employee->pin ?? '-',
                'leave_date'      => $req->leave_date->format('d M Y'),
                'hours_used'      => number_format($req->hours_used, 2),
                'work_date'       => $sub ? Carbon::parse($sub->date)->format('d M Y') : '-',
                'reason'          => $req->reason,
                'status'          => $req->status,
                'approved_at'     => $req->approved_at?->format('d M Y H:i'),
                'rejected_reason' => $req->rejected_reason,
                'created_at'      => $req->created_at->format('d M Y H:i'),
                'can_cancel'      => $req->canBeCancelledByManager(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * GET /toil/approval/saldo/{employeeId}
     * AJAX: list saldo TOIL aktif milik karyawan tertentu.
     * Dipakai untuk dropdown saat manager pilih saldo.
     */
    public function getEmployeeSaldoToil(Request $request, $employeeId)
    {
        $user    = Auth::user();
        $manager = $user->employee;

        // Validasi: karyawan harus bawahan manager
        $subordinateIds = $this->getSubordinates($manager)->pluck('id')->toArray();
        if (!in_array($employeeId, $subordinateIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan ini bukan bawahan Anda.',
            ], 403);
        }

        $saldoList = Toilbalances::ofType('Toil')
            ->with('overtimeSubmission')
            ->where('employee_id', $employeeId)
            ->where('status', 'active')
            ->where('expires_at', '>=', today())
            ->orderBy('expires_at', 'asc')
            ->get();

        $data = $saldoList->map(function ($balance) {
            $sub = $balance->overtimeSubmission;
            return [
                'id'              => $balance->id,
                'work_date'       => $sub ? Carbon::parse($sub->date)->format('d M Y') : '-',
                'remaining_hours' => number_format($balance->remaining_hours, 2),
                'expires_at'      => $balance->expires_at->format('d M Y'),
                'days_left'       => $balance->days_until_expired,
                'label'           => sprintf(
                    '%s jam — Lembur %s — Expired %s',
                    number_format($balance->remaining_hours, 2),
                    $sub ? Carbon::parse($sub->date)->format('d M Y') : '-',
                    $balance->expires_at->format('d M Y')
                ),
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * POST /toil/approval
     * Manager INPUT klaim TOIL Leave untuk karyawan + LANGSUNG approve.
     * 
     * Single-step flow:
     * 1. Validasi karyawan = bawahan manager
     * 2. Validasi saldo cukup
     * 3. Create TOIL Leave dengan status 'Approved' (skip Pending)
     * 4. Potong saldo (used_hours += hours_used)
     * 5. Update roster jadi 'Off' + simpan original_shift_id
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id'     => 'required|exists:employees_tables,id',
            'toil_balance_id' => 'required|exists:toil_balances_tables,id',
            'hours_used'      => 'required|numeric|min:0.5|max:8',
            'leave_date'      => 'required|date|after_or_equal:today',
            'reason'          => 'required|string|min:10|max:1000',
        ], [
            'hours_used.min'            => 'Minimal 0.5 jam.',
            'hours_used.max'            => 'Maksimal 8 jam (= 1 hari) per request.',
            'leave_date.after_or_equal' => 'Tanggal libur tidak boleh masa lalu.',
            'reason.min'                => 'Alasan minimal 10 karakter.',
        ]);

        $user    = Auth::user();
        $manager = $user->employee;

        // Strict: Manager harus punya structure_id
        if (!$manager || !$manager->structure_id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus terdaftar di struktur organisasi.',
            ], 403);
        }

        // Validasi: karyawan harus bawahan manager
        $subordinateIds = $this->getSubordinates($manager)->pluck('id')->toArray();
        if (!in_array($validated['employee_id'], $subordinateIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan ini bukan bawahan Anda di struktur organisasi.',
            ], 403);
        }

        // Validasi saldo
        $balance = Toilbalances::with('overtimeSubmission')->findOrFail($validated['toil_balance_id']);

        if ($balance->employee_id !== $validated['employee_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo ini bukan milik karyawan yang dipilih.',
            ], 422);
        }

        if ($balance->overtimeSubmission->compensation_type !== 'Toil') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya saldo TOIL yang bisa diklaim untuk libur. Cash otomatis masuk payroll.',
            ], 422);
        }

        if ($balance->status !== 'active' || $balance->expires_at < today()) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo tidak aktif atau sudah expired.',
            ], 422);
        }

        if ($balance->remaining_hours < $validated['hours_used']) {
            return response()->json([
                'success' => false,
                'message' => "Saldo tidak cukup. Tersisa {$balance->remaining_hours} jam.",
            ], 422);
        }

        try {
            DB::beginTransaction();

            // 1. Cari roster di tanggal leave untuk simpan shift original
            $roster = Roster::where('employee_id', $validated['employee_id'])
                ->whereDate('date', $validated['leave_date'])
                ->first();

            $originalShiftId = $roster?->shift_id;

            // 2. Create TOIL Leave LANGSUNG dengan status 'Approved'
            $leaveRequest = ToilLeaveRequests::create([
                'employee_id'       => $validated['employee_id'],
                'toil_balance_id'   => $balance->id,
                'approver_id'       => $manager->id,
                'hours_used'        => $validated['hours_used'],
                'leave_date'        => $validated['leave_date'],
                'reason'            => $validated['reason'],
                'original_shift_id' => $originalShiftId,
                'status'            => 'Approved',           // ⭐ Skip Pending, langsung Approved
                'approved_at'       => now(),
            ]);

            // 3. Update saldo: tambah used_hours
            $balance->update([
                'used_hours' => $balance->used_hours + $validated['hours_used'],
            ]);

            // 4. Refresh status balance (auto-handle transisi)
            $balance->refresh();
            $balance->refreshStatus();

            // 5. Update Roster: ubah day_type jadi Off
            if ($roster) {
                $roster->update([
                    'day_type' => 'Off',
                    'shift_id' => null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'TOIL Leave berhasil di-assign. Saldo dipotong & roster ter-update.',
                'data'    => [
                    'id'         => $leaveRequest->id,
                    'leave_date' => $leaveRequest->leave_date->format('d M Y'),
                    'hours_used' => $leaveRequest->hours_used,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ToilLeaveRequests: store ERROR', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PUT /toil/approval/{id}/cancel
     * Manager cancel TOIL Leave yang sudah Approved.
     * 
     * Aturan:
     * - Hanya approver (manager yang assign) yang bisa cancel
     * - Hanya status 'Approved' yang bisa di-cancel
     * - leave_date harus >= today (tidak boleh cancel masa lalu)
     */
    public function cancelApproved(Request $request, $id)
    {
        $request->validate([
            'cancel_reason' => 'required|string|min:10|max:1000',
        ], [
            'cancel_reason.required' => 'Alasan cancel wajib diisi.',
            'cancel_reason.min'      => 'Alasan minimal 10 karakter.',
        ]);

        $manager      = Auth::user()->employee;
        $leaveRequest = ToilLeaveRequests::with('balance')->findOrFail($id);

        if ($leaveRequest->approver_id !== $manager->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak punya akses untuk cancel TOIL Leave ini.',
            ], 403);
        }

        if (!$leaveRequest->canBeCancelledByManager()) {
            return response()->json([
                'success' => false,
                'message' => 'TOIL Leave tidak bisa di-cancel. Pastikan status Approved dan tanggalnya belum lewat.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            // 1. Kembalikan saldo
            $balance = $leaveRequest->balance;
            $balance->update([
                'used_hours' => max(0, $balance->used_hours - $leaveRequest->hours_used),
            ]);

            // 2. Refresh status balance
            $balance->refresh();
            $balance->refreshStatus();

            // 3. Restore roster ke shift original
            $this->restoreRosterFromCancel(
                $leaveRequest->employee_id,
                $leaveRequest->leave_date,
                $leaveRequest->original_shift_id
            );

            // 4. Update status leave request ke Cancelled
            $leaveRequest->update([
                'status'          => 'Cancelled',
                'rejected_reason' => $request->cancel_reason,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'TOIL Leave berhasil di-cancel. Saldo dikembalikan & roster di-restore.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ToilApproval: cancelApproved ERROR', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal cancel: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ════════════════════════════════════════════════════════════════
    //   SUBORDINATES (STRICT STRUCTURESNEW)
    // ════════════════════════════════════════════════════════════════

    /**
     * Ambil bawahan manager via tree structuresnew.
     */
    private function getSubordinates(Employee $manager): \Illuminate\Database\Eloquent\Collection
    {
        if (!$manager->structure_id) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        $structure = \App\Models\Structuresnew::with([
            'allChildren.employee' => function ($q) use ($manager) {
                $q->where('status', 'Active')
                  ->where('id', '!=', $manager->id)
                  ->select('id', 'employee_name', 'pin', 'store_id', 'department_id', 'structure_id');
            },
        ])
        ->find($manager->structure_id);

        if (!$structure) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        return $this->flattenEmployeesFromStructure($structure);
    }

    private function flattenEmployeesFromStructure($structure): \Illuminate\Database\Eloquent\Collection
    {
        $employees = new \Illuminate\Database\Eloquent\Collection();

        foreach ($structure->allChildren ?? [] as $child) {
            if ($child->employee && $child->employee->isNotEmpty()) {
                $employees = $employees->merge($child->employee);
            }
            $deeperEmployees = $this->flattenEmployeesFromStructure($child);
            $employees = $employees->merge($deeperEmployees);
        }

        return $employees;
    }

    // ════════════════════════════════════════════════════════════════
    //   ROSTER RESTORE
    // ════════════════════════════════════════════════════════════════

    private function restoreRosterFromCancel(string $employeeId, $leaveDate, ?string $originalShiftId): void
    {
        $roster = Roster::where('employee_id', $employeeId)
            ->whereDate('date', $leaveDate)
            ->first();

        if (!$roster) {
            return;
        }

        $roster->update([
            'day_type' => 'Work',
            'shift_id' => $originalShiftId,
        ]);
    }


    /*
    public function getActiveSaldoToil(Request $request)
    {
        // [OLD] Karyawan ambil daftar saldo TOIL aktif untuk dropdown klaim
        // SUDAH TIDAK DIPAKAI — karyawan tidak bisa klaim sendiri
        
        $user     = Auth::user();
        $employee = $user->employee;

        $saldoList = Toilbalances::ofType('Toil')
            ->with('overtimeSubmission')
            ->where('employee_id', $employee->id)
            ->where('status', 'active')
            ->where('expires_at', '>=', today())
            ->orderBy('expires_at', 'asc')
            ->get();

        $data = $saldoList->map(function ($balance) {
            $sub = $balance->overtimeSubmission;
            return [
                'id'              => $balance->id,
                'work_date'       => $sub ? Carbon::parse($sub->date)->format('d M Y') : '-',
                'remaining_hours' => number_format($balance->remaining_hours, 2),
                'expires_at'      => $balance->expires_at->format('d M Y'),
                'days_left'       => $balance->days_until_expired,
                'label'           => sprintf(
                    '%s jam — Lembur %s — Expired %s',
                    number_format($balance->remaining_hours, 2),
                    $sub ? Carbon::parse($sub->date)->format('d M Y') : '-',
                    $balance->expires_at->format('d M Y')
                ),
            ];
        });

        return response()->json(['data' => $data]);
    }
    */

    /*
    public function destroy($id)
    {
        // [OLD] Karyawan cancel request Pending sendiri
        // SUDAH TIDAK DIPAKAI — karyawan tidak bisa klaim, jadi tidak ada Pending
        
        $user         = Auth::user();
        $employee     = $user->employee;
        $leaveRequest = ToilLeaveRequests::findOrFail($id);

        if ($leaveRequest->employee_id !== $employee->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak punya akses untuk cancel request ini.',
            ], 403);
        }

        if (!$leaveRequest->canBeCancelledByEmployee()) {
            return response()->json([
                'success' => false,
                'message' => 'Request tidak bisa di-cancel.',
            ], 422);
        }

        $leaveRequest->update(['status' => 'Cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Request berhasil di-cancel.',
        ]);
    }
    */

    /*
    public function approve(Request $request, $id)
    {
        // [OLD] Manager approve request Pending
        // SUDAH TIDAK DIPAKAI — store() langsung create dengan status 'Approved'
        
        // ... logic approve lama
    }
    */

    /*
    public function reject(Request $request, $id)
    {
        // [OLD] Manager reject request Pending
        // SUDAH TIDAK DIPAKAI — tidak ada Pending lagi
        
        // ... logic reject lama
    }
    */
}