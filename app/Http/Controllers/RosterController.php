<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Roster;
use App\Models\Shifts;
use App\Models\Stores;
use App\Models\ToilLeaveRequests;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RosterController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    //  HELPER: Resolve allowed day_type berdasarkan status_employee
    // ─────────────────────────────────────────────────────────────

    /**
     * PKWT → Work, Off, Public Holiday, Leave, Cuti Melahirkan
     * OJT  → Work, Off, Public Holiday
     * DW   → Work, Off saja
     */
    private function allowedDayTypes(?string $statusEmployee): array
    {
        $status = strtoupper($statusEmployee ?? '');

        if ($status === 'DW') {
            return ['Work', 'Off'];
        }

        if ($status === 'On Job Training') {
            return ['Work', 'Off', 'Public Holiday'];
        }

        // PKWT dan status lainnya → semua day_type diperbolehkan
        return ['Work', 'Off', 'Public Holiday', 'Leave', 'Cuti Melahirkan'];
    }

    // ─────────────────────────────────────────────────────────────
    //  HELPER: Get tanggal TOIL approved per employee (1 query)
    // ─────────────────────────────────────────────────────────────

    /**
     * Ambil daftar tanggal yang ada TOIL Approved untuk
     * karyawan-karyawan tertentu dalam rentang tanggal tertentu.
     *
     * Return format:
     * [
     *   'employee_id_1' => ['2026-03-15', '2026-03-20'],
     *   'employee_id_2' => ['2026-03-18'],
     * ]
     */
    private function getToilApprovedDatesMap(array $employeeIds, string $startDate, string $endDate): array
    {
        $approvedLeaves = ToilLeaveRequests::select('employee_id', 'leave_date')
            ->whereIn('employee_id', $employeeIds)
            ->where('status', 'Approved')
            ->whereBetween('leave_date', [$startDate, $endDate])
            ->get();

        $map = [];
        foreach ($approvedLeaves as $leave) {
            $empId = $leave->employee_id;
            $date  = Carbon::parse($leave->leave_date)->toDateString();

            if (!isset($map[$empId])) {
                $map[$empId] = [];
            }
            $map[$empId][] = $date;
        }

        return $map;
    }

    /**
     * Cek apakah tanggal tertentu untuk karyawan tertentu
     * sudah ada TOIL leave Approved.
     */
    private function hasToilApproved(array $approvedMap, string $employeeId, string $date): bool
    {
        return isset($approvedMap[$employeeId])
            && in_array($date, $approvedMap[$employeeId]);
    }

    // ─────────────────────────────────────────────────────────────
    //  INDEX
    // ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $stores    = Stores::select('id', 'name')->whereNotNull('name')->orderBy('name')->get();
        // $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->toDateString();
        // $endDate   = $request->end_date   ?? Carbon::now()->endOfMonth()->toDateString();
        $today = Carbon::now();

