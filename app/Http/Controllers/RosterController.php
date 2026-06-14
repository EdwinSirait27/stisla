<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Ph;
use App\Models\Roster;
use App\Models\User;
use App\Models\Shifts;
use App\Models\Stores;
use App\Models\RosterSetting;
use App\Models\PublicHoliday;
use Yajra\DataTables\DataTables;
use Spatie\Activitylog\Models\Activity;
use App\Models\ToilLeaveRequests;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RosterHistoryExport;

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


    private function isSPVOnly(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();
        return $user->hasPermissionTo('ManageRosterSPVManager')
            && !$user->hasPermissionTo('ManageRoster');
    }

    private function checkRosterWindow(): bool
    {
        $setting = RosterSetting::where('is_active', true)->latest()->first();
        if (!$setting) return false;

        $today    = now()->day;
        $openDay  = $setting->open_day;
        $closeDay = $setting->close_day;

        if ($openDay <= $closeDay) {
            return $today >= $openDay && $today <= $closeDay;
        }

        // Lintas bulan: misal open 25, close 3
        return $today >= $openDay || $today <= $closeDay;
    }

    public function index(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        $canManageAll = $user->hasPermissionTo('ManageRoster');
        $canManageSPV = $user->hasPermissionTo('ManageRosterSPVManager');
        $canView      = $user->hasPermissionTo('ViewRoster');


        if (!$canManageAll && !$canManageSPV && !$canView) {
            return abort(403, 'Unauthorized');
        }


        $myEmployee  = optional($user->employee);
        // $myStoreId   = $myEmployee->store_id;
        // $myStoreName = optional($myEmployee->store)->name;
        // $myDepartmentId = $myEmployee->department_id;
//         $myStore        = $myEmployee->store?->wherePivot('is_primary', true)->first();
// $myStoreId      = $myStore?->id;
// $myStoreName    = $myStore?->name;
// $myDepartment   = $myEmployee->department?->wherePivot('is_primary', true)->first();
// $myDepartmentId = $myDepartment?->id;
if ($myEmployee) {
    $myStore        = $myEmployee->store()->wherePivot('is_primary', true)->first();
    $myStoreId      = $myStore?->id;
    $myStoreName    = $myStore?->name;
    $myDepartment   = $myEmployee->department()->wherePivot('is_primary', true)->first();
    $myDepartmentId = $myDepartment?->id;
} else {
    $myStore        = null;
    $myStoreId      = null;
    $myStoreName    = null;
    $myDepartment   = null;
    $myDepartmentId = null;
}

        $today            = now();
        $defaultStartDate = $today->copy()->subMonth()->day(26)->toDateString();
        $defaultEndDate   = $today->copy()->day(25)->toDateString();

        if ($canView && !$canManageAll && !$canManageSPV) {
            $storeId = $myStoreId;
        } else {
            // $storeId = $request->store_id;
        }

        if (!$canManageAll && $canManageSPV) {
            // Guard store_id
            // if ($request->store_id && $request->store_id !== $myStoreId) {
            //     return redirect()->route('roster.index', [
            //         'store_id'   => $myStoreId,
            //         'start_date' => $request->start_date,
            //         'end_date'   => $request->end_date,
            //     ]);
            // }
            if ($request->store_id && $request->store_id !== $myStoreId) {
    return redirect()->route('roster.index', [
                'store_id'   => $myStoreId,
                    'start_date' => $request->start_date,
                    'end_date'   => $request->end_date,
    ]);
}

            // Guard date range
            $maxStartDate = Carbon::parse($defaultStartDate)->addMonth();
            $maxEndDate   = Carbon::parse($defaultEndDate)->addMonth();

            if ($request->start_date && Carbon::parse($request->start_date)->gt($maxStartDate)) {
                return redirect()->route('roster.index', [
                    'store_id'   => $request->store_id,
                    'start_date' => $defaultStartDate,
                    'end_date'   => $request->end_date,
                ]);
            }

            if ($request->end_date && Carbon::parse($request->end_date)->gt($maxEndDate)) {
                return redirect()->route('roster.index', [
                    'store_id'   => $request->store_id,
                    'start_date' => $request->start_date,
                    'end_date'   => $defaultEndDate,
                ]);
            }
        }

        $startDate = $request->start_date ?? $defaultStartDate;
        $endDate   = $request->end_date   ?? $defaultEndDate;
        $storeId   = $request->store_id;

        $stores = Stores::select('id', 'name')
            ->whereNotNull('name')
            ->orderBy('name')
            ->get();

        $employees = collect();
        $shifts    = collect();
        $dates     = [];

        if ($storeId) {
            // $employeeQuery = Employee::with([
            //     'position:id,name',
            //     'store:id,name',
            //     'department:id,department_name',
            //     'rosters' => fn($q) => $q
            //         ->whereBetween('date', [$startDate, $endDate])
            //         ->with('shift:id,shift_name,start_time,end_time'),
            // ])
            //     ->select('id', 'employee_name', 'store_id', 'status_employee', 'status', 'company_id', 'department_id', 'position_id')
            //     ->whereNull('deleted_at')
            //     ->where('store_id', $storeId)
            //     ->orderBy('employee_name');
            $employeeQuery = Employee::with([
    'store'      => fn($q) => $q->wherePivot('is_primary', true),
    'position'   => fn($q) => $q->wherePivot('is_primary', true),
    'department' => fn($q) => $q->wherePivot('is_primary', true),
    'rosters'    => fn($q) => $q
        ->whereBetween('date', [$startDate, $endDate])
        ->with('shift:id,shift_name,start_time,end_time'),
])
->select('id', 'employee_name', 'status_employee', 'status', 'company_id') // ← hapus FK columns
->whereNull('deleted_at')
->whereHas('store', fn($q) => $q->where('stores_tables.id', $storeId)) // ← filter via pivot
->orderBy('employee_name');


            if ($canManageAll) {
                $employeeQuery->whereIn('status', ['Active', 'Pending', 'On Leave']);
            } elseif ($canManageSPV) {
                $employeeQuery->where('status', 'Active');
            } elseif ($canView) {
                $employeeQuery->where('id', $myEmployee->id);
            } else {
                return abort(403, 'Unauthorized');
            }

            $employees = $employeeQuery->get();

            $shifts = Shifts::where('store_id', $storeId)
                ->orderBy('shift_name')
                ->get();


            $start   = Carbon::parse($startDate);
            $end     = Carbon::parse($endDate);
            $current = $start->copy();

            while ($current->lte($end)) {
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

        $isSupervisorOrManager = $canManageSPV && !$canManageAll;
        $isRosterOpen = $this->isSPVOnly() ? $this->checkRosterWindow() : true;
        $canViewOnly = $canView && !$canManageAll && !$canManageSPV;

        return view('pages.Roster.Roster', compact(
            'employees',
            'shifts',
            'stores',
            'dates',

            'startDate',
            'endDate',
            'storeId',
            'myStoreId',
            'isRosterOpen',
            'myStoreName',
            'isSupervisorOrManager',
            'canView',
            'canManageAll',
            'canManageSPV',
            'canViewOnly',
            'user'
        ));
    }
    // ─────────────────────────────────────────────────────────────
    //  STORE (klik cell)
    // ─────────────────────────────────────────────────────────────

    public function store(Request $request)

    {


        if ($this->isSPVOnly() && !$this->checkRosterWindow()) {
            return response()->json([
                'success' => false,
                'message' => 'Periode pengisian roster sedang ditutup.',
            ], 403);
        }
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
        if ($this->isSPVOnly() && !$this->checkRosterWindow()) {
            return response()->json([
                'success' => false,
                'message' => 'Periode pengisian roster sedang ditutup.',
            ], 403);
        }
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


        // Roster::where('employee_id', $request->employee_id)
        //     ->where('date', $request->date)
        //     ->delete();
        //  Sesudah - trigger Eloquent events
        $rosters = Roster::where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->get();
        Log::info('Rosters found: ' . $rosters->count());
        Log::info($rosters->pluck('id')->toArray());

        foreach ($rosters as $roster) {
            $roster->delete();
        }

        return response()->json(['success' => true]);
    }
    private function resolveRelevantPhTypes(?string $religion): array
    {
        return ($religion === 'Hindu')
            ? ['Hindu', 'All']
            : ['Non Hindu', 'All'];
    }


    /**
     * Tentukan apakah karyawan berhak mendapat Public Holiday
     * berdasarkan status_employee:
     *   - PKWT → dapat PH
     *   - OJT  → dapat PH
     *   - DW   → TIDAK dapat PH
     */
    private function isEligibleForPH(?string $statusEmployee): bool
    {
        return !in_array(strtoupper($statusEmployee ?? ''), ['DW']);
    }

    public function bulkAssign(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);
        if ($this->isSPVOnly() && !$this->checkRosterWindow()) {
            return response()->json([
                'success' => false,
                'message' => 'Periode pengisian roster sedang ditutup.',
            ], 403);
        }
        Log::info('=== BULK ASSIGN START ===', [
            'request' => $request->all()
        ]);

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


        $employees = Employee::select(
            'id',
            'employee_name',
            'status_employee',
            'religion'
        )
            ->whereIn('id', $request->employee_ids)
            ->get()
            ->keyBy('id');

        Log::info('Employees Loaded', [
            'count' => $employees->count(),
            'employees' => $employees->toArray()
        ]);

        $rejected = [];

        foreach ($employees as $emp) {

            $allowed = $this->allowedDayTypes($emp->status_employee);

            Log::info('Checking Allowed Day Type', [
                'employee' => $emp->employee_name,
                'status_employee' => $emp->status_employee,
                'requested_day_type' => $request->day_type,
                'allowed' => $allowed
            ]);

            if (!in_array($request->day_type, $allowed)) {

                $rejected[] = "{$emp->employee_name} (status: " . strtoupper($emp->status_employee ?? '-') . ")";
            }
        }

        if (!empty($rejected)) {

            Log::warning('Rejected Employees', [
                'rejected' => $rejected
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Karyawan berikut tidak dapat di-assign day type "' . $request->day_type . '": '
                    . implode(', ', $rejected) . '.',
            ], 422);
        }
        $publicHolidayMap = PublicHoliday::whereBetween('date', [$request->start_date, $request->end_date])
            ->get()
            ->groupBy(fn($ph) => Carbon::parse($ph->date)->toDateString());

        Log::info('Public Holiday Loaded', [
            'dates' => $publicHolidayMap->keys()
        ]);

        $toilApprovedMap = $this->getToilApprovedDatesMap(
            $request->employee_ids,
            $request->start_date,
            $request->end_date
        );


        Log::info('TOIL Approved Map', [
            'map' => $toilApprovedMap
        ]);

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


        Log::info('Generated Dates', [
            'dates' => collect($dates)->map(fn($d) => $d->toDateString())
        ]);

        $count        = 0;
        $skippedCount = 0;

        foreach ($request->employee_ids as $empId) {

            $emp = $employees[$empId];

            Log::info('Processing Employee', [
                'employee_id' => $empId,
                'employee_name' => $emp->employee_name,
                'religion' => $emp->religion,
                'status_employee' => $emp->status_employee
            ]);

            $relevantPhTypes = $this->resolveRelevantPhTypes($emp->religion);

            $eligibleForPH = $this->isEligibleForPH($emp->status_employee);

            Log::info('PH Eligibility', [
                'employee' => $emp->employee_name,
                'relevant_ph_types' => $relevantPhTypes,
                'eligible_for_ph' => $eligibleForPH
            ]);

            foreach ($dates as $date) {

                $dateStr = $date->toDateString();


                //  Reset tiap iterasi agar tidak carry over ke tanggal berikutnya
                $resolvedDayType = $request->day_type;
                $resolvedShiftId = $request->day_type === 'Work' ? $request->shift_id : null;
                $resolvedNotes   = null;

                if ($this->hasToilApproved($toilApprovedMap, $empId, $dateStr)) {
                    Roster::updateOrCreate(
                        ['employee_id' => $empId, 'date' => $dateStr],
                        ['shift_id' => null, 'day_type' => 'Off', 'notes' => null]
                    );
                    $phCount++;
                    continue;
                }

                if ($eligibleForPH && isset($publicHolidayMap[$dateStr])) {
                    $matchingPH = $publicHolidayMap[$dateStr]->first(
                        fn($ph) => in_array($ph->type, $relevantPhTypes)
                    );

                    if ($matchingPH) {
                        Log::info('Public Holiday Match', [
                            'employee' => $emp->employee_name,
                            'date'     => $dateStr,
                            'type'     => $matchingPH->type,
                            'remark'   => $matchingPH->remark,
                        ]);

                        $resolvedDayType = 'Public Holiday';
                        $resolvedShiftId = null;
                        $resolvedNotes   = $matchingPH->remark;  // ← PH remark → Roster notes
                    }
                }

                if ($date->isSaturday()) {
                    if ($request->saturday_shift && $request->saturday_shift_id) {
                        $finalDayType = ($resolvedDayType === 'Public Holiday') ? 'Public Holiday' : 'Work';
                        $finalShiftId = ($finalDayType === 'Work') ? $request->saturday_shift_id : null;

                        Roster::updateOrCreate(
                            ['employee_id' => $empId, 'date' => $dateStr],

                            ['shift_id' => $finalShiftId, 'day_type' => $finalDayType, 'notes' => $resolvedNotes]  // 

                        );
                        $count++;
                    } elseif ($request->skip_weekend) {
                        continue;
                    } else {
                        Roster::updateOrCreate(
                            ['employee_id' => $empId, 'date' => $dateStr],
                            ['shift_id' => $resolvedShiftId, 'day_type' => $resolvedDayType, 'notes' => $resolvedNotes]  // 
                        );
                        $count++;
                    }
                    continue;
                }

                Roster::updateOrCreate(
                    ['employee_id' => $empId, 'date' => $dateStr],
                    ['shift_id' => $resolvedShiftId, 'day_type' => $resolvedDayType, 'notes' => $resolvedNotes]  // 
                );
                $count++;
            }
        }


        Log::info('=== BULK ASSIGN FINISHED ===', [
            'success_count' => $count,
            'skipped_count' => $skippedCount
        ]);

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
    //  BULK DELETE
    //  Update: protect tanggal TOIL Approved (versi AMAN, loop per karyawan)
    // ─────────────────────────────────────────────────────────────

    public function bulkDelete(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);
        if ($this->isSPVOnly() && !$this->checkRosterWindow()) {
            return response()->json([
                'success' => false,
                'message' => 'Periode pengisian roster sedang ditutup.',
            ], 403);
        }

        $request->validate([
            'employee_ids'   => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees_tables,id',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
        ]);

        $toilApprovedMap = $this->getToilApprovedDatesMap(
            $request->employee_ids,
            $request->start_date,
            $request->end_date
        );

        $count          = 0;
        $protectedCount = 0;

        foreach ($request->employee_ids as $empId) {
            $protectedDates = $toilApprovedMap[$empId] ?? [];

            $query = Roster::where('employee_id', $empId)
                ->whereBetween('date', [$request->start_date, $request->end_date]);

            if (!empty($protectedDates)) {
                $query->whereNotIn('date', $protectedDates);
                $protectedCount += count($protectedDates);
            }

            //  Chunk agar tidak timeout kalau data banyak
            $query->chunk(100, function ($rosters) use (&$count) {
                foreach ($rosters as $roster) {
                    $roster->delete(); // trigger Eloquent events → activity log tercatat
                    $count++;
                }
            });
        }

        $message = "Berhasil menghapus {$count} jadwal.";
        if ($protectedCount > 0) {
            $message .= " {$protectedCount} jadwal di-protect karena ada TOIL Leave yang sudah Approved.";
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
        ini_set('memory_limit', '512M');
        set_time_limit(300);
        if ($this->isSPVOnly() && !$this->checkRosterWindow()) {
            return response()->json([
                'success' => false,
                'message' => 'Periode pengisian roster sedang ditutup.',
            ], 403);
        }
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

    public function history(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'store_id'   => 'nullable',
            'search'     => 'nullable|string',
        ]);


        $user         = auth()->user();
        /** @var \App\Models\User|null $user */
        $canManageAll = $user->hasPermissionTo('ManageRoster');
        $canManageSPV = $user->hasPermissionTo('ManageRosterSPVManager');
        $canViewOwn   = $user->hasPermissionTo('ViewRoster'); // ← tambah
        $myEmployee   = optional($user->employee);

        // Tidak punya permission apapun → 403
        if (!$canManageAll && !$canManageSPV && !$canViewOwn) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }
         $myStoreId = $myEmployee->id
        ? $myEmployee->store()->wherePivot('is_primary', true)->first()?->id
        : null;


        $query = Roster::with([
        // ← Fix: hapus FK dari select, pakai pivot untuk relasi
        'employee:id,employee_name,status_employee',
        'employee.department' => fn($q) => $q->wherePivot('is_primary', true),
        'employee.position'   => fn($q) => $q->wherePivot('is_primary', true),
        'shift:id,shift_name,start_time,end_time',
    ])
    ->whereBetween('date', [$request->start_date, $request->end_date])
    ->whereHas('employee', function ($q) use ($request, $myEmployee, $myStoreId, $canManageAll, $canManageSPV, $canViewOwn) {
        $q->whereNull('deleted_at');

        if ($request->search) {
            $q->where('employee_name', 'like', '%' . $request->search . '%');
        }

        if ($canManageAll) {
            if ($request->store_id) {
                // ← Fix: pakai whereHas pivot
                $q->whereHas('store', fn($q2) =>
                    $q2->where('stores_tables.id', $request->store_id)
                );
            }
        } elseif ($canManageSPV) {
            // ← Fix: pakai whereHas pivot
            $q->whereHas('store', fn($q2) =>
                $q2->where('stores_tables.id', $myStoreId)
            );
        } elseif ($canViewOwn) {
            $q->where('id', $myEmployee->id);
        }
    })
    ->orderBy('date')
    ->orderBy('employee_id');

    $rosters = $query->get();
    $grouped = $rosters->groupBy('employee_id');

    return response()->json([
        'success' => true,
        'data'    => $grouped->map(function ($items) {
            $emp = $items->first()->employee;
            return [
                'employee_name'   => $emp->employee_name,
                'department'      => $emp->department->first()?->department_name ?? '-',
                'position'        => $emp->position->first()?->name ?? '-',
                'status_employee' => $emp->status_employee ?? '-',
                'rosters'         => $items->map(fn($r) => [
                    'date'       => Carbon::parse($r->date)->toDateString(),
                    'day_type'   => $r->day_type,
                    'shift_name' => $r->shift?->shift_name ?? '-',
                    'start_time' => $r->shift ? substr($r->shift->start_time, 0, 5) : '',
                    'end_time'   => $r->shift ? substr($r->shift->end_time, 0, 5) : '',
                    'notes'      => $r->notes ?? '-',
                ])->values(),
            ];
        })->values(),
    ]);
}
//     public function history(Request $request)
//     {
//         $request->validate([
//             'start_date' => 'required|date',
//             'end_date'   => 'required|date|after_or_equal:start_date',
//             'store_id'   => 'nullable',
//             'search'     => 'nullable|string',
//         ]);


