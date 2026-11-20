<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Overtimesubmissions;
use App\Models\Payroll;
use App\Models\User;
use App\Models\Toilbalances;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

class OvertimesubmissionsController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $manager = $user->employee;
        if (!$user->hasPermissionTo('assignment')) {
            return redirect()->back()->with('error', 'masih bawahan gabole akses yaa.');
        }
        if (!$manager) {
            return redirect()->back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        // $employees = $this->getSubordinates($manager);
        // Assign lembur ke diri sendiri hanya kalau punya permission assignmentSelf
        $canSelf = $user->can('assignmentSelf');

        // ── Query employee ──
        $employees = $this->assignableEmployeesQuery($manager, $canSelf)
            ->select('id', 'employee_name', 'employee_pengenal', 'status_employee')
            ->with(['store' => fn($q) => $q->wherePivot('is_primary', true)])
            ->when(!$canSelf, fn($q) => $q->where('id', '!=', $manager->id))
            ->orderBy('employee_name')
            ->get();
        [$minDate, $maxDate] = $this->currentPeriodRange();


        return view('pages.Toil.assignment', compact('employees', 'manager','minDate',
    'maxDate'));
    }
    public function getData(Request $request)
    {
        $user    = Auth::user();
        $manager = $user->employee;

        if (!$manager) {
            return response()->json(['data' => []]);
        }

        // Atasan karyawan saat ini juga bisa mengelola, supaya data tidak tertahan
        // kalau manager pembuatnya sudah pindah / resign.
        $bawahanIds = $manager->bawahanList()->pluck('employees_tables.id');

        $query = Overtimesubmissions::with([
            'employees:id,employee_name,employee_pengenal,pin,status_employee',
            'approver:id,employee_name',
            'balance',
        ])
            ->where(function ($q) use ($manager, $bawahanIds) {
                $q->where('approver_id', $manager->id)
                    ->orWhereIn('employee_id', $bawahanIds);
            })
            ->orderBy('created_at', 'desc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }
        if ($request->filled('compensation_type')) {
            $query->where('compensation_type', $request->compensation_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->get()->map(function ($row) {
            return [
                'id'                => $row->id,
                'employee_name'     => $row->employees->employee_name ?? '-',
                'date'              => Carbon::parse($row->date)->format('d M Y'),
                'date_raw'          => Carbon::parse($row->date)->format('Y-m-d'),
                'time_range'        => ($row->start_time ? Carbon::parse($row->start_time)->format('H:i') : '-')
                    . ' - '
                    . ($row->end_time ? Carbon::parse($row->end_time)->format('H:i') : '-'),
                'start_time_raw'    => $row->start_time ? Carbon::parse($row->start_time)->format('H:i') : '',
                'end_time_raw'      => $row->end_time ? Carbon::parse($row->end_time)->format('H:i') : '',
                'total_hours'       => number_format($row->total_hours, 2),
                'compensation_type' => $row->compensation_type,
                'status'            => $row->status,
                'reason'            => $row->reason,
                'expires_at'        => $row->balance?->expires_at?->format('d M Y') ?? '-',
                'balance_status'    => $row->balance?->effectiveStatus($row->compensation_type) ?? '-',
                'employee_pengenal' => $row->employees->employee_pengenal ?? '-',
                'employee_type'     => $row->employees->status_employee ?? '-',
                'approver_name'     => $row->approver->employee_name ?? '-',
                'used_hours'        => $row->balance ? (float) $row->balance->used_hours : 0,
                'remaining_hours'   => $row->balance ? number_format($row->balance->remaining_hours, 2) : '-',
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        [$minDate, $maxDate] = $this->currentPeriodRange();

        $periodMsg = "harus di periode berjalan ({$minDate->format('d-m-Y')} s/d {$maxDate->format('d-m-Y')}).";

        $validated = $request->validate([
            'employee_ids'      => 'required|array|min:1',
            'employee_ids.*'    => 'exists:employees_tables,id',
           
            // Tanggal harus di dalam periode payroll berjalan (26 – 25)
            'date'              => ['required', 'date', 'after_or_equal:' . $minDate->toDateString(), 'before_or_equal:' . $maxDate->toDateString()],
            'end_date'          => ['required', 'date', 'after_or_equal:date', 'before_or_equal:' . $maxDate->toDateString()],

            // Wajib: kolomnya NOT NULL, dan total jam dihitung server dari jam ini
            'start_time'        => 'required|date_format:H:i',
            'end_time'          => 'required|date_format:H:i',
            'total_hours'       => 'required|numeric|min:0.5|max:24',
            'compensation_type' => 'required|in:Cash,Toil',
            'reason'            => 'required|string|min:10|max:1000',
        ], [
            'date.after_or_equal'      => "Tanggal mulai {$periodMsg}",
            'date.before_or_equal'     => "Tanggal mulai {$periodMsg}",
            'end_date.before_or_equal' => "Tanggal selesai {$periodMsg}",
        ]);

        // Jam per hari dihitung ulang di server dari start/end time (jangan percaya input)
        if (!empty($validated['start_time']) && !empty($validated['end_time'])) {
            $hours = $this->calculateHours($validated['start_time'], $validated['end_time']);
            if ($hours < 0.5) {
                return response()->json(['success' => false, 'message' => 'Jam selesai harus setelah jam mulai (minimal 0.5 jam).'], 422);
            }
            $validated['total_hours'] = $hours;
        }

        $user    = Auth::user();
        $manager = $user->employee;

        if (!$manager) {
            return response()->json(['success' => false, 'message' => 'Data karyawan tidak ditemukan.'], 403);
        }

$canSelf = $user->can('assignmentSelf');

// Assign ke diri sendiri tanpa permission → tolak dengan pesan jelas
if (!$canSelf && in_array($manager->id, $validated['employee_ids'], true)) {
    return response()->json([
        'success' => false,
        'message' => 'Anda tidak punya izin untuk assign lembur ke diri sendiri (permission: assignmentSelf).',
    ], 403);
}

$validSubordinateIds = $this->assignableEmployeesQuery($manager, $canSelf)
    ->pluck('id')
    ->toArray();

$invalidIds = array_diff($validated['employee_ids'], $validSubordinateIds);

if (!empty($invalidIds)) {
    $invalidNames = Employee::whereIn('id', $invalidIds)
        ->pluck('employee_name')
        ->toArray();

    return response()->json([
        'success' => false,
        'message' => 'Karyawan berikut tidak terdaftar sebagai bawahan Anda: ' . implode(', ', $invalidNames),
    ], 422);
}

        // DW tidak boleh libur TOIL, jadi lemburnya harus Cash
        if ($validated['compensation_type'] === 'Toil') {
            $dwNames = Employee::whereIn('id', $validated['employee_ids'])
                ->where('status_employee', 'DW')
                ->pluck('employee_name')
                ->toArray();

            if (!empty($dwNames)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Karyawan DW tidak bisa diberi lembur tipe Toil, gunakan Cash: ' . implode(', ', $dwNames),
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            // 1 record per karyawan per hari → total jam = jam per hari × jumlah hari
            $dates = [];
            for ($d = Carbon::parse($validated['date']); $d->lte(Carbon::parse($validated['end_date'])); $d->addDay()) {
                $dates[] = $d->toDateString();
            }

            Toilbalances::lockEmployees($validated['employee_ids']);

            $conflict = $this->findConflicts($validated['employee_ids'], $dates, $validated['compensation_type']);
            if ($conflict) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => $conflict], 422);
            }

            $created = 0;
            foreach ($validated['employee_ids'] as $empId) {
                foreach ($dates as $date) {
                    Overtimesubmissions::create([
                        'employee_id'       => $empId,
                        'approver_id'       => $manager->id,
                        'date'              => $date,
                        'end_date'          => $date,
                        'start_time'        => $validated['start_time'] ?? null,
                        'end_time'          => $validated['end_time'] ?? null,
                        'total_hours'       => $validated['total_hours'],
                        'compensation_type' => $validated['compensation_type'],
                        'reason'            => $validated['reason'],
                        'status'            => 'Approved',
                        'approved_at'       => now(),
                    ]);
                    $created++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil assign overtime untuk ' . count($validated['employee_ids']) . ' karyawan × ' . count($dates) . " hari ({$created} record, {$validated['total_hours']} jam/hari).",
                'count'   => $created,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Overtimesubmissions: store ERROR', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        [$minDate, $maxDate] = $this->currentPeriodRange();
        $periodMsg = "Tanggal harus di periode berjalan ({$minDate->format('d-m-Y')} s/d {$maxDate->format('d-m-Y')}).";

        $validated = $request->validate([
            'date'        => ['sometimes', 'date', 'after_or_equal:' . $minDate->toDateString(), 'before_or_equal:' . $maxDate->toDateString()],
            // Isi keduanya atau tidak sama sekali, supaya total jam selalu cocok dengan jamnya
            'start_time'  => 'sometimes|required|date_format:H:i|required_with:end_time',
            'end_time'    => 'sometimes|required|date_format:H:i|required_with:start_time',
            'total_hours' => 'sometimes|numeric|min:0.5|max:24',
            'reason'      => 'sometimes|string|min:10|max:1000',
        ], [
            'date.after_or_equal'  => $periodMsg,
            'date.before_or_equal' => $periodMsg,
        ]);

        // Jam dihitung ulang di server dari start/end time
        if (!empty($validated['start_time']) && !empty($validated['end_time'])) {
            $hours = $this->calculateHours($validated['start_time'], $validated['end_time']);
            if ($hours < 0.5) {
                return response()->json(['success' => false, 'message' => 'Jam selesai harus setelah jam mulai (minimal 0.5 jam).'], 422);
            }
            $validated['total_hours'] = $hours;
        }

        $found = Overtimesubmissions::findOrFail($id);

        if (!$this->canManage($found)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak punya akses untuk edit assignment ini.',
            ], 403);
        }

        try {
            DB::beginTransaction();
            Toilbalances::lockEmployees([$found->employee_id]);

            // Dibaca setelah kunci didapat, supaya saldo yang dicek adalah saldo terbaru
            $submission = Overtimesubmissions::with('balance')->findOrFail($id);
            $error      = $this->updateError($submission, $validated, $minDate, $maxDate);

            if ($error) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => $error], 422);
            }

            $dateChanged = isset($validated['date'])
                && Carbon::parse($validated['date'])->toDateString() !== $submission->date->toDateString();

            if ($dateChanged) {
                $validated['end_date'] = $validated['date'];
            }

            $submission->update($validated);

            if ($submission->balance) {
                $balanceChanges = [];
                if (isset($validated['total_hours'])) {
                    $balanceChanges['earned_hours'] = $validated['total_hours'];
                }
                if ($dateChanged) {
                    $balanceChanges['expires_at'] = $submission->balanceExpiryDate();
                }
                if ($balanceChanges) {
                    $submission->balance->update($balanceChanges);
                    $submission->balance->refresh(); // ← remaining_hours (generated column) perlu dibaca ulang
                    $submission->balance->refreshStatus();
                }
            } elseif (isset($validated['total_hours'])) {
                $submission->createOrUpdateBalance();
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Assignment berhasil di-update.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal update: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $found = Overtimesubmissions::findOrFail($id);

        if (!$this->canManage($found)) {
            return response()->json(['success' => false, 'message' => 'Anda tidak punya akses untuk cancel assignment ini.'], 403);
        }

        try {
            DB::beginTransaction();
            Toilbalances::lockEmployees([$found->employee_id]);

            // Dibaca setelah kunci didapat, supaya saldo yang baru dipakai ikut terlihat
            $submission = Overtimesubmissions::with('balance')->findOrFail($id);

            $error = null;
            if ($submission->status !== 'Approved') {
                $error = $submission->status === 'Approved HR'
                    ? 'Overtime sudah diproses ke payroll, tidak bisa di-cancel.'
                    : 'Assignment ini sudah di-cancel.';
            } elseif ($submission->balance && $submission->balance->used_hours > 0) {
                $error = "Tidak bisa cancel — saldo sudah dipakai {$submission->balance->used_hours} jam.";
            }

            if ($error) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => $error], 422);
            }

            if ($submission->balance) {
                $submission->balance->update(['status' => 'cancelled']);
            }
            $submission->update(['status' => 'Rejected']);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Assignment berhasil di-cancel.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal cancel: ' . $e->getMessage()], 500);
        }
    }

    // ════════════════════════════════════════════════════════════════
    //   PUBLIC: AJAX Endpoints
    // ════════════════════════════════════════════════════════════════

    /**
     * GET /toil/assignment/subordinates
     * AJAX: return list bawahan manager via structuresnew tree.
     * Dipakai oleh modal "Add Overtime" di dashboard.
     */
    public function getSubordinatesList()
    {
        $manager = Auth::user()->employee;
        if (!$manager) {
            return response()->json(['data' => [], 'message' => 'Data karyawan tidak ditemukan.'], 403);
        }

        $employees = $this->getSubordinates($manager);

        // Tambahkan diri sendiri kalau punya permission assignmentSelf
        if (Auth::user()->can('assignmentSelf') && !$employees->contains('id', $manager->id)) {
            $employees->prepend($manager);
        }

        if ($employees->isEmpty()) {
            return response()->json([
                'data'    => [],
                'message' => 'Tidak ada bawahan ditemukan. Pastikan struktur organisasi sudah disetup.',
            ]);
        }

        return response()->json([
            'data' => $employees->map(fn($e) => [
                'id'            => $e->id,
                'employee_name' => $e->employee_name,
                'pin'           => $e->pin ?? '-',
            ])->values(),
        ]);
    }

    // ════════════════════════════════════════════════════════════════
    //   PRIVATE METHODS
    // ════════════════════════════════════════════════════════════════

    /**
     * Range periode payroll berjalan: 26 bulan lalu – 25 bulan ini
     * (atau 26 bulan ini – 25 bulan depan kalau hari ini >= 26).
     */
    private function currentPeriodRange(): array
    {
        $today = today();

        if ($today->day >= 26) {
            $minDate = $today->copy()->day(26);
            $maxDate = $today->copy()->startOfMonth()->addMonthNoOverflow()->day(25);
        } else {
            $minDate = $today->copy()->startOfMonth()->subMonthNoOverflow()->day(26);
            $maxDate = $today->copy()->day(25);
        }

        return [$minDate, $maxDate];
    }

    /**
     * Hitung jam lembur dari HH:MM – HH:MM (lewat tengah malam didukung),
     * dibulatkan ke 0.5 jam — sama dengan autoCalcHours() di view.
     */
    private function calculateHours(string $start, string $end): float
    {
        [$sh, $sm] = array_map('intval', explode(':', $start));
        [$eh, $em] = array_map('intval', explode(':', $end));

        $startMinutes = $sh * 60 + $sm;
        $endMinutes   = $eh * 60 + $em;

        if ($endMinutes <= $startMinutes) {
            $endMinutes += 24 * 60;
        }

        return round((($endMinutes - $startMinutes) / 60) * 2) / 2;
    }

    /**
     * Karyawan yang boleh diberi lembur oleh manager ini:
     * - bawahan langsung,
     * - rekan satu store + department, KECUALI atasannya sendiri dan sesama
     *   pemegang permission assignment (supaya tidak saling memberi lembur),
     * - diri sendiri kalau punya permission assignmentSelf.
     */
    private function assignableEmployeesQuery(Employee $manager, bool $canSelf)
    {
        $myStoreIds = $manager->store()->pluck('stores_tables.id')->toArray();
        $myDeptIds  = $manager->department()->pluck('departments_tables.id')->toArray();
        $bawahanIds = $manager->bawahanList()->pluck('employees_tables.id')->toArray();

        $excludedIds = $manager->atasanList()->pluck('employees_tables.id')
            ->merge(User::permission('assignment')->whereNotNull('employee_id')->pluck('employee_id'))
            ->unique()
            ->all();

        return Employee::query()
            ->whereNull('deleted_at')
            ->whereIn('status', ['Active', 'Pending', 'On Leave'])
            ->where(function ($q) use ($myStoreIds, $myDeptIds, $bawahanIds, $excludedIds, $canSelf, $manager) {
                $q->where(function ($q1) use ($myStoreIds, $myDeptIds, $excludedIds) {
                    $q1->whereNotIn('id', $excludedIds)
                        ->whereHas('store', fn($sq) => $sq->whereIn('stores_tables.id', $myStoreIds))
                        ->whereHas('department', fn($dq) => $dq->whereIn('departments_tables.id', $myDeptIds));
                });
                if (!empty($bawahanIds)) {
                    $q->orWhereIn('id', $bawahanIds);
                }
                if ($canSelf) {
                    $q->orWhere('id', $manager->id);
                }
            });
    }

    /**
     * Pembuat assignment atau atasan karyawan saat ini.
     */
    private function canManage(Overtimesubmissions $submission): bool
    {
        $manager = Auth::user()->employee;
        if (!$manager) return false;

        return $submission->approver_id === $manager->id
            || $manager->bawahanList()->where('employees_tables.id', $submission->employee_id)->exists();
    }

    /**
     * Pesan error kalau lembur tidak boleh dibuat: sudah ada lembur di tanggal yang sama,
     * atau (Cash) payroll periodenya sudah di-generate sehingga lembur tidak akan terbayar.
     */
    private function findConflicts(array $employeeIds, array $dates, string $compensationType): ?string
    {
        $existing = Overtimesubmissions::with('employees:id,employee_name')
            ->whereIn('employee_id', $employeeIds)
            ->whereIn('date', $dates)
            ->whereIn('status', ['Approved', 'Approved HR'])
            ->first();

        if ($existing) {
            return "{$existing->employees?->employee_name} sudah punya lembur di tanggal {$existing->date->toDateString()}. Edit data yang sudah ada.";
        }

        if ($compensationType !== 'Cash') {
            return null;
        }

        $payroll = Payroll::with('employee:id,employee_name')
            ->whereIn('employee_id', $employeeIds)
            ->whereDate('period_start', '<=', max($dates))
            ->whereDate('period_end', '>=', min($dates))
            ->first();

        return $payroll
            ? "Payroll {$payroll->employee?->employee_name} untuk periode ini sudah di-generate, lembur Cash tidak akan terbayar. Hapus draft payroll-nya dulu."
            : null;
    }

    private function updateError(Overtimesubmissions $submission, array $validated, Carbon $minDate, Carbon $maxDate): ?string
    {
        if ($submission->status === 'Approved HR') {
            return 'Overtime sudah diproses ke payroll, tidak bisa diedit.';
        }
        if ($submission->status !== 'Approved') {
            return 'Assignment yang sudah di-cancel tidak bisa diedit.';
        }
        if ($submission->date->lt($minDate) || $submission->date->gt($maxDate)) {
            return 'Lembur dari periode yang sudah lewat tidak bisa diedit.';
        }

        $used = (float) ($submission->balance?->used_hours ?? 0);

        if (isset($validated['total_hours']) && $validated['total_hours'] < $used) {
            return "Jam baru ({$validated['total_hours']}) tidak boleh kurang dari yang sudah dipakai ({$used} jam).";
        }

        if (!isset($validated['date'])) {
            return null;
        }

        $newDate = Carbon::parse($validated['date'])->toDateString();
        if ($newDate === $submission->date->toDateString()) {
            return null;
        }

        // Tanggal menentukan masa berlaku, yang bisa jadi lebih pendek dari klaim yang sudah memakainya
        if ($used > 0) {
            return "Tanggal tidak bisa diubah karena saldo sudah dipakai {$used} jam.";
        }

        $duplicate = Overtimesubmissions::where('employee_id', $submission->employee_id)
            ->where('id', '!=', $submission->id)
            ->whereDate('date', $newDate)
            ->whereIn('status', ['Approved', 'Approved HR'])
            ->exists();

        return $duplicate ? "Karyawan ini sudah punya lembur di tanggal {$newDate}." : null;
    }

    private function getSubordinates(Employee $manager): Collection
    {
        return $manager->bawahanList()->get();
    }
}
