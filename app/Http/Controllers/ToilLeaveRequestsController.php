<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Roster;
use App\Models\Toilbalances;
use App\Models\ToilLeaveRequests;
use App\Models\ToilLeaveRequestBalance;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ToilLeaveRequestsController extends Controller
{
    private const HOURS_PER_DAY  = 8;
    private const MAX_RANGE_DAYS = 31;

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

    if (!$manager) {
        return response()->json(['data' => []]);
    }

    // Atasan karyawan saat ini juga bisa mengelola, supaya klaim tidak tertahan
    // kalau manager pembuatnya sudah pindah / resign.
    $bawahanIds = $manager->bawahanList()->pluck('employees_tables.id');

    $query = ToilLeaveRequests::with(['employee:id,employee_name,employee_pengenal,pin'])
        ->where(function ($q) use ($manager, $bawahanIds) {
            $q->where('approver_id', $manager->id)
                ->orWhereIn('employee_id', $bawahanIds);
        })
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

    // ── Kelompokkan per batch + status (baris tanpa batch_id diperlakukan sendiri) ──
    // Status ikut jadi kunci karena batch bisa ter-cancel sebagian.
    $grouped = $rows->groupBy(fn($r) => $r->batch_id ? $r->batch_id . '|' . $r->status : $r->id);

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
            'id'              => $first->id,           // id baris pertama (cancel berlaku untuk 1 batch)
            'batch_id'        => $first->batch_id,
            'employee_name'   => $first->employee->employee_name ?? '-',
            'employee_pin'    => $first->employee->pin ?? '-',
            'leave_date'      => $dateRange,
            'start_raw'       => Carbon::parse($first->leave_date)->toDateString(),
            'employee_pengenal' => $first->employee->employee_pengenal ?? '-',
            'total_days'      => $totalDays,
            'hours_used'      => number_format($totalHours, 2),
            'reason'          => $first->reason,
            'status'          => $first->status,
            'approved_at'     => $first->approved_at?->format('d M Y H:i'),
            'rejected_reason' => $first->rejected_reason,
            'created_at'      => $first->created_at->format('d M Y H:i'),
            'can_cancel'      => $group->contains(fn($r) => $r->canBeCancelledByManager()),
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

        if (!$manager) {
            return response()->json(['success' => false, 'message' => 'Data karyawan tidak ditemukan.'], 403);
        }

        $subordinateIds = $this->getSubordinates($manager)->pluck('id')->toArray();
        if (!in_array($employeeId, $subordinateIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan ini bukan bawahan Anda.',
            ], 403);
        }

        $saldoList = Toilbalances::usableForLeave(today())
            ->where('employee_id', $employeeId)
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

        // DW tidak boleh libur TOIL
        if (strtoupper(Employee::whereKey($validated['employee_id'])->value('status_employee') ?? '') === 'DW') {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan DW tidak bisa mengambil TOIL Leave.',
            ], 422);
        }

        // Karyawan harus bawahan manager
        $subordinateIds = $this->getSubordinates($manager)->pluck('id')->toArray();
        if (!in_array($validated['employee_id'], $subordinateIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan ini bukan bawahan Anda di struktur organisasi.',
            ], 403);
        }

        $start = Carbon::parse($validated['start_date']);
        $end   = Carbon::parse($validated['end_date']);

        if ($start->diffInDays($end) >= self::MAX_RANGE_DAYS) {
            return response()->json([
                'success' => false,
                'message' => 'Rentang TOIL Leave maksimal ' . self::MAX_RANGE_DAYS . ' hari.',
            ], 422);
        }

        try {
            // Semua pengecekan & pemotongan saldo di bawah kunci karyawan,
            // supaya request bersamaan tidak memakai saldo yang sama.
            return DB::transaction(fn() => $this->assignLeave($validated, $manager, $start, $end));
        } catch (\Illuminate\Database\QueryException $e) {
            // Unique index toil_lr_emp_approved_date_unique: klaim lain untuk tanggal yang sama
            // baru saja tersimpan. Transaksi sudah di-rollback, jadi saldo tidak terpotong.
            if (($e->errorInfo[1] ?? null) === 1062) {
                return response()->json([
                    'success' => false,
                    'message' => 'Salah satu tanggal baru saja di-assign TOIL Leave oleh user lain. Muat ulang data lalu coba lagi.',
                ], 422);
            }

            Log::error('ToilLeaveRequests: store DB ERROR', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan database saat menyimpan TOIL Leave.',
            ], 500);
        } catch (\Exception $e) {
            Log::error('ToilLeaveRequests: store ERROR', ['error' => $e->getMessage(), 'line' => $e->getLine()]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function assignLeave(array $validated, Employee $manager, Carbon $start, Carbon $end): JsonResponse
    {
        $employeeId = $validated['employee_id'];

        Toilbalances::lockEmployees([$employeeId]);

        $rosters = Roster::where('employee_id', $employeeId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn($r) => Carbon::parse($r->date)->toDateString());

        // ── Bangun daftar tanggal KERJA saja ──
        // Hari libur tidak memotong saldo & roster-nya tidak diubah:
        // - ada roster  → libur kalau day_type = 'Off'
        // - belum ada roster → libur kalau hari Minggu (sama dengan working days payroll)
        $dates      = [];
        $skippedOff = [];
        for ($cur = $start->copy(); $cur->lte($end); $cur->addDay()) {
            $roster = $rosters->get($cur->toDateString());
            $isOff  = $roster ? $roster->day_type === 'Off' : $cur->isSunday();

            if ($isOff) {
                $skippedOff[] = $cur->format('d M');
            } else {
                $dates[] = $cur->toDateString();
            }
        }

        if (empty($dates)) {
            return response()->json([
                'success' => false,
                'message' => 'Semua tanggal yang dipilih adalah hari libur (Off), tidak perlu TOIL.',
            ], 422);
        }

        $totalDays = count($dates);
        $needed    = $totalDays * self::HOURS_PER_DAY;

        // ── Cek BENTROK tiap tanggal. Tolak SELURUH rentang kalau ada satu pun yang bentrok. ──
        $approvedDates = ToilLeaveRequests::where('employee_id', $employeeId)
            ->where('status', 'Approved')
            ->whereIn('leave_date', $dates)
            ->get()
            ->map(fn($r) => $r->leave_date->toDateString());

        foreach ($dates as $dateStr) {
            if ($approvedDates->contains($dateStr)) {
                return response()->json([
                    'success' => false,
                    'message' => "Tanggal {$dateStr} sudah ada TOIL Leave Approved. Ganti rentang tanggal.",
                ], 422);
            }

            $dayType = $rosters->get($dateStr)?->day_type;
            if (in_array($dayType, ['Public Holiday', 'Leave', 'Cuti Melahirkan', 'Sick', 'TOIL Off'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => "Tanggal {$dateStr} sudah berstatus {$dayType} di roster. Ganti rentang tanggal.",
                ], 422);
            }
        }

        // ── Alokasi saldo per tanggal: paling cepat hangus dipakai dulu, ──
        // dan saldo hanya boleh dipakai untuk tanggal sebelum masa berlakunya habis.
        $balances = Toilbalances::usableForLeave($dates[0])
            ->where('employee_id', $employeeId)
            ->orderBy('expires_at', 'asc')
            ->get();

        $available  = $balances->mapWithKeys(fn($b) => [$b->id => (float) $b->remaining_hours])->all();
        $allocation = [];

        foreach ($dates as $dateStr) {
            $remaining = self::HOURS_PER_DAY;

            foreach ($balances as $balance) {
                if ($remaining <= 0) break;
                if ($available[$balance->id] <= 0) continue;
                if ($balance->expires_at->toDateString() < $dateStr) continue;

                $take = min($available[$balance->id], $remaining);
                $available[$balance->id] -= $take;
                $remaining -= $take;
                $allocation[$dateStr][$balance->id] = $take;
            }

            if ($remaining > 0) {
                $usable = $balances->sum(fn($b) => (float) $b->remaining_hours);
                return response()->json([
                    'success' => false,
                    'message' => "Saldo TOIL tidak cukup. Butuh {$needed} jam ({$totalDays} hari), tersedia {$usable} jam, "
                        . "dan saldo yang masih berlaku pada {$dateStr} tidak mencukupi.",
                ], 422);
            }
        }

        // ── Buat 1 baris per tanggal (semua batch_id sama), breakdown per baris ──
        $batchId = (string) Str::uuid();

        foreach ($dates as $dateStr) {
            $roster = $rosters->get($dateStr);

            $row = ToilLeaveRequests::create([
                'batch_id'          => $batchId,
                'employee_id'       => $employeeId,
                'toil_balance_id'   => array_key_first($allocation[$dateStr]),
                'approver_id'       => $manager->id,
                'hours_used'        => self::HOURS_PER_DAY,
                'leave_date'        => $dateStr,
                'reason'            => $validated['reason'],
                'original_shift_id' => $roster?->shift_id,
                // 'Off' di sini berarti tanggal itu belum punya roster, karena roster
                // Off sungguhan tidak pernah sampai ke sini (lihat restoreRosterFromCancel).
                'original_day_type' => $roster?->day_type ?? 'Off',
                'original_notes'    => $roster?->notes,
                'status'            => 'Approved',
                'approved_at'       => now(),
            ]);

            foreach ($allocation[$dateStr] as $balanceId => $take) {
                ToilLeaveRequestBalance::create([
                    'leave_request_id' => $row->id,
                    'toil_balance_id'  => $balanceId,
                    'hours_taken'      => $take,
                ]);
            }

            Roster::updateOrCreate(
                ['employee_id' => $employeeId, 'date' => $dateStr],
                ['day_type' => 'TOIL Off', 'shift_id' => null, 'notes' => 'Tukar TOIL (' . $totalDays . ' hari)']
            );
        }

        // ── Potong saldo ──
        foreach ($balances as $balance) {
            $taken = (float) $balance->remaining_hours - $available[$balance->id];
            if ($taken <= 0) continue;

            $balance->update(['used_hours' => (float) $balance->used_hours + $taken]);
            $balance->refresh();
            $balance->refreshStatus();
        }

        return response()->json([
            'success' => true,
            'message' => "TOIL Leave {$totalDays} hari berhasil di-assign. Saldo dipotong {$needed} jam & roster ter-update."
                . ($skippedOff ? ' Hari libur tidak dipotong: ' . implode(', ', $skippedOff) . '.' : ''),
        ]);
    }

    /**
     * PUT /toil/approval/{id}/cancel
     * Manager cancel TOIL Leave yang sudah Approved.
     *
     * Aturan:
     * - Hanya approver atau atasan karyawan saat ini yang bisa cancel
     * - Hanya status 'Approved' yang bisa di-cancel
     * - Dalam satu batch, tanggal yang sudah lewat tetap Approved;
     *   yang di-cancel hanya hari ini dan seterusnya
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
        if (!$manager) {
            return response()->json(['success' => false, 'message' => 'Data karyawan tidak ditemukan.'], 403);
        }

        $clicked = ToilLeaveRequests::findOrFail($id);

        $isCurrentAtasan = $manager->bawahanList()->where('employees_tables.id', $clicked->employee_id)->exists();
        if ($clicked->approver_id !== $manager->id && !$isCurrentAtasan) {
            return response()->json(['success' => false, 'message' => 'Anda tidak punya akses untuk cancel TOIL Leave ini.'], 403);
        }

        try {
            return DB::transaction(function () use ($clicked, $request) {
                Toilbalances::lockEmployees([$clicked->employee_id]);

                // Dibaca setelah kunci didapat, supaya double cancel tidak mengembalikan saldo dua kali.
                // Batch null = data lama, pakai baris ini saja.
                $batchRows = $clicked->batch_id
                    ? ToilLeaveRequests::where('batch_id', $clicked->batch_id)->get()
                    : ToilLeaveRequests::whereKey($clicked->id)->get();

                $targets = $batchRows->filter(fn($r) => $r->canBeCancelledByManager());

                if ($targets->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'TOIL Leave tidak bisa di-cancel. Pastikan status Approved dan tanggalnya belum lewat.',
                    ], 422);
                }

                $this->refundBalances($batchRows, $targets);

                foreach ($targets as $row) {
                    $this->restoreRosterFromCancel($row);
                    $row->update([
                        'status'          => 'Cancelled',
                        'rejected_reason' => $request->cancel_reason,
                    ]);
                }

                $passed = $batchRows->where('status', 'Approved')->count();

                return response()->json([
                    'success' => true,
                    'message' => 'TOIL Leave berhasil di-cancel (' . $targets->count() . ' hari). Saldo dikembalikan & roster di-restore.'
                        . ($passed ? " {$passed} hari yang sudah lewat tetap tercatat." : ''),
                ]);
            });
        } catch (\Exception $e) {
            Log::error('ToilApproval: cancelApproved ERROR', ['error' => $e->getMessage(), 'line' => $e->getLine()]);
            return response()->json(['success' => false, 'message' => 'Gagal cancel: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Kembalikan jam baris yang di-cancel ke saldo asalnya.
     *
     * Breakdown baris yang di-cancel dipakai lebih dulu. Batch lama menyimpan seluruh
     * breakdown di baris pertama, jadi sisanya diambil dari baris lain dalam batch.
     * Klaim dari sebelum tabel breakdown ada dikembalikan ke toil_balance_id-nya.
     */
    private function refundBalances(Collection $batchRows, Collection $targets): void
    {
        $targetIds  = $targets->pluck('id')->all();
        $breakdowns = ToilLeaveRequestBalance::whereIn('leave_request_id', $batchRows->pluck('id'))
            ->get()
            ->sortBy(fn($bd) => in_array($bd->leave_request_id, $targetIds, true) ? 0 : 1);

        if ($breakdowns->isEmpty()) {
            foreach ($targets as $row) {
                $this->refundBalance($row->toil_balance_id, (float) $row->hours_used);
            }
            return;
        }

        $remaining = $targets->sum(fn($r) => (float) $r->hours_used);

        foreach ($breakdowns as $bd) {
            if ($remaining <= 0) break;

            $take = min((float) $bd->hours_taken, $remaining);
            $this->refundBalance($bd->toil_balance_id, $take);
            $remaining -= $take;

            if ($take >= (float) $bd->hours_taken) {
                $bd->delete();
            } else {
                $bd->update(['hours_taken' => (float) $bd->hours_taken - $take]);
            }
        }
    }

    private function refundBalance(string $balanceId, float $hours): void
    {
        $balance = Toilbalances::find($balanceId);
        if (!$balance) return;

        $balance->update(['used_hours' => max(0, (float) $balance->used_hours - $hours)]);
        $balance->refresh();
        $balance->refreshStatus();
    }

    // ════════════════════════════════════════════════════════════════
    //   SUBORDINATES (ambil relasi dari employee.php)
    // ════════════════════════════════════════════════════════════════

    private function getSubordinates(Employee $manager): \Illuminate\Database\Eloquent\Collection
    {
        return $manager->bawahanList()
            ->whereNull('employees_tables.deleted_at')
            ->whereIn('employees_tables.status', ['Active', 'Pending', 'On Leave'])
            ->where(fn($q) => $q->whereNull('employees_tables.status_employee')
                ->orWhere('employees_tables.status_employee', '!=', 'DW'))
            ->get();
    }

    // ════════════════════════════════════════════════════════════════
    //   ROSTER RESTORE
    // ════════════════════════════════════════════════════════════════

    private function restoreRosterFromCancel(ToilLeaveRequests $row): void
    {
        $roster = Roster::where('employee_id', $row->employee_id)
            ->whereDate('date', $row->leave_date)
            ->first();

        // Roster sudah diubah jadi hal lain sejak klaim dibuat → jangan ditimpa.
        if (!$roster || !in_array($roster->day_type, ['TOIL Off', 'Off'], true)) return;

        // 'Off' asli tidak pernah diklaim, jadi ini tanggal yang tadinya belum punya roster.
        // Baris dihapus supaya Auto Roster bisa mengisinya lagi.
        if ($row->original_day_type === 'Off') {
            $roster->delete();
            return;
        }

        $roster->update([
            'day_type' => $row->original_day_type ?? 'Work',
            'shift_id' => $row->original_shift_id,
            'notes'    => $row->original_notes,
        ]);
    }
}
