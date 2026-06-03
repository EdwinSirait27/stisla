<?php

namespace App\Http\Controllers;

use App\Models\Employee;
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
    // public function index(Request $request)
    // {
    //     $stores = Stores::select('id', 'name')
    //         ->whereNotNull('name')
    //         ->orderBy('name')
    //         ->get();

    //     $today = now();

    //     $defaultStartDate = $today->copy()->subMonth()->day(26)->toDateString();
    //     $defaultEndDate   = $today->copy()->day(25)->toDateString();

    //     $startDate = $request->start_date ?? $defaultStartDate;
    //     $endDate   = $request->end_date   ?? $defaultEndDate;
    //     $storeId   = $request->store_id;

    //     $employees = collect();
    //     $shifts    = collect();
    //     $dates     = [];

    //     if ($storeId) {
    //         $employees = Employee::with([
    //             'position:id,name',
    //             'store:id,name',
    //             'department:id,department_name',
    //             'rosters' => fn($q) => $q
    //                 ->whereBetween('date', [$startDate, $endDate])
    //                 ->with('shift:id,shift_name,start_time,end_time'),
    //         ])
    //             ->select('id', 'employee_name', 'store_id', 'status_employee', 'status','company_id','department_id','position_id') 
    //             ->whereNull('deleted_at')
    //             ->where('store_id', $storeId)
    //             ->whereIn('status', ['Active', 'Pending', 'On Leave'])
    //             ->orderBy('employee_name')
    //             ->get();

    //         $shifts = Shifts::where('store_id', $storeId)
    //             ->orderBy('shift_name')
    //             ->get();

    //         $start   = Carbon::parse($startDate);
    //         $end     = Carbon::parse($endDate);
    //         $current = $start->copy();

    //         while ($current->lte($end)) {
    //             $dates[] = $current->copy();
    //             $current->addDay();
    //         }
    //     }

    //     return view('pages.Roster.Roster', compact(
    //         'employees',
    //         'shifts',
    //         'stores',
    //         'dates',
    //         'startDate',
    //         'endDate',
    //         'storeId'
    //     ));
    // }
    //     public function index(Request $request)
    // {
    //     // dd(auth()->user()->toArray());

    //     $user  = auth()->user();
    //     $role  = $user->role;

    // $isSupervisorOrManager = $user->hasAnyRole(['Supervisor', 'Manager']);

    //    $myStoreId = optional($user->employee)->store_id;

    //     // Stores: Supervisor/Manager hanya store sendiri
    //     $stores = $isSupervisorOrManager
    //         ? Stores::select('id', 'name')->where('id', $myStoreId)->get()
    //         : Stores::select('id', 'name')->whereNotNull('name')->orderBy('name')->get();

    //     $today = now();

    //     $defaultStartDate = $today->copy()->subMonth()->day(26)->toDateString();
    //     $defaultEndDate   = $today->copy()->day(25)->toDateString();

    //     $startDate = $request->start_date ?? $defaultStartDate;
    //     $endDate   = $request->end_date   ?? $defaultEndDate;

    //     // storeId: Supervisor/Manager dikunci, Admin bebas pilih
    //     $storeId = $isSupervisorOrManager ? $myStoreId : $request->store_id;

    //     $employees = collect();
    //     $shifts    = collect();
    //     $dates     = [];

    //     if ($storeId) {
    //         $employeeQuery = Employee::with([
    //             'position:id,name',
    //             'store:id,name',
    //             'department:id,department_name',
    //             'rosters' => fn($q) => $q
    //                 ->whereBetween('date', [$startDate, $endDate])
    //                 ->with('shift:id,shift_name,start_time,end_time'),
    //         ])
    //         ->select('id', 'employee_name', 'store_id', 'status_employee', 'status', 'company_id', 'department_id', 'position_id')
    //         ->whereNull('deleted_at')
    //         ->where('store_id', $storeId)
    //         ->orderBy('employee_name');

    //         // Supervisor/Manager: Active saja | Admin: semua status
    //         $isSupervisorOrManager
    //             ? $employeeQuery->where('status', 'Active')
    //             : $employeeQuery->whereIn('status', ['Active', 'Pending', 'On Leave']);

    //         $employees = $employeeQuery->get();

    //         $shifts = Shifts::where('store_id', $storeId)
    //             ->orderBy('shift_name')
    //             ->get();

    //         $start   = Carbon::parse($startDate);
    //         $end     = Carbon::parse($endDate);
    //         $current = $start->copy();

    //         while ($current->lte($end)) {
    //             $dates[] = $current->copy();
    //             $current->addDay();
    //         }
    //     }

    //     return view('pages.Roster.Roster', compact(
    //         'employees',
    //         'shifts',
    //         'stores',
    //         'dates',
    //         'startDate',
    //         'endDate',
    //         'storeId',
    //         'isSupervisorOrManager'
    //     ));
    // }
    // public function index(Request $request)
    // {
    //     $user  = auth()->user();
    //     $isSupervisorOrManager = $user->hasAnyRole(['Supervisor', 'Manager']);
    //     // Ambil department_id dari employee user login
    //     $myEmployee     = optional($user->employee);
    //     $myStoreId      = $myEmployee->store_id;
    //     $myDepartmentId = $myEmployee->department_id;
    //     // Stores: tetap bisa pilih semua store (tidak dikunci per department)
    //     $stores = Stores::select('id', 'name')
    //         ->whereNotNull('name')
    //         ->orderBy('name')
    //         ->get();
    //     $today = now();
    //     $defaultStartDate = $today->copy()->subMonth()->day(26)->toDateString();
    //     $defaultEndDate   = $today->copy()->day(25)->toDateString();

    //     $startDate = $request->start_date ?? $defaultStartDate;
    //     $endDate   = $request->end_date   ?? $defaultEndDate;
    //     $storeId   = $request->store_id;

    //     $employees = collect();
    //     $shifts    = collect();
    //     $dates     = [];

    //     if ($storeId) {
    //         $employeeQuery = Employee::with([
    //             'position:id,name',
    //             'store:id,name',
    //             'department:id,department_name',
    //             'rosters' => fn($q) => $q
    //                 ->whereBetween('date', [$startDate, $endDate])
    //                 ->with('shift:id,shift_name,start_time,end_time'),
    //         ])
    //         ->select('id', 'employee_name', 'store_id', 'status_employee', 'status', 'company_id', 'department_id', 'position_id')
    //         ->whereNull('deleted_at')
    //         ->where('store_id', $storeId)
    //         ->orderBy('employee_name');

    //         if ($isSupervisorOrManager) {
    //             // Hanya tampilkan employee di department yang sama, status Active
    //             $employeeQuery
    //                 ->where('department_id', $myDepartmentId)
    //                 ->where('status', 'Active');
    //         } else {
    //             // Admin: semua department, semua status
    //             $employeeQuery->whereIn('status', ['Active', 'Pending', 'On Leave']);
    //         }

    //         $employees = $employeeQuery->get();

    //         $shifts = Shifts::where('store_id', $storeId)
    //             ->orderBy('shift_name')
    //             ->get();

    //         $start   = Carbon::parse($startDate);
    //         $end     = Carbon::parse($endDate);
    //         $current = $start->copy();

    //         while ($current->lte($end)) {
    //             $dates[] = $current->copy();
    //             $current->addDay();
    //         }
    //     }

    //     return view('pages.Roster.Roster', compact(
    //         'employees',
    //         'shifts',
    //         'stores',
    //         'dates',
    //         'startDate',
    //         'endDate',
    //         'storeId',
    //         'isSupervisorOrManager',
    //         'user'
    //     ));
    // }
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
    // if ($this->isSPVOnly() && !$this->checkRosterWindow()) {
    //     return response()->json([
    //         'success' => false,
    //         'message' => 'Periode pengisian roster sedang ditutup.',
    //     ], 403);
    // }
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        // Permission-based check
        $canManageAll = $user->hasPermissionTo('ManageRoster');           // Admin/HR: semua dept
        $canManageSPV = $user->hasPermissionTo('ManageRosterSPVManager'); // SPV/Manager: dept sendiri

        $myEmployee     = optional($user->employee);
        $myStoreId      = $myEmployee->store_id;
        $myStoreName = optional($myEmployee->store)->name; // pastikan relasi store ada di Employee model

        $myDepartmentId = $myEmployee->department_id;

        $stores = Stores::select('id', 'name')
            ->whereNotNull('name')
            ->orderBy('name')
            ->get();

        $today = now();
        $defaultStartDate = $today->copy()->subMonth()->day(26)->toDateString();
        $defaultEndDate   = $today->copy()->day(25)->toDateString();

        $startDate = $request->start_date ?? $defaultStartDate;
        $endDate   = $request->end_date   ?? $defaultEndDate;
        $storeId   = $request->store_id;

        $employees = collect();
        $shifts    = collect();
        $dates     = [];

        if ($storeId) {
            $employeeQuery = Employee::with([
                'position:id,name',
                'store:id,name',
                'department:id,department_name',
                'rosters' => fn($q) => $q
                    ->whereBetween('date', [$startDate, $endDate])
                    ->with('shift:id,shift_name,start_time,end_time'),
            ])
                ->select('id', 'employee_name', 'store_id', 'status_employee', 'status', 'company_id', 'department_id', 'position_id')
                ->whereNull('deleted_at')
                ->where('store_id', $storeId)
                ->orderBy('employee_name');
            if ($canManageAll) {
                // Admin/HR: semua department, semua status
                $employeeQuery->whereIn('status', ['Active', 'Pending', 'On Leave']);
            } elseif ($canManageSPV) {
                // SPV/Manager: semua department berdasarkan store_id, hanya status Active
                $employeeQuery->where('status', 'Active'); // ✅ hapus filter department_id

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
        }

        // Tetap kirim flag ke view untuk kondisional UI
        $isSupervisorOrManager = $canManageSPV && !$canManageAll;
        $isRosterOpen = $this->isSPVOnly() ? $this->checkRosterWindow() : true;


        return view('pages.Roster.Roster', compact(
            'employees',
            'shifts',
            'stores',
            'dates',
            'startDate',
            'endDate',
            'storeId',
            'myStoreId',      // tambah
            'isRosterOpen',      // tambah
            'myStoreName',    // tambah
            'isSupervisorOrManager',
            'user'
        ));
    }
    // ─────────────────────────────────────────────────────────────
    //  STORE (klik cell)
    //  Update: tolak override kalau ada TOIL Approved
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
    private function resolveRelevantPhTypes(?string $religion): array
    {
        return ($religion === 'Hindu')
            ? ['Hindu', 'All']
            : ['Non Hindu', 'All'];  // pastikan value ini PERSIS sama dengan di DB
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

                // ✅ Reset tiap iterasi agar tidak carry over ke tanggal berikutnya
                $resolvedDayType = $request->day_type;
                $resolvedShiftId = $request->day_type === 'Work' ? $request->shift_id : null;
                $resolvedNotes   = null;  // ← reset di sini

                if ($this->hasToilApproved($toilApprovedMap, $empId, $dateStr)) {
                    Roster::updateOrCreate(
                        ['employee_id' => $empId, 'date' => $dateStr],
                        ['shift_id' => null, 'day_type' => 'Off', 'notes' => null]
                    );
                    $skippedCount++;
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
                            ['shift_id' => $finalShiftId, 'day_type' => $finalDayType, 'notes' => $resolvedNotes]  // ✅
                        );
                        $count++;
                    } elseif ($request->skip_weekend) {
                        continue;
                    } else {
                        Roster::updateOrCreate(
                            ['employee_id' => $empId, 'date' => $dateStr],
                            ['shift_id' => $resolvedShiftId, 'day_type' => $resolvedDayType, 'notes' => $resolvedNotes]  // ✅
                        );
                        $count++;
                    }
                    continue;
                }

                // Hari biasa
                Roster::updateOrCreate(
                    ['employee_id' => $empId, 'date' => $dateStr],
                    ['shift_id' => $resolvedShiftId, 'day_type' => $resolvedDayType, 'notes' => $resolvedNotes]  // ✅
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
    // ─────────────────────────────────────────────────────────────
    //  COPY ROSTER
    //  Update: force Off untuk tanggal TOIL Approved
    // ─────────────────────────────────────────────────────────────

    public function copyRoster(Request $request)
    {
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
    //     public function history(Request $request)
    // {
    //     $request->validate([
    //         'start_date' => 'required|date',
    //         'end_date'   => 'required|date|after_or_equal:start_date',
    //         'store_id'   => 'required',
    //         'search'     => 'nullable|string',
    //     ]);

    //     $user         = auth()->user();
    //     $canManageAll = $user->hasPermissionTo('ManageRoster');
    //     $canManageSPV = $user->hasPermissionTo('ManageRosterSPVManager');
    //     $myEmployee   = optional($user->employee);

    //     $query = Roster::with([
    //             'employee:id,employee_name,department_id,position_id,store_id,status_employee',
    //             'employee.department:id,department_name',
    //             'employee.position:id,name',
    //             'shift:id,shift_name,start_time,end_time',
    //         ])
    //         ->whereBetween('date', [$request->start_date, $request->end_date])
    //         ->whereHas('employee', function ($q) use ($request) {
    //             $q->where('store_id', $request->store_id)
    //               ->whereNull('deleted_at');

    //             if ($request->search) {
    //                 $q->where('employee_name', 'like', '%' . $request->search . '%');
    //             }
    //         })
    //         ->orderBy('date')
    //         ->orderBy('employee_id');

    //     // Filter permission
    //     if ($canManageSPV && !$canManageAll) {
    //         $query->whereHas('employee', function ($q) use ($myEmployee) {
    //             $q->where('store_id', $myEmployee->store_id);
    //         });
    //     }

    //     $rosters = $query->get();

    //     // Group by employee
    //     $grouped = $rosters->groupBy('employee_id');

    //     return response()->json([
    //         'success' => true,
    //         'data'    => $grouped->map(function ($items) {
    //             $emp = $items->first()->employee;
    //             return [
    //                 'employee_name'   => $emp->employee_name,
    //                 'department'      => $emp->department->department_name ?? '-',
    //                 'position'        => $emp->position->name ?? '-',
    //                 'status_employee' => $emp->status_employee ?? '-',
    //                 'rosters'         => $items->map(fn($r) => [
    //                     'date' => Carbon::parse($r->date)->translatedFormat('d F Y'),
    //                     'day_type'   => $r->day_type,
    //                     'shift_name' => $r->shift?->shift_name ?? '-',
    //                     'start_time' => $r->shift ? substr($r->shift->start_time, 0, 5) : '',
    //                     'end_time'   => $r->shift ? substr($r->shift->end_time, 0, 5) : '',
    //                     'notes'      => $r->notes ?? '',
    //                 ])->values(),
    //             ];
    //         })->values(),
    //     ]);
    // }
    public function history(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'store_id'   => 'nullable', // ← ubah dari required ke nullable
            'search'     => 'nullable|string',
        ]);

        $user         = auth()->user();
        /** @var \App\Models\User|null $user */
        $canManageAll = $user->hasPermissionTo('ManageRoster');
        $canManageSPV = $user->hasPermissionTo('ManageRosterSPVManager');
        $myEmployee   = optional($user->employee);

        $query = Roster::with([
            'employee:id,employee_name,department_id,position_id,store_id,status_employee',
            'employee.department:id,department_name',
            'employee.position:id,name',
            'shift:id,shift_name,start_time,end_time',
        ])
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->whereHas('employee', function ($q) use ($request, $myEmployee, $canManageAll, $canManageSPV) {
                $q->whereNull('deleted_at');

                if ($request->search) {
                    $q->where('employee_name', 'like', '%' . $request->search . '%');
                }

                if ($canManageAll) {
                    // Admin: filter store kalau dipilih, kalau kosong = semua store
                    if ($request->store_id) {
                        $q->where('store_id', $request->store_id);
                    }
                } elseif ($canManageSPV) {
                    // SPV: selalu terkunci store sendiri
                    $q->where('store_id', $myEmployee->store_id);
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
                    'department'      => $emp->department->department_name ?? '-',
                    'position'        => $emp->position->name ?? '-',
                    'status_employee' => $emp->status_employee ?? '-',
                    'rosters'         => $items->map(fn($r) => [
                        'date'       => Carbon::parse($r->date)->toDateString(), // ← kembalikan ke Y-m-d agar JS bisa proses
                        'day_type'   => $r->day_type,
                        'shift_name' => $r->shift?->shift_name ?? '-',
                        'start_time' => $r->shift ? substr($r->shift->start_time, 0, 5) : '',
                        'end_time'   => $r->shift ? substr($r->shift->end_time, 0, 5) : '',
                        'notes'      => $r->notes ?? '',
                    ])->values(),
                ];
            })->values(),
        ]);
    }
    public function historyExport(Request $request)
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
        $myStoreId    = optional($user->employee)->store_id;

        $filename = 'roster-history-' . $request->start_date . '-to-' . $request->end_date . '.xlsx';
        $storeName = $request->store_id
            ? Stores::find($request->store_id)?->name
            : 'All Locations';
        return Excel::download(
            new RosterHistoryExport(
                startDate: $request->start_date,
                endDate: $request->end_date,
                storeId: $request->store_id,
                search: $request->search,
                canManageAll: $canManageAll,
                myStoreId: $canManageSPV && !$canManageAll ? $myStoreId : null,
                storeName: $storeName,
            ),
            $filename
        );
    }

    public function getActivities(Request $request)
    {
        $query = Activity::with('causer.employee')
            ->where('subject_type', Roster::class)
            ->orderBy('created_at', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('causer_name', fn($row) => optional($row->causer->employee)->employee_name ?? 'System')
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
                    return collect($properties->get('old', []))
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
