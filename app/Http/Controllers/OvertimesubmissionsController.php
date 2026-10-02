<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Overtimesubmissions;
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

        $myStoreIds = $manager->store()->pluck('stores_tables.id')->toArray();
    $myDeptIds  = $manager->department()->pluck('departments_tables.id')->toArray();
    $bawahanIds = $manager->bawahanList()->pluck('employees_tables.id')->toArray();

    // ── Query employee ──
    $employees = Employee::select('id', 'employee_name', 'employee_pengenal')
     ->with(['store' => fn($q) => $q->wherePivot('is_primary', true)])
        ->whereNull('deleted_at')
        ->when(!$canSelf, fn($q) => $q->where('id', '!=', $manager->id))
        ->whereIn('status', ['Active', 'Pending', 'On Leave'])
        ->where(function ($q) use ($myStoreIds, $myDeptIds, $bawahanIds, $canSelf, $manager) {
            // Kepunyaan sendiri: store + department sama
            $q->where(function ($q1) use ($myStoreIds, $myDeptIds) {
                $q1->whereHas('store', fn($sq) =>
                    $sq->whereIn('stores_tables.id', $myStoreIds)
                )
                ->whereHas('department', fn($dq) =>
                    $dq->whereIn('departments_tables.id', $myDeptIds)
                );
            });
            // Atau bawahan langsung (beda company/store tetap muncul)
            if (!empty($bawahanIds)) {
                $q->orWhereIn('id', $bawahanIds);
            }
            // Atau diri sendiri (kalau punya permission assignmentSelf)
            if ($canSelf) {
                $q->orWhere('id', $manager->id);
            }
        })
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

        $query = Overtimesubmissions::with([
            'employees:id,employee_name,pin',
            'approver:id,employee_name',
            'balance',
        ])
            ->where('approver_id', $manager->id)
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
                'balance_status'    => $row->balance?->status ?? '-',
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

            'start_time'        => 'nullable|date_format:H:i',
            'end_time'          => 'nullable|date_format:H:i',
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

        // $validSubordinateIds = $this->getSubordinates($manager)
        //     ->where('status', 'Active')
        //     ->pluck('id')
        //     ->toArray();
        $myStoreIds = $manager->store()->pluck('stores_tables.id')->toArray();
$myDeptIds  = $manager->department()->pluck('departments_tables.id')->toArray();
$bawahanIds = $manager->bawahanList()->pluck('employees_tables.id')->toArray();

$canSelf = $user->can('assignmentSelf');

// Assign ke diri sendiri tanpa permission → tolak dengan pesan jelas
if (!$canSelf && in_array($manager->id, $validated['employee_ids'], true)) {
    return response()->json([
        'success' => false,
        'message' => 'Anda tidak punya izin untuk assign lembur ke diri sendiri (permission: assignmentSelf).',
    ], 403);
}

$validSubordinateIds = Employee::select('id')
    ->whereNull('deleted_at')
    ->whereIn('status', ['Active', 'Pending', 'On Leave'])
    ->where(function ($q) use ($myStoreIds, $myDeptIds, $bawahanIds, $canSelf, $manager) {
        $q->where(function ($q1) use ($myStoreIds, $myDeptIds) {
            $q1->whereHas('store', fn($sq) =>
                $sq->whereIn('stores_tables.id', $myStoreIds)
            )
            ->whereHas('department', fn($dq) =>
                $dq->whereIn('departments_tables.id', $myDeptIds)
            );
        });
        if (!empty($bawahanIds)) {
            $q->orWhereIn('id', $bawahanIds);
        }
        if ($canSelf) {
            $q->orWhere('id', $manager->id);
        }
    })
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

        try {
            DB::beginTransaction();

            // 1 record per karyawan per hari → total jam = jam per hari × jumlah hari
            $dates = [];
            for ($d = Carbon::parse($validated['date']); $d->lte(Carbon::parse($validated['end_date'])); $d->addDay()) {
                $dates[] = $d->toDateString();
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
        $validated = $request->validate([
            'date'        => 'sometimes|date',
            'start_time'  => 'nullable|date_format:H:i',
            'end_time'    => 'nullable|date_format:H:i',
            'total_hours' => 'sometimes|numeric|min:0.5|max:24',
            'reason'      => 'sometimes|string|min:10|max:1000',
        ]);

        // Jam dihitung ulang di server dari start/end time
        if (!empty($validated['start_time']) && !empty($validated['end_time'])) {
            $hours = $this->calculateHours($validated['start_time'], $validated['end_time']);
            if ($hours < 0.5) {
                return response()->json(['success' => false, 'message' => 'Jam selesai harus setelah jam mulai (minimal 0.5 jam).'], 422);
            }
            $validated['total_hours'] = $hours;
        }

        $submission = Overtimesubmissions::with('balance')->findOrFail($id);
         if ($submission->status === 'Approved HR') {
        return back()->with('error', 'Overtime sudah diproses ke payroll, tidak bisa diedit.');
    }

        $manager = Auth::user()->employee;
        if ($submission->approver_id !== $manager->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak punya akses untuk edit assignment ini.',
            ], 403);
        }

        if ($submission->balance && isset($validated['total_hours'])) {
            $used = (float) $submission->balance->used_hours;
            if ($validated['total_hours'] < $used) {
                return response()->json([
                    'success' => false,
                    'message' => "Jam baru ({$validated['total_hours']}) tidak boleh kurang dari yang sudah dipakai ({$used} jam).",
                ], 422);
            }
        }

        try {
            DB::beginTransaction();
            $submission->update($validated);

            if (isset($validated['total_hours'])) {
                if ($submission->balance) {
                    $submission->balance->update(['earned_hours' => $validated['total_hours']]);
                    $submission->balance->refresh(); // ← remaining_hours (generated column) perlu dibaca ulang
                    $submission->balance->refreshStatus();
                } else {
                    $submission->createOrUpdateBalance();
                }
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
        $submission = Overtimesubmissions::with('balance')->findOrFail($id);
            if ($submission->status === 'Approved HR') {
        return back()->with('error', 'Overtime sudah diproses ke payroll, tidak bisa diedit.');
    }
        $manager    = Auth::user()->employee;

        if ($submission->approver_id !== $manager->id) {
            return response()->json(['success' => false, 'message' => 'Anda tidak punya akses untuk cancel assignment ini.'], 403);
        }

        if ($submission->balance && $submission->balance->used_hours > 0) {
            return response()->json([
                'success' => false,
                'message' => "Tidak bisa cancel — saldo sudah dipakai {$submission->balance->used_hours} jam.",
            ], 422);
        }

        try {
            DB::beginTransaction();
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
        $manager   = Auth::user()->employee;
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
    private function getSubordinates(Employee $manager): Collection
    {
        return $manager->bawahanList()->get();
    }
}