if ($today->day >= 26) {
    // Periode: 26 bulan ini - 25 bulan depan
    $startDate = $request->start_date 
        ?? $today->copy()->day(26)->toDateString();

    $endDate = $request->end_date 
        ?? $today->copy()->addMonth()->day(25)->toDateString();
} else {
    // Periode: 26 bulan lalu - 25 bulan ini
    $startDate = $request->start_date 
        ?? $today->copy()->subMonth()->day(26)->toDateString();

    $endDate = $request->end_date 
        ?? $today->copy()->day(25)->toDateString();
}
        $storeId   = $request->store_id;
        $employees = collect();
        $shifts    = collect();
        $dates     = [];

        if ($storeId) {
            $employees = Employee::with([
                'position:id,name',
                'store:id,name',
                'rosters' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                      ->with('shift:id,shift_name,start_time,end_time');
                },
            ])
            ->select('id', 'employee_name', 'store_id', 'status_employee')
            ->whereNull('deleted_at')
            ->where('store_id', $storeId)
            ->whereIn('status', ['Active', 'Pending', 'On Leave'])
            ->orderBy('employee_name')
            ->get();

            $shifts = Shifts::where('store_id', $storeId)
                ->orderBy('shift_name')->get();

            $current = Carbon::parse($startDate);
            while ($current->lte(Carbon::parse($endDate))) {
                $dates[] = $current->copy();
                $current->addDay();
            }
        }

        return view('pages.Roster.Roster', compact(
            'employees', 'shifts', 'stores', 'dates', 'startDate', 'endDate', 'storeId'
        ));
    }

    // ─────────────────────────────────────────────────────────────
    //  STORE (klik cell)
    //  Update: tolak override kalau ada TOIL Approved
    // ─────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees_tables,id',
            'shift_id'    => 'nullable|exists:shifts_tables,id',
            'date'        => 'required|date',
            'day_type'    => 'required|in:Work,Off,Public Holiday,Leave,Cuti Melahirkan',
        ]);

        // Validasi day_type vs status_employee
        $employee = Employee::select('id', 'status_employee')
            ->find($request->employee_id);

        $allowed = $this->allowedDayTypes($employee?->status_employee);

        if (!in_array($request->day_type, $allowed)) {
            $status = strtoupper($employee?->status_employee ?? '-');
            return response()->json([
                'success' => false,
                'message' => "Karyawan dengan status {$status} tidak dapat di-assign day type \"{$request->day_type}\".",
            ], 422);
        }

        // Cek apakah tanggal ini ada TOIL approved
        $toilApproved = ToilLeaveRequests::where('employee_id', $request->employee_id)
            ->whereDate('leave_date', $request->date)
            ->where('status', 'Approved')
            ->exists();

        // Kalau ada TOIL approved & user coba set ke selain Off → tolak
        if ($toilApproved && $request->day_type !== 'Off') {
            return response()->json([
                'success' => false,
                'message' => "Karyawan punya TOIL Leave yang sudah Approved di tanggal ini. Cancel TOIL leave dulu via menu Approval kalau mau ubah jadwal.",
            ], 422);
        }

        $roster = Roster::updateOrCreate(
            ['employee_id' => $request->employee_id, 'date' => $request->date],
            [
                'shift_id' => $request->day_type === 'Work' ? $request->shift_id : null,
                'day_type' => $request->day_type,
                'notes'    => $request->notes,
            ]
        );

        return response()->json([
            'success'     => true,
            'roster'      => $roster->load('shift'),
            'roster_name' => $roster->shift?->shift_name ?? $request->day_type,
            'roster_time' => $roster->shift
                ? substr($roster->shift->start_time, 0, 5) . '-' . substr($roster->shift->end_time, 0, 5)
                : '',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  DESTROY (hapus 1 cell)
    //  Update: tolak delete kalau ada TOIL Approved
    // ─────────────────────────────────────────────────────────────

    public function destroy(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees_tables,id',
            'date'        => 'required|date',
        ]);

        // Cek apakah tanggal ini ada TOIL approved
        $toilApproved = ToilLeaveRequests::where('employee_id', $request->employee_id)
            ->whereDate('leave_date', $request->date)
            ->where('status', 'Approved')
            ->exists();

        if ($toilApproved) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa hapus roster — ada TOIL Leave yang sudah Approved untuk tanggal ini.',
            ], 422);
        }

        Roster::where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->delete();

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────────────────────
    //  BULK ASSIGN
    //  Update: force Off untuk tanggal TOIL Approved
    // ─────────────────────────────────────────────────────────────

    public function bulkAssign(Request $request)
    {
        $request->validate([
            'employee_ids'      => 'required|array|min:1',
            'employee_ids.*'    => 'exists:employees_tables,id',
            'shift_id'          => 'nullable|exists:shifts_tables,id',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'day_type'          => 'required|in:Work,Off,Public Holiday,Leave,Cuti Melahirkan',
            'skip_weekend'      => 'boolean',
            'saturday_shift'    => 'boolean',
            'saturday_shift_id' => 'nullable|exists:shifts_tables,id',
        ]);

        // Validasi day_type vs status_employee untuk setiap karyawan
        $employees = Employee::select('id', 'employee_name', 'status_employee')
            ->whereIn('id', $request->employee_ids)
            ->get()
            ->keyBy('id');

        $rejected = [];
        foreach ($employees as $emp) {
            $allowed = $this->allowedDayTypes($emp->status_employee);
            if (!in_array($request->day_type, $allowed)) {
                $rejected[] = "{$emp->employee_name} (status: " . strtoupper($emp->status_employee ?? '-') . ")";
            }
        }

        if (!empty($rejected)) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan berikut tidak dapat di-assign day type "' . $request->day_type . '": '
                           . implode(', ', $rejected) . '.',
            ], 422);
        }

        // Ambil map tanggal TOIL approved (1 query untuk semua)
        $toilApprovedMap = $this->getToilApprovedDatesMap(
            $request->employee_ids,
            $request->start_date,
            $request->end_date
        );

        // Generate daftar tanggal
        $dates   = [];
        $current = Carbon::parse($request->start_date);
        $end     = Carbon::parse($request->end_date);

        while ($current->lte($end)) {
            if ($request->skip_weekend && $current->isSunday()) {
                $current->addDay();
                continue;
            }
            $dates[] = $current->copy();
            $current->addDay();
        }

        $count        = 0;
        $skippedCount = 0;  // counter untuk tanggal yang force Off karena TOIL

        foreach ($request->employee_ids as $empId) {
            foreach ($dates as $date) {
                $dateStr = $date->toDateString();

                // Cek dulu: kalau ada TOIL approved, force jadi Off
                if ($this->hasToilApproved($toilApprovedMap, $empId, $dateStr)) {
                    Roster::updateOrCreate(
                        ['employee_id' => $empId, 'date' => $dateStr],
                        [
                            'shift_id' => null,
                            'day_type' => 'Off',
                        ]
                    );
                    $skippedCount++;
                    continue;
                }

                // Handle Sabtu
                if ($date->isSaturday()) {
                    if ($request->saturday_shift && $request->saturday_shift_id) {
                        Roster::updateOrCreate(
                            ['employee_id' => $empId, 'date' => $dateStr],
                            [
                                'shift_id' => $request->saturday_shift_id,
                                'day_type' => 'Work',
                            ]
                        );
                        $count++;
                    } elseif ($request->skip_weekend) {
                        continue;
                    } else {
                        Roster::updateOrCreate(
                            ['employee_id' => $empId, 'date' => $dateStr],
                            [
                                'shift_id' => $request->day_type === 'Work' ? $request->shift_id : null,
                                'day_type' => $request->day_type,
                            ]
                        );
                        $count++;
                    }
                    continue;
                }

                // Hari biasa
                Roster::updateOrCreate(
                    ['employee_id' => $empId, 'date' => $dateStr],
                    [
                        'shift_id' => $request->day_type === 'Work' ? $request->shift_id : null,
                        'day_type' => $request->day_type,
                    ]
                );
                $count++;
            }
        }

        // Response message yang informatif
        $message = "Assign schedule {$count} berhasil.";
        if ($skippedCount > 0) {
            $message .= " {$skippedCount} tanggal di-set Off karena ada TOIL Leave yang sudah Approved.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  COPY ROSTER
    //  Update: force Off untuk tanggal TOIL Approved
    // ─────────────────────────────────────────────────────────────

    public function copyRoster(Request $request)
    {
        $request->validate([
            'store_id'     => 'nullable|exists:stores_tables,id',
            'source_start' => 'required|date',
            'source_end'   => 'required|date|after_or_equal:source_start',
            'target_start' => 'required|date',
            'target_end'   => 'nullable|date|after_or_equal:target_start',
        ]);

        $sourceStart = Carbon::parse($request->source_start);
        $sourceEnd   = Carbon::parse($request->source_end);
        $targetStart = Carbon::parse($request->target_start);

        $sourceRosters = Roster::whereBetween('date', [
            $sourceStart->toDateString(),
            $sourceEnd->toDateString(),
        ])
        ->when($request->store_id, fn($q) =>
            $q->whereHas('employee', fn($eq) => $eq->where('store_id', $request->store_id))
        )
        ->orderBy('date')
        ->get();

        if ($sourceRosters->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada jadwal di periode sumber.',
            ]);
        }

        // Tentukan rentang target untuk ambil TOIL approved map
        $targetEndForCheck = $request->filled('target_end')
            ? Carbon::parse($request->target_end)
            : $targetStart->copy()->addDays($sourceStart->diffInDays($sourceEnd));

        $employeeIds = $sourceRosters->pluck('employee_id')->unique()->toArray();
        $toilApprovedMap = $this->getToilApprovedDatesMap(
            $employeeIds,
            $targetStart->toDateString(),
            $targetEndForCheck->toDateString()
        );

        $count        = 0;
        $skippedCount = 0;

        if ($request->filled('target_end')) {
            $targetEnd = Carbon::parse($request->target_end);

            $sourceMap = [];
            foreach ($sourceRosters as $src) {
                $offset = $sourceStart->diffInDays(Carbon::parse($src->date));
                $sourceMap[$src->employee_id][$offset] = $src;
            }

            $sourceLengthDays = $sourceStart->diffInDays($sourceEnd) + 1;
            $employeeIds      = array_keys($sourceMap);

            foreach ($employeeIds as $empId) {
                $current  = $targetStart->copy();
                $dayIndex = 0;

                while ($current->lte($targetEnd)) {
                    $srcOffset = $dayIndex % $sourceLengthDays;
                    $dateStr   = $current->toDateString();

                    // Kalau ada TOIL approved, force Off
                    if ($this->hasToilApproved($toilApprovedMap, $empId, $dateStr)) {
                        Roster::updateOrCreate(
                            ['employee_id' => $empId, 'date' => $dateStr],
                            ['shift_id' => null, 'day_type' => 'Off']
                        );
                        $skippedCount++;
                    } elseif (isset($sourceMap[$empId][$srcOffset])) {
                        $src = $sourceMap[$empId][$srcOffset];
                        Roster::updateOrCreate(
                            ['employee_id' => $empId, 'date' => $dateStr],
                            ['shift_id' => $src->shift_id, 'day_type' => $src->day_type]
                        );
                        $count++;
                    }

                    $current->addDay();
                    $dayIndex++;
                }
            }

        } else {
            $diffDays = $sourceStart->diffInDays($targetStart);

            foreach ($sourceRosters as $src) {
                $newDate = Carbon::parse($src->date)->addDays($diffDays)->toDateString();

                // Kalau ada TOIL approved di tanggal baru, force Off
                if ($this->hasToilApproved($toilApprovedMap, $src->employee_id, $newDate)) {
                    Roster::updateOrCreate(
                        ['employee_id' => $src->employee_id, 'date' => $newDate],
                        ['shift_id' => null, 'day_type' => 'Off']
                    );
                    $skippedCount++;
                } else {
                    Roster::updateOrCreate(
                        ['employee_id' => $src->employee_id, 'date' => $newDate],
                        ['shift_id' => $src->shift_id, 'day_type' => $src->day_type]
                    );
                    $count++;
                }
            }
        }

        // Response message
        $message = "Schedule copied {$count} berhasil.";
        if ($skippedCount > 0) {
            $message .= " {$skippedCount} tanggal di-set Off karena ada TOIL Leave yang sudah Approved.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  BULK DELETE
    //  Update: protect tanggal TOIL Approved (versi AMAN, loop per karyawan)
    // ─────────────────────────────────────────────────────────────

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'employee_ids'   => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees_tables,id',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
        ]);

        // Ambil map tanggal TOIL approved (1 query)
        $toilApprovedMap = $this->getToilApprovedDatesMap(
            $request->employee_ids,
            $request->start_date,
            $request->end_date
        );

        $count          = 0;
        $protectedCount = 0;

        // Loop per karyawan — logic clear & mudah debug
        foreach ($request->employee_ids as $empId) {
            $protectedDates = $toilApprovedMap[$empId] ?? [];

            $query = Roster::where('employee_id', $empId)
                ->whereBetween('date', [$request->start_date, $request->end_date]);

            // Exclude tanggal yang ada TOIL approved
            if (!empty($protectedDates)) {
                $query->whereNotIn('date', $protectedDates);
                $protectedCount += count($protectedDates);
            }

            $count += $query->delete();
        }

        // Response message
        $message = "Berhasil menghapus {$count} jadwal.";
        if ($protectedCount > 0) {
            $message .= " {$protectedCount} jadwal di-protect karena ada TOIL Leave yang sudah Approved.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }
}