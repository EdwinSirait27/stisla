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
        $myStoreIds = $manager->store()->pluck('stores_tables.id')->toArray();
    $myDeptIds  = $manager->department()->pluck('departments_tables.id')->toArray();
    $bawahanIds = $manager->bawahanList()->pluck('employees_tables.id')->toArray();

    // ── Query employee ──
    $employees = Employee::select('id', 'employee_name', 'employee_pengenal')
     ->with(['store' => fn($q) => $q->wherePivot('is_primary', true)])
        ->whereNull('deleted_at')
        ->whereIn('status', ['Active', 'Pending', 'On Leave'])
        ->where(function ($q) use ($myStoreIds, $myDeptIds, $bawahanIds) {
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
        })
        ->orderBy('employee_name')
        ->get();
        $today = now();

if ($today->day >= 26) {
    $minDate = $today->copy()->day(26);
    $maxDate = $today->copy()->addMonth()->day(25);
} else {
    $minDate = $today->copy()->subMonth()->day(26);
    $maxDate = $today->copy()->day(25);
}


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
        $validated = $request->validate([
            'employee_ids'      => 'required|array|min:1',
            'employee_ids.*'    => 'exists:employees_tables,id',
           
'date' => [
    'required',
    'date',
    function ($attribute, $value, $fail) {
        $date = Carbon::parse($value);

        if ($date->day < 26) {
            $fail('Tanggal mulai harus tanggal 26 atau setelahnya.');
        }
    }
],
'end_date' => [
    'required',
    'date',
    'after_or_equal:date',
    function ($attribute, $value, $fail) use ($request) {

        $start = Carbon::parse($request->date);
        $end   = Carbon::parse($value);

        $maxEnd = $start->copy()
            ->addMonthNoOverflow()
            ->day(25);

        if ($end->gt($maxEnd)) {
            $fail("Tanggal selesai maksimal {$maxEnd->format('d-m-Y')}.");
        }
    }
],

            'start_time'        => 'nullable|date_format:H:i',
            'end_time'          => 'nullable|date_format:H:i',
            'total_hours'       => 'required|numeric|min:0.5|max:24',
            'compensation_type' => 'required|in:Cash,Toil',
            'reason'            => 'required|string|min:10|max:1000',
        ]);

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

$validSubordinateIds = Employee::select('id')
    ->whereNull('deleted_at')
    ->whereIn('status', ['Active', 'Pending', 'On Leave'])
    ->where(function ($q) use ($myStoreIds, $myDeptIds, $bawahanIds) {
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

            $created = 0;
            foreach ($validated['employee_ids'] as $empId) {
                Overtimesubmissions::create([
                    'employee_id'       => $empId,
                    'approver_id'       => $manager->id,
                    'date'              => $validated['date'],
                    'end_date'          => $validated['end_date'],
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

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil assign overtime untuk {$created} karyawan.",
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
    private function getSubordinates(Employee $manager): Collection
    {
        return $manager->bawahanList()->get();
    }
}
