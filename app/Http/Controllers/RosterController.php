<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Ph;
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

    private function allowedDayTypes(?string $statusEmployee): array
    {
        $status = strtoupper($statusEmployee ?? '');

        if ($status === 'DW') {
            return ['Work', 'Off'];
        }

        if (str_contains($status, 'JOB TRAINING')) {
            return ['Work', 'Off', 'Public Holiday'];
        }

        return ['Work', 'Off', 'Public Holiday', 'Leave', 'Cuti Melahirkan'];
    }

    // ─────────────────────────────────────────────────────────────
    //  HELPER: Mapping status_employee → CSS class badge
    // ─────────────────────────────────────────────────────────────

    private function getStatusClass(?string $statusEmployee): string
    {
        $status = strtoupper($statusEmployee ?? '');

        if ($status === 'DW') {
            return 'status-dw';
        }

        if (str_contains($status, 'JOB TRAINING')) {
            return 'status-ojt';
        }

        return 'status-pkwt';
    }

    // ─────────────────────────────────────────────────────────────
    //  HELPER: Build data 1 cell roster (badge class, name, time, type)
    // ─────────────────────────────────────────────────────────────

    private function buildCellData($roster, Carbon $date): array
    {
        $isWeekend = $date->isWeekend();
        $isToday   = $date->isToday();

        $badgeClass = '';
        $badgeName  = '+';
        $badgeTime  = '';
        $cellType   = 'empty';

        if ($roster) {
            if ($roster->day_type === 'TOIL Off') {
                $badgeClass = 'r-badge r-off';
                $badgeName  = 'TOIL Off';
                $cellType   = 'toiloff';
            }elseif ($roster->day_type === 'Off') {
                $badgeClass = 'r-badge r-off';
                $badgeName  = 'Off';
                $cellType   = 'off';
            } elseif ($roster->day_type === 'Public Holiday') {
                $badgeClass = 'r-badge r-holiday';
                $badgeName  = 'Public Holiday';
                $cellType   = 'holiday';
            } elseif ($roster->day_type === 'Cuti Melahirkan') {
                $badgeClass = 'r-badge r-leave';
                $badgeName  = 'Cuti Melahirkan';
                $cellType   = 'melahirkan';
            } elseif ($roster->day_type === 'Leave') {
                $badgeClass = 'r-badge r-leave';
                $badgeName  = 'Leave';
                $cellType   = 'leave';
            } elseif ($roster->shift) {
                $badgeClass = 'r-badge r-work';
                $badgeName  = $roster->shift->shift_name;
                $badgeTime  = substr($roster->shift->start_time, 0, 5)
                    . '-'
                    . substr($roster->shift->end_time, 0, 5);
                $cellType   = 'work';
            } else {
                $badgeClass = 'r-badge r-work';
                $badgeName  = 'Work';
                $cellType   = 'work';
            }
        } elseif ($isWeekend) {
            $badgeClass = 'r-badge r-off';
            $badgeName  = 'Off';
            $cellType   = 'weekend';
        }

        return [
            'badge_class' => $badgeClass,
            'badge_name'  => $badgeName,
            'badge_time'  => $badgeTime,
            'cell_type'   => $cellType,
            'has_roster'  => (bool) $roster,
            'is_weekend'  => $isWeekend,
            'is_today'    => $isToday,
            'shift_id'    => $roster?->shift_id ?? '',
            'day_type'    => $roster?->day_type ?? 'Work',
            'notes'       => $roster?->notes ?? '',
            'date_str'    => $date->toDateString(),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  HELPER: Get tanggal TOIL approved per employee (1 query)
    // ─────────────────────────────────────────────────────────────

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

    private function hasToilApproved(array $approvedMap, string $employeeId, string $date): bool
    {
        return isset($approvedMap[$employeeId])
            && in_array($date, $approvedMap[$employeeId]);
    }

    // ─────────────────────────────────────────────────────────────
    //  HELPER: Public Holiday
    // ─────────────────────────────────────────────────────────────

    /**
     * Map: [date => ['type' => 'All'|'Hindu'|'Non Hindu', 'remark' => 'Tahun Baru Imlek']]
     */
    private function getPublicHolidaysMap(string $startDate, string $endDate): array
    {
        $holidays = Ph::select('date', 'type', 'remark')
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $map = [];
        foreach ($holidays as $ph) {
            $date = Carbon::parse($ph->date)->toDateString();
            $map[$date] = [
                'type'   => $ph->type,
                'remark' => $ph->remark,
            ];
        }

        return $map;
    }

    private function isPublicHolidayForEmployee(array $phMap, string $date, ?string $religion): bool
    {
        if (!isset($phMap[$date])) {
            return false;
        }

        $phType = $phMap[$date]['type'];

        if ($phType === 'All') {
            return true;
        }

        if (empty($religion)) {
            return false;
        }

        if ($phType === 'Hindu') {
            return $religion === 'Hindu';
        }

        if ($phType === 'Non Hindu') {
            return $religion !== 'Hindu';
        }

        return false;
    }

    /**
     * Ambil remark PH untuk tanggal tertentu (mis: 'Tahun Baru Imlek').
     * Return '' kalau tanggal bukan PH.
     */
    private function getPublicHolidayRemark(array $phMap, string $date): string
    {
        return $phMap[$date]['remark'] ?? '';
    }

    // ─────────────────────────────────────────────────────────────
    //  INDEX
    //  Pre-compute semua data untuk view (view 100% bersih dari @php)
    // ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $stores = Stores::select('id', 'name', 'is_auto_generate')
            ->whereNotNull('name')
            ->orderBy('name')
            ->get();

        $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->toDateString();
        $endDate   = $request->end_date   ?? Carbon::now()->endOfMonth()->toDateString();
        $storeId   = $request->store_id;

        $employees        = collect();
        $shifts           = collect();
        $dates            = [];
        $dateHeaders      = [];
        $currentStoreName = '';
        $showAutoGenerate = false;

        if ($storeId) {
            $currentStore     = $stores->firstWhere('id', $storeId);
            $currentStoreName = $currentStore?->name ?? '';
            $showAutoGenerate = (bool) ($currentStore?->is_auto_generate ?? false);

            $employees = Employee::with([
                'position:id,name',
                'store:id,name',
                'department:id,department_name',
                'rosters' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                        ->with('shift:id,shift_name,start_time,end_time');
                },
            ])
                ->select('id', 'employee_name', 'store_id', 'status_employee', 'religion', 'position_id', 'department_id')
                ->whereNull('deleted_at')
                ->where('store_id', $storeId)
                ->orderBy('employee_name')
                ->get();

            $shifts = Shifts::where('store_id', $storeId)
                ->orderBy('shift_name')
                ->get();

            $current   = Carbon::parse($startDate);
            $endCarbon = Carbon::parse($endDate);
            while ($current->lte($endCarbon)) {
                $dates[] = $current->copy();
                $current->addDay();
            }

            // Pre-compute per employee
            foreach ($employees as $employee) {
                $employee->status_class = $this->getStatusClass($employee->status_employee);

                $rosterByDate = $employee->rosters->keyBy(
                    fn($r) => Carbon::parse($r->date)->toDateString()
                );

                $cells = [];
                foreach ($dates as $carbon) {
                    $dateStr = $carbon->toDateString();
                    $roster  = $rosterByDate->get($dateStr);
                    $cells[$dateStr] = $this->buildCellData($roster, $carbon);
                }
                $employee->cells = $cells;
            }

            // Pre-compute date headers
            foreach ($dates as $carbon) {
                $dateHeaders[] = [
                    'carbon'     => $carbon,
                    'day_label'  => $carbon->format('D'),
                    'date_label' => $carbon->format('d/m'),
                    'is_weekend' => $carbon->isWeekend(),
                    'is_today'   => $carbon->isToday(),
                ];
            }
        }

        return view('pages.Roster.Roster', compact(
            'employees',
            'shifts',
            'stores',
            'dates',
            'dateHeaders',
            'startDate',
            'endDate',
            'storeId',
            'currentStoreName',
            'showAutoGenerate'
        ));
    }

    // ─────────────────────────────────────────────────────────────
    //  STORE (klik cell)
    // ─────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees_tables,id',
            'shift_id'    => 'nullable|exists:shifts_tables,id',
            'date'        => 'required|date',
            'day_type'    => 'required|in:Work,Off,Public Holiday,Leave,Cuti Melahirkan',
        ]);

        $employee = Employee::select('id', 'status_employee', 'religion')
            ->find($request->employee_id);

        $allowed = $this->allowedDayTypes($employee?->status_employee);

        if (!in_array($request->day_type, $allowed)) {
            $status = strtoupper($employee?->status_employee ?? '-');
            return response()->json([
                'success' => false,
                'message' => "Karyawan dengan status {$status} tidak dapat di-assign day type \"{$request->day_type}\".",
            ], 422);
        }

        $toilApproved = ToilLeaveRequests::where('employee_id', $request->employee_id)
            ->whereDate('leave_date', $request->date)
            ->where('status', 'Approved')
            ->exists();

        if ($toilApproved) {
            return response()->json([
                'success' => false,
                'message' => "Karyawan punya TOIL Leave yang sudah Approved di tanggal ini. Cancel TOIL leave dulu via menu Approval kalau mau ubah jadwal.",
            ], 422);
        }

        $phMap = $this->getPublicHolidaysMap($request->date, $request->date);
        $isPH  = $this->isPublicHolidayForEmployee($phMap, $request->date, $employee?->religion);

        if ($isPH && $request->day_type !== 'Public Holiday') {
            return response()->json([
                'success' => false,
                'message' => "Tanggal ini adalah Public Holiday untuk karyawan ini. Day type harus \"Public Holiday\".",
            ], 422);
        }

        // Kalau Public Holiday, auto-isi notes dari ph.remark (kecuali user isi notes manual)
        $notes = $request->notes;
        if ($request->day_type === 'Public Holiday') {
            $phRemark = $this->getPublicHolidayRemark($phMap, $request->date);
            if (empty($notes) && !empty($phRemark)) {
                $notes = $phRemark;
            }
        }

        $roster = Roster::updateOrCreate(
            ['employee_id' => $request->employee_id, 'date' => $request->date],
            [
                'shift_id' => $request->day_type === 'Work' ? $request->shift_id : null,
                'day_type' => $request->day_type,
                'notes'    => $notes,
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
    // ─────────────────────────────────────────────────────────────

    public function destroy(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees_tables,id',
            'date'        => 'required|date',
        ]);

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

        $employee = Employee::select('id', 'religion')->find($request->employee_id);
        $phMap    = $this->getPublicHolidaysMap($request->date, $request->date);
        $isPH     = $this->isPublicHolidayForEmployee($phMap, $request->date, $employee?->religion);

        if ($isPH) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa hapus roster — tanggal ini adalah Public Holiday.',
            ], 422);
        }

        Roster::where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->delete();

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────────────────────
    //  BULK ASSIGN
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

        $employees = Employee::select('id', 'employee_name', 'status_employee', 'religion')
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

        $toilApprovedMap = $this->getToilApprovedDatesMap(
            $request->employee_ids,
            $request->start_date,
            $request->end_date
        );

        $phMap = $this->getPublicHolidaysMap($request->start_date, $request->end_date);

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

        $count     = 0;
        $toilCount = 0;
        $phCount   = 0;

        foreach ($request->employee_ids as $empId) {
            $employee = $employees[$empId] ?? null;
            $religion = $employee?->religion;

            foreach ($dates as $date) {
                $dateStr = $date->toDateString();

                if ($this->hasToilApproved($toilApprovedMap, $empId, $dateStr)) {
                    Roster::updateOrCreate(
                        ['employee_id' => $empId, 'date' => $dateStr],
                        ['shift_id' => null, 'day_type' => 'TOIL Off']
                    );
                    $toilCount++;
                    continue;
                }

                if ($this->isPublicHolidayForEmployee($phMap, $dateStr, $religion)) {
                    Roster::updateOrCreate(
                        ['employee_id' => $empId, 'date' => $dateStr],
                        [
                            'shift_id' => null,
                            'day_type' => 'Public Holiday',
                            'notes'    => $this->getPublicHolidayRemark($phMap, $dateStr),
                        ]
                    );
                    $phCount++;
                    continue;
                }

                if ($date->isSaturday()) {
                    if ($request->saturday_shift && $request->saturday_shift_id) {
                        Roster::updateOrCreate(
                            ['employee_id' => $empId, 'date' => $dateStr],
                            ['shift_id' => $request->saturday_shift_id, 'day_type' => 'Work']
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

        $message = "Assign schedule {$count} berhasil.";
        if ($toilCount > 0) {
            $message .= " {$toilCount} tanggal di-set Off karena ada TOIL Leave Approved.";
        }
        if ($phCount > 0) {
            $message .= " {$phCount} tanggal di-set Public Holiday otomatis.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  COPY ROSTER
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
            ->when(
                $request->store_id,
                fn($q) =>
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

        $targetEndForCheck = $request->filled('target_end')
            ? Carbon::parse($request->target_end)
            : $targetStart->copy()->addDays($sourceStart->diffInDays($sourceEnd));

        $employeeIds = $sourceRosters->pluck('employee_id')->unique()->toArray();

        $employees = Employee::select('id', 'religion')
            ->whereIn('id', $employeeIds)
            ->get()
            ->keyBy('id');

        $toilApprovedMap = $this->getToilApprovedDatesMap(
            $employeeIds,
            $targetStart->toDateString(),
            $targetEndForCheck->toDateString()
        );

        $phMap = $this->getPublicHolidaysMap(
            $targetStart->toDateString(),
            $targetEndForCheck->toDateString()
        );

        $count     = 0;
        $toilCount = 0;
        $phCount   = 0;

        if ($request->filled('target_end')) {
            $targetEnd = Carbon::parse($request->target_end);

            $sourceMap = [];
            foreach ($sourceRosters as $src) {
                $offset = $sourceStart->diffInDays(Carbon::parse($src->date));
                $sourceMap[$src->employee_id][$offset] = $src;
            }

            $sourceLengthDays = $sourceStart->diffInDays($sourceEnd) + 1;
            $employeeIdsLoop  = array_keys($sourceMap);

            foreach ($employeeIdsLoop as $empId) {
                $religion = $employees[$empId]?->religion ?? null;
                $current  = $targetStart->copy();
                $dayIndex = 0;

                while ($current->lte($targetEnd)) {
                    $srcOffset = $dayIndex % $sourceLengthDays;
                    $dateStr   = $current->toDateString();

                    if ($this->hasToilApproved($toilApprovedMap, $empId, $dateStr)) {
                        Roster::updateOrCreate(
                            ['employee_id' => $empId, 'date' => $dateStr],
                            ['shift_id' => null, 'day_type' => 'TOIL Off']
                        );
                        $toilCount++;
                    } elseif ($this->isPublicHolidayForEmployee($phMap, $dateStr, $religion)) {
                        Roster::updateOrCreate(
                            ['employee_id' => $empId, 'date' => $dateStr],
                            [
                                'shift_id' => null,
                                'day_type' => 'Public Holiday',
                                'notes'    => $this->getPublicHolidayRemark($phMap, $dateStr),
                            ]
                        );
                        $phCount++;
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
                $newDate  = Carbon::parse($src->date)->addDays($diffDays)->toDateString();
                $religion = $employees[$src->employee_id]?->religion ?? null;

                if ($this->hasToilApproved($toilApprovedMap, $src->employee_id, $newDate)) {
                    Roster::updateOrCreate(
                        ['employee_id' => $src->employee_id, 'date' => $newDate],
                        ['shift_id' => null, 'day_type' => 'TOIL Off']
                    );
                    $toilCount++;
                } elseif ($this->isPublicHolidayForEmployee($phMap, $newDate, $religion)) {
                    Roster::updateOrCreate(
                        ['employee_id' => $src->employee_id, 'date' => $newDate],
                        [
                            'shift_id' => null,
                            'day_type' => 'Public Holiday',
                            'notes'    => $this->getPublicHolidayRemark($phMap, $newDate),
                        ]
                    );
                    $phCount++;
                } else {
                    Roster::updateOrCreate(
                        ['employee_id' => $src->employee_id, 'date' => $newDate],
                        ['shift_id' => $src->shift_id, 'day_type' => $src->day_type]
                    );
                    $count++;
                }
            }
        }

        $message = "Schedule copied {$count} berhasil.";
        if ($toilCount > 0) {
            $message .= " {$toilCount} tanggal di-set Off karena ada TOIL Leave Approved.";
        }
        if ($phCount > 0) {
            $message .= " {$phCount} tanggal di-set Public Holiday otomatis.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  BULK DELETE
    // ─────────────────────────────────────────────────────────────

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'employee_ids'   => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees_tables,id',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
        ]);

        $employees = Employee::select('id', 'religion')
            ->whereIn('id', $request->employee_ids)
            ->get()
            ->keyBy('id');

        $toilApprovedMap = $this->getToilApprovedDatesMap(
            $request->employee_ids,
            $request->start_date,
            $request->end_date
        );

        $phMap = $this->getPublicHolidaysMap($request->start_date, $request->end_date);

        $count         = 0;
        $toilProtected = 0;
        $phProtected   = 0;

        foreach ($request->employee_ids as $empId) {
            $religion  = $employees[$empId]?->religion ?? null;
            $toilDates = $toilApprovedMap[$empId] ?? [];

            $phDates = [];
            foreach (array_keys($phMap) as $phDate) {
                if ($this->isPublicHolidayForEmployee($phMap, $phDate, $religion)) {
                    $phDates[] = $phDate;
                }
            }

            $protectedDates = array_unique(array_merge($toilDates, $phDates));

            $query = Roster::where('employee_id', $empId)
                ->whereBetween('date', [$request->start_date, $request->end_date]);

            if (!empty($protectedDates)) {
                $query->whereNotIn('date', $protectedDates);
                $toilProtected += count($toilDates);
                $phProtected   += count($phDates);
            }

            $count += $query->delete();
        }

        $message = "Berhasil menghapus {$count} jadwal.";
        if ($toilProtected > 0) {
            $message .= " {$toilProtected} jadwal di-protect karena TOIL Leave Approved.";
        }
        if ($phProtected > 0) {
            $message .= " {$phProtected} jadwal di-protect karena Public Holiday.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }
}