//         $user         = auth()->user();
//         /** @var \App\Models\User|null $user */
//         $canManageAll = $user->hasPermissionTo('ManageRoster');
//         $canManageSPV = $user->hasPermissionTo('ManageRosterSPVManager');
//         $canViewOwn   = $user->hasPermissionTo('ViewRoster'); // ← tambah
//         $myEmployee   = optional($user->employee);

//         // Tidak punya permission apapun → 403
//         if (!$canManageAll && !$canManageSPV && !$canViewOwn) {
//             return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
//         }

//         $query = Roster::with([
//             'employee:id,employee_name,department_id,position_id,store_id,status_employee',
//             'employee.department:id,department_name',
//             'employee.position:id,name',
//             'shift:id,shift_name,start_time,end_time',
//         ])
//             ->whereBetween('date', [$request->start_date, $request->end_date])
//             ->whereHas('employee', function ($q) use ($request, $myEmployee, $canManageAll, $canManageSPV, $canViewOwn) {
//                 $q->whereNull('deleted_at');

//                 if ($request->search) {
//                     $q->where('employee_name', 'like', '%' . $request->search . '%');
//                 }

//                 if ($canManageAll) {
//                     // Admin: semua store, filter kalau dipilih
//                     if ($request->store_id) {
//                         $q->where('store_id', $request->store_id);
//                     }
//                 } elseif ($canManageSPV) {
//                     // SPV: terkunci store sendiri
//                     $q->where('store_id', $myEmployee->store_id);
//                 } elseif ($canViewOwn) {
//                     // Employee: hanya data diri sendiri
//                     $q->where('id', $myEmployee->id);
//                 }
//             })
//             ->orderBy('date')
//             ->orderBy('employee_id');

