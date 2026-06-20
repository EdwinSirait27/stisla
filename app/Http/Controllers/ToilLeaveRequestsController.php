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

    $query = ToilLeaveRequests::with(['employee:id,employee_name,pin'])
        ->where('approver_id', $manager->id)
        ->orderBy('leave_date', 'asc');

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('start_date') && $request->filled('end_date')) {
        $query->whereBetween('leave_date', [$request->start_date, $request->end_date]);
    }

    if ($request->filled('employee_search')) {
        $query->whereHas('employee', function ($q) use ($request) {
            $q->where('employee_name', 'like', '%' . $request->employee_search . '%');
        });
    }

    $rows = $query->get();

    // ── Kelompokkan per batch (baris tanpa batch_id diperlakukan sendiri) ──
    $grouped = $rows->groupBy(fn($r) => $r->batch_id ?? $r->id);

    $data = $grouped->map(function ($group) {
        $first = $group->sortBy('leave_date')->first();
        $last  = $group->sortBy('leave_date')->last();
        $totalDays  = $group->count();
        $totalHours = $group->sum(fn($r) => (float) $r->hours_used);

        // Rentang tanggal
        $startStr = Carbon::parse($first->leave_date)->format('d M Y');
        $endStr   = Carbon::parse($last->leave_date)->format('d M Y');
        $dateRange = $totalDays > 1 ? "{$startStr} → {$endStr}" : $startStr;

        return [
            'id'              => $first->id,           // id baris pertama (untuk tombol cancel)
            'batch_id'        => $first->batch_id,
            'employee_name'   => $first->employee->employee_name ?? '-',
            'employee_pin'    => $first->employee->pin ?? '-',
            'leave_date'      => $dateRange,
            'total_days'      => $totalDays,
            'hours_used'      => number_format($totalHours, 2),
            'reason'          => $first->reason,
            'status'          => $first->status,
            'approved_at'     => $first->approved_at?->format('d M Y H:i'),
            'rejected_reason' => $first->rejected_reason,
            'created_at'      => $first->created_at->format('d M Y H:i'),
            'can_cancel'      => $first->canBeCancelledByManager(),
        ];
    })->values();

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
            'start_date'  => 'required|date|after_or_equal:today',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'reason'      => 'required|string|min:10|max:1000',
        ], [
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh masa lalu.',
            'end_date.after_or_equal'   => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
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

        // ── Bangun daftar tanggal (semua hari, termasuk weekend) ──
        $start = Carbon::parse($validated['start_date']);
        $end   = Carbon::parse($validated['end_date']);
        $dates = [];
        $cur   = $start->copy();
        while ($cur->lte($end)) {
            $dates[] = $cur->copy();
            $cur->addDay();
        }

        $totalDays  = count($dates);
        $hoursPerDay = 8;
        $needed     = $totalDays * $hoursPerDay;

        // ── Cek BENTROK tiap tanggal: TOIL approved / PH / cuti (Leave) ──
        // Tolak SELURUH rentang kalau ada satu pun yang bentrok.
        foreach ($dates as $d) {
            $dateStr = $d->toDateString();

            // TOIL approved lain
            $toilClash = ToilLeaveRequests::where('employee_id', $validated['employee_id'])
                ->where('status', 'Approved')
                ->whereDate('leave_date', $dateStr)
                ->exists();
            if ($toilClash) {
                return response()->json([
                    'success' => false,
                    'message' => "Tanggal {$dateStr} sudah ada TOIL Leave Approved. Ganti rentang tanggal.",
                ], 422);
            }

            // PH atau cuti di roster
            $rosterClash = Roster::where('employee_id', $validated['employee_id'])
                ->whereDate('date', $dateStr)
                ->whereIn('day_type', ['Public Holiday', 'Leave', 'Cuti Melahirkan'])
                ->exists();
            if ($rosterClash) {
                return response()->json([
                    'success' => false,
                    'message' => "Tanggal {$dateStr} sudah ada Public Holiday / Cuti. Ganti rentang tanggal.",
                ], 422);
            }
        }

        // ── Ambil saldo FIFO, cek cukup untuk TOTAL ──
        $balances = Toilbalances::ofType('Toil')
            ->where('employee_id', $validated['employee_id'])
            ->where('status', 'active')
            ->where('expires_at', '>=', today())
            ->orderBy('expires_at', 'asc')
            ->get();

        $totalAvailable = $balances->sum(fn($b) => (float) $b->remaining_hours);

        if ($totalAvailable < $needed) {
            return response()->json([
                'success' => false,
                'message' => "Saldo TOIL tidak cukup. Butuh {$needed} jam ({$totalDays} hari), tersedia {$totalAvailable} jam.",
            ], 422);
        }

        try {
            DB::beginTransaction();

            $batchId        = (string) \Illuminate\Support\Str::uuid();
            $firstBalanceId = $balances->first()->id;
            $firstLeaveRow  = null;

            // ── Buat 1 baris per tanggal (semua batch_id sama) ──
            foreach ($dates as $i => $d) {
                $dateStr = $d->toDateString();

                $roster = Roster::where('employee_id', $validated['employee_id'])
                    ->whereDate('date', $dateStr)
                    ->first();
                $originalShiftId = $roster?->shift_id;
                $originalDayType = $roster?->day_type ?? 'Off';

                $row = ToilLeaveRequests::create([
                    'batch_id'          => $batchId,
                    'employee_id'       => $validated['employee_id'],
                    'toil_balance_id'   => $firstBalanceId,
                    'approver_id'       => $manager->id,
                    'hours_used'        => $hoursPerDay,
                    'leave_date'        => $dateStr,
                    'reason'            => $validated['reason'],
                    'original_shift_id' => $originalShiftId,
                    'original_day_type' => $originalDayType,
                    'status'            => 'Approved',
                    'approved_at'       => now(),
                ]);

                if ($i === 0) $firstLeaveRow = $row;

                // Roster jadi TOIL Off
                Roster::updateOrCreate(
                    ['employee_id' => $validated['employee_id'], 'date' => $dateStr],
                    ['day_type' => 'TOIL Off', 'shift_id' => null, 'notes' => 'Tukar TOIL (' . $totalDays . ' hari)']
                );
            }

            // ── Potong saldo FIFO untuk TOTAL, breakdown ke baris pertama ──
            $remainingToDeduct = $needed;
            foreach ($balances as $balance) {
                if ($remainingToDeduct <= 0) break;
                $available = (float) $balance->remaining_hours;
                if ($available <= 0) continue;

                $take = min($available, $remainingToDeduct);

                ToilLeaveRequestBalance::create([
                    'leave_request_id' => $firstLeaveRow->id,   // breakdown ke baris pertama (pilihan a)
                    'toil_balance_id'  => $balance->id,
                    'hours_taken'      => $take,
                ]);

                $balance->update(['used_hours' => (float) $balance->used_hours + $take]);
                $balance->refresh();
                $balance->refreshStatus();

                $remainingToDeduct -= $take;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "TOIL Leave {$totalDays} hari berhasil di-assign. Saldo dipotong {$needed} jam & roster ter-update.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ToilLeaveRequests: store ERROR', ['error' => $e->getMessage(), 'line' => $e->getLine()]);
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

        $manager = Auth::user()->employee;
        $clicked = ToilLeaveRequests::findOrFail($id);

        if ($clicked->approver_id !== $manager->id) {
            return response()->json(['success' => false, 'message' => 'Anda tidak punya akses untuk cancel TOIL Leave ini.'], 403);
        }

        // Ambil semua baris dalam batch (kalau batch_id null, pakai baris ini saja — kompat data lama)
        $batchRows = $clicked->batch_id
            ? ToilLeaveRequests::where('batch_id', $clicked->batch_id)->get()
            : collect([$clicked]);

        // Validasi: semua harus Approved & belum lewat
        foreach ($batchRows as $row) {
            if (!$row->canBeCancelledByManager()) {
                return response()->json([
                    'success' => false,
                    'message' => 'TOIL Leave tidak bisa di-cancel. Pastikan status Approved dan tanggalnya belum lewat.',
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            // 1. Kembalikan saldo dari breakdown (breakdown ada di baris pertama batch)
            $rowIds = $batchRows->pluck('id')->toArray();
            $breakdowns = ToilLeaveRequestBalance::whereIn('leave_request_id', $rowIds)->get();

            foreach ($breakdowns as $bd) {
                $balance = Toilbalances::find($bd->toil_balance_id);
                if (!$balance) continue;
                $balance->update(['used_hours' => max(0, (float) $balance->used_hours - (float) $bd->hours_taken)]);
                $balance->refresh();
                $balance->refreshStatus();
            }
            ToilLeaveRequestBalance::whereIn('leave_request_id', $rowIds)->delete();

            // 2. Restore roster tiap tanggal + set status Cancelled
            foreach ($batchRows as $row) {
                $this->restoreRosterFromCancel(
                    $row->employee_id,
                    $row->leave_date,
                    $row->original_shift_id,
                    $row->original_day_type
                );
                $row->update([
                    'status'          => 'Cancelled',
                    'rejected_reason' => $request->cancel_reason,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'TOIL Leave berhasil di-cancel (' . $batchRows->count() . ' hari). Saldo dikembalikan & roster di-restore.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ToilApproval: cancelApproved ERROR', ['error' => $e->getMessage(), 'line' => $e->getLine()]);
            return response()->json(['success' => false, 'message' => 'Gagal cancel: ' . $e->getMessage()], 500);
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

    private function restoreRosterFromCancel(string $employeeId, $leaveDate, ?string $originalShiftId, ?string $originalDayType = 'Work'): void
    {
        $roster = Roster::where('employee_id', $employeeId)
            ->whereDate('date', $leaveDate)
            ->first();
        if (!$roster) return;

        $roster->update([
            'day_type' => $originalDayType ?? 'Work',
            'shift_id' => $originalShiftId,
            'notes'    => null,
        ]);
    }
}
