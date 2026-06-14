<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Roster;
use App\Models\Toilbalances;
use App\Models\ToilLeaveRequests;
use App\Models\ToilLeaveRequestBalance;
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

    if (!$manager) {
    return redirect()->back()->with('error', 'Data karyawan tidak ditemukan.');
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

        $subordinateIds = $this->getSubordinates($manager)->pluck('id')->toArray();
        if (!in_array($employeeId, $subordinateIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan ini bukan bawahan Anda.',
            ], 403);
        }

        $saldoList = Toilbalances::ofType('Toil')
            ->where('employee_id', $employeeId)
            ->where('status', 'active')
            ->where('expires_at', '>=', today())
            ->get();

        $totalRemaining = $saldoList->sum(fn($b) => (float) $b->remaining_hours);

        // Saldo terdekat expired (untuk info)
        $nearest = $saldoList->sortBy('expires_at')->first();

        return response()->json([
            'total_remaining' => number_format($totalRemaining, 2),
            'count'           => $saldoList->count(),
            'nearest_expiry'  => $nearest ? $nearest->expires_at->format('d M Y') : null,
        ]);
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
            'employee_id' => 'required|exists:employees_tables,id',
            'hours_used'  => 'required|numeric|min:0.5|max:8',
            'leave_date'  => 'required|date|after_or_equal:today',
            'reason'      => 'required|string|min:10|max:1000',
        ], [
            'hours_used.min'            => 'Minimal 0.5 jam.',
            'hours_used.max'            => 'Maksimal 8 jam (= 1 hari) per request.',
            'leave_date.after_or_equal' => 'Tanggal libur tidak boleh masa lalu.',
            'reason.min'                => 'Alasan minimal 10 karakter.',
        ]);

        $user    = Auth::user();
        $manager = $user->employee;

        if (!$manager) {
    return response()->json(['success' => false, 'message' => 'Data karyawan tidak ditemukan.'], 403);
}

        // Karyawan harus bawahan manager
        $subordinateIds = $this->getSubordinates($manager)->pluck('id')->toArray();
        if (!in_array($validated['employee_id'], $subordinateIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan ini bukan bawahan Anda di struktur organisasi.',
            ], 403);
        }

        // Cegah leave ganda di tanggal sama (poin 2 yang dulu kita catat)
        $existingLeave = ToilLeaveRequests::where('employee_id', $validated['employee_id'])
            ->whereDate('leave_date', $validated['leave_date'])
            ->where('status', 'Approved')
            ->exists();
        if ($existingLeave) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan sudah punya TOIL Leave aktif di tanggal tersebut.',
            ], 422);
        }

        // Ambil SEMUA saldo Toil aktif, urut FIFO (paling cepat expired dulu)
        $balances = Toilbalances::ofType('Toil')
            ->where('employee_id', $validated['employee_id'])
            ->where('status', 'active')
            ->where('expires_at', '>=', today())
            ->orderBy('expires_at', 'asc')
            ->get();

        // Cek total saldo cukup
        $totalAvailable = $balances->sum(fn($b) => (float) $b->remaining_hours);
        $needed = (float) $validated['hours_used'];

        if ($totalAvailable < $needed) {
            return response()->json([
                'success' => false,
                'message' => "Saldo TOIL tidak cukup. Total tersedia {$totalAvailable} jam, diminta {$needed} jam.",
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Roster di tanggal leave (untuk simpan shift original)
            $roster = Roster::where('employee_id', $validated['employee_id'])
                ->whereDate('date', $validated['leave_date'])
                ->first();
            $originalShiftId = $roster?->shift_id;

            // Buat leave request — toil_balance_id diisi saldo PERTAMA (kompatibilitas)
            $firstBalanceId = $balances->first()->id;

            $leaveRequest = ToilLeaveRequests::create([
                'employee_id'       => $validated['employee_id'],
                'toil_balance_id'   => $firstBalanceId,
                'approver_id'       => $manager->id,
                'hours_used'        => $needed,
                'leave_date'        => $validated['leave_date'],
                'reason'            => $validated['reason'],
                'original_shift_id' => $originalShiftId,
                'status'            => 'Approved',
                'approved_at'       => now(),
            ]);

            // Potong FIFO dari beberapa saldo + catat ke pivot
            $remainingToDeduct = $needed;

            foreach ($balances as $balance) {
                if ($remainingToDeduct <= 0) break;

                $available = (float) $balance->remaining_hours;
                if ($available <= 0) continue;

                $take = min($available, $remainingToDeduct);

                // Catat potongan ke pivot
                ToilLeaveRequestBalance::create([
                    'leave_request_id' => $leaveRequest->id,
                    'toil_balance_id'  => $balance->id,
                    'hours_taken'      => $take,
                ]);

                // Update saldo
                $balance->update([
                    'used_hours' => (float) $balance->used_hours + $take,
                ]);
                $balance->refresh();
                $balance->refreshStatus();

                $remainingToDeduct -= $take;
            }

            // Update roster jadi Off
            if ($roster) {
                $roster->update([
                    'day_type' => 'TOIL Off',
                    'shift_id' => null,
                    'notes'    => 'Tukar TOIL ' . $needed . ' jam',
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
        $leaveRequest = ToilLeaveRequests::with('balanceBreakdowns')->findOrFail($id);

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

            // 1. Kembalikan jam ke TIAP saldo asal berdasarkan pivot
            foreach ($leaveRequest->balanceBreakdowns as $breakdown) {
                $balance = Toilbalances::find($breakdown->toil_balance_id);
                if (!$balance) continue;

                $balance->update([
                    'used_hours' => max(0, (float) $balance->used_hours - (float) $breakdown->hours_taken),
                ]);
                $balance->refresh();
                $balance->refreshStatus();
            }

            // 2. Hapus catatan pivot (potongan sudah dikembalikan)
            $leaveRequest->balanceBreakdowns()->delete();

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
    //   SUBORDINATES (ambil relasi dari employee.php)
    // ════════════════════════════════════════════════════════════════

    private function getSubordinates(Employee $manager): \Illuminate\Database\Eloquent\Collection
{
    return $manager->bawahanList()->get();
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
            'notes'    => null,
        ]);
    }
}