//         $rosters = $query->get();
//         $grouped = $rosters->groupBy('employee_id');

//         return response()->json([
//             'success' => true,
//             'data'    => $grouped->map(function ($items) {
//                 $emp = $items->first()->employee;
//                 return [
//                     'employee_name'   => $emp->employee_name,
//                     // 'department'      => $emp->department->department_name ?? '-',
//                     // 'position'        => $emp->position->name ?? '-',
//                     'department' => $emp->department->first()?->department_name ?? '-',
// 'position'   => $emp->position->first()?->name ?? '-',
//                     'status_employee' => $emp->status_employee ?? '-',
//                     'rosters'         => $items->map(fn($r) => [
//                         'date'       => Carbon::parse($r->date)->toDateString(),
//                         'day_type'   => $r->day_type,
//                         'shift_name' => $r->shift?->shift_name ?? '-',
//                         'start_time' => $r->shift ? substr($r->shift->start_time, 0, 5) : '',
//                         'end_time'   => $r->shift ? substr($r->shift->end_time, 0, 5) : '',
//                         'notes'      => $r->notes ?? '-',
//                     ])->values(),
//                 ];
//             })->values(),
//         ]);
//     }

    public function historyExport(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'store_id'   => 'nullable',
            'search'     => 'nullable|string',
        ]);

        $user         = auth()->user();
        /** @var \App\Models\User|null $user */
        $canManageAll = $user->hasPermissionTo('ManageRoster');
        $canManageSPV = $user->hasPermissionTo('ManageRosterSPVManager');
        $canViewOwn   = $user->hasPermissionTo('ViewRoster');
        $myEmployee   = optional($user->employee);
        // $myStoreId    = $myEmployee->store_id;
        $myStore   = $myEmployee->store()->wherePivot('is_primary', true)->first();
$myStoreId = $myStore?->id;

        // Tidak punya permission apapun → 403
        if (!$canManageAll && !$canManageSPV && !$canViewOwn) {
            abort(403, 'Unauthorized.');
        }

        // ViewRoster: paksa export hanya data diri sendiri
        $employeeIdFilter = null;
        if ($canViewOwn && !$canManageAll && !$canManageSPV) {
            $employeeIdFilter = $myEmployee->id;
        }

        // Nama file
        $filename = $employeeIdFilter
            ? 'roster-' . str($myEmployee->employee_name ?? 'employee')->slug() . '-' . $request->start_date . '-to-' . $request->end_date . '.xlsx'
            : 'roster-history-' . $request->start_date . '-to-' . $request->end_date . '.xlsx';

        // Store name untuk kop
        // $storeName = $request->store_id
        //     ? Stores::find($request->store_id)?->name
        //     : ($employeeIdFilter ? optional($myEmployee->store)->name : 'All Locations');
        $storeName = $request->store_id
    ? Stores::find($request->store_id)?->name
    : ($employeeIdFilter ? $myStore?->name : 'All Locations');

        return Excel::download(
            new RosterHistoryExport(
                startDate: $request->start_date,
                endDate: $request->end_date,
                storeId: $request->store_id,
                search: $request->search,
                canManageAll: $canManageAll,
                myStoreId: $canManageSPV && !$canManageAll ? $myStoreId : null,
                storeName: $storeName,
                employeeIdFilter: $employeeIdFilter,
            ),
            $filename
        );
    }
    public function getActivities(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user         = auth()->user();
        $canManageAll = $user->hasPermissionTo('ManageRoster');
        $canManageSPV = $user->hasPermissionTo('ManageRosterSPVManager');

        if (!$canManageAll && !$canManageSPV) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = Activity::with('causer.employee')
            ->where('subject_type', Roster::class)
            ->orderBy('created_at', 'desc');

        // SPV hanya lihat activity dari causer yang store_id-nya sama
        // if (!$canManageAll && $canManageSPV) {
        //     $storeId = $user->employee->store_id;

        //     $query->whereHas('causer.employee', function ($q) use ($storeId) {
        //         $q->where('store_id', $storeId);
        //     });
        // }
        if (!$canManageAll && $canManageSPV) {
    $myStoreId = $user->employee->store()->wherePivot('is_primary', true)->first()?->id;
    $query->whereHas('causer.employee', function ($q) use ($myStoreId) {
        $q->whereHas('store', fn($q2) =>
            $q2->where('stores_tables.id', $myStoreId)
        );
    });
}

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('causer_name', function ($row) {
                if (!$row->causer) return 'System';
                return optional($row->causer->employee)->employee_name ?? $row->causer->name ?? 'System';
            })
            ->addColumn('properties', function ($row) {
                $properties = $row->properties;

                if ($row->event === 'created') {
                    return collect($properties->get('attributes', []))
                        ->map(fn($val, $key) => "<b>{$key}</b>: {$val}")
                        ->implode('<br>');
                }

                if ($row->event === 'updated') {
                    $old        = $properties->get('old', []);
                    $attributes = $properties->get('attributes', []);

                    return collect($attributes)
                        ->map(fn($val, $key) => "<b>{$key}</b>: " . ($old[$key] ?? '-') . " → {$val}")
                        ->implode('<br>');
                }

                if ($row->event === 'deleted') {
                    return collect($properties->get('attributes', []))
                        ->map(fn($val, $key) => "<b>{$key}</b>: {$val}")
                        ->implode('<br>');
                }

                return '-';
            })
            ->addColumn('event_badge', fn($row) => match ($row->event) {
                'created' => '<span class="badge bg-success">Created</span>',
                'updated' => '<span class="badge bg-warning text-dark">Updated</span>',
                'deleted' => '<span class="badge bg-danger">Deleted</span>',
                default   => '<span class="badge bg-secondary">' . $row->event . '</span>',
            })
            ->addColumn('created_at_formatted', fn($row) => $row->created_at->format('d M Y, H:i'))
            ->rawColumns(['properties', 'event_badge'])
            ->make(true);
    }
}
