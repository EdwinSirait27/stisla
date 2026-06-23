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
use App\Models\RosterPHCarryover;
use Yajra\DataTables\DataTables;
use Spatie\Activitylog\Models\Activity;
use App\Models\ToilLeaveRequests;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RosterTemplateExport;
use App\Imports\RosterImport;
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
            return ['Work', 'Off', 'TOIL Off'];
        }

        if (str_contains($status, 'JOB TRAINING')) {
            return ['Work', 'Off', 'Public Holiday', 'Sick', 'TOIL Off'];
        }

        return ['Work', 'Off', 'Public Holiday', 'Leave', 'Cuti Melahirkan', 'Sick', 'TOIL Off'];
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
                $badgeClass = 'r-badge r-toiloff';
                $badgeName  = 'TOIL Off';
                $cellType   = 'toiloff';
            } elseif ($roster->day_type === 'Off') {
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
            } elseif ($roster->day_type === 'Sick') {
                $badgeClass = 'r-badge r-sick';
                $badgeName  = 'Sick';
                $cellType   = 'sick';
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
            'sick_attachment' => $roster?->sick_attachment ?? '',
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

        $myStores  = collect();
        $myStoreId = null;
        $myStoreName = null;

        $canManageAll = $user->hasPermissionTo('ManageRoster');
        $canManageSPV = $user->hasPermissionTo('ManageRosterSPVManager');
        $canView      = $user->hasPermissionTo('ViewRoster');


        if (!$canManageAll && !$canManageSPV && !$canView) {
            return abort(403, 'Unauthorized');
        }


        $myEmployee  = optional($user->employee);
 
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

            $myStoreIds = $this->userStoreIds($user);

            // Guard store_id
            if ($request->store_id && !in_array($request->store_id, $myStoreIds)) {
                return redirect()->route('roster.index', [
                    'store_id'   => $myStoreIds[0] ?? $myStoreId,
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
         
$employeeQuery = Employee::with([
    'store'      => fn($q) => $q->wherePivot('is_primary', true),
    'position'   => fn($q) => $q->wherePivot('is_primary', true),
    'department' => fn($q) => $q->wherePivot('is_primary', true),
    'rosters'    => fn($q) => $q
        ->whereBetween('date', [$startDate, $endDate])
        ->with('shift:id,shift_name,start_time,end_time'),
])
->select('id', 'employee_name', 'status_employee', 'status', 'company_id')
->whereNull('deleted_at')
->whereHas('store', fn($q) => $q->where('stores_tables.id', $storeId))
->orderBy('employee_name');

if ($canManageAll) {
    $employeeQuery->whereIn('status', ['Active', 'Pending', 'On Leave']);

} elseif ($canManageSPV) {
    $employeeQuery
        ->whereIn('status', ['Active', 'Pending', 'On Leave'])
        ->where('company_id', $myEmployee->company_id) // ← filter company
        ->whereHas('department', fn($q) =>              // ← filter department
            $q->where('departments_tables.id', $myDepartmentId)
        );

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
        $currentStoreName = $storeId
            ? optional($stores->firstWhere('id', $storeId))->name ?? ''
            : '';

        // Daftar store untuk dropdown SPV (hanya store miliknya)
        $myStores = collect();
        if (!$canManageAll && $canManageSPV) {
            $myStoreIds = $this->userStoreIds($user);
            $myStores = Stores::select('id', 'name')
                ->whereIn('id', $myStoreIds)
                ->orderBy('name')
                ->get();
        }

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
            'currentStoreName',
            'myStores',
            'user'
        ));
    }
    public function sickAttachmentUrl(Request $request)
{
    $request->validate(['path' => 'required|string']);

    $path = $request->input('path');

    // Pastikan path tidak keluar dari folder yang diizinkan
    if (!str_starts_with($path, 'employee-sickness/')) {
        return response()->json(['error' => 'Invalid path'], 403);
    }

    if (!Storage::disk('s3')->exists($path)) {
        return response()->json(['error' => 'File not found'], 404);
    }

    // Generate temporary signed URL (berlaku 5 menit)
    $url = Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(5));

    return response()->json(['url' => $url]);
}
    // ─────────────────────────────────────────────────────────────
    //  STORE (klik cell)
    // ─────────────────────────────────────────────────────────────

    // public function store(Request $request)

    // {

    //     if ($this->isSPVOnly() && !$this->checkRosterWindow()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Periode pengisian roster sedang ditutup.',
    //         ], 403);
    //     }

    //     if ($request->shift_id === '') {
    //         $request->merge(['shift_id' => null]);
    //     }
    //     $request->validate([
    //         'employee_id' => 'required|exists:employees_tables,id',
    //         'shift_id'    => 'nullable|exists:shifts_tables,id',
    //         'date'        => 'required|date',
    //         'day_type'    => 'required|in:Work,Off,Public Holiday,Leave,Cuti Melahirkan,Sick,TOIL Off',
    //         'ph_carryover_id' => 'nullable|exists:roster_ph_carryovers,id',
    //     ]);

    //     $employee = Employee::with(['store' => fn($q) => $q->wherePivot('is_primary', true)])
    //         ->select('id', 'status_employee', 'religion', 'employee_name', 'store_id')
    //         ->find($request->employee_id);

    //     $allowed = $this->allowedDayTypes($employee?->status_employee);

    //     if (!in_array($request->day_type, $allowed)) {
    //         $status = strtoupper($employee?->status_employee ?? '-');
    //         return response()->json([
    //             'success' => false,
    //             'message' => "Karyawan dengan status {$status} tidak dapat di-assign day type \"{$request->day_type}\".",
    //         ], 422);
    //     }

    //     $toilApproved = ToilLeaveRequests::where('employee_id', $request->employee_id)
    //         ->whereDate('leave_date', $request->date)
    //         ->where('status', 'Approved')
    //         ->exists();

    //     if ($toilApproved) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => "Karyawan punya TOIL Leave yang sudah Approved di tanggal ini. Cancel TOIL leave dulu via menu Approval kalau mau ubah jadwal.",
    //         ], 422);
    //     }

    //     $phMap = $this->getPublicHolidaysMap($request->date, $request->date);
    //     $isPH  = $this->isPublicHolidayForEmployee($phMap, $request->date, $employee?->religion);

    //     // PH di Minggu HANGUS untuk store statis → anggap bukan PH
    //     $storeName = $employee?->store_id ? Stores::find($employee->store_id)?->name : null;
    //     if ($isPH && $this->isPhVoidedOnSunday($request->date, $storeName)) {
    //         $isPH = false;
    //     }
        

    //     // ── PH TUKAR: kalau hari ini PH tapi HR set Work → SIMPAN saldo PH ──
    //     // (guard lama "harus Public Holiday" DILONGGARKAN untuk kasus Work)
    //     if ($isPH && $request->day_type === 'Work') {
    //         $phName = $this->getPublicHolidayRemark($phMap, $request->date) ?? 'Public Holiday';

    //         // Anti-duplikat: 1 PH per karyawan per tanggal asal
    //         RosterPHCarryover::firstOrCreate(
    //             [
    //                 'employee_id' => $request->employee_id,
    //                 'ph_date'     => $request->date,
    //             ],
    //             [
    //                 'ph_name'    => $phName,
    //                 'expired_at' => $this->phCarryoverExpiry($request->date)->toDateString(),
    //                 'status'     => 'available',
    //             ]
    //         );
    //     }
    //     // Kalau bukan Work dan bukan Public Holiday, baru tolak (PH wajib diakui)
    //     elseif ($isPH && $request->day_type !== 'Public Holiday') {
    //         return response()->json([
    //             'success' => false,
    //             'message' => "Tanggal ini adalah Public Holiday. Pilih \"Work\" (PH disimpan) atau \"Public Holiday\".",
    //         ], 422);
    //     }

    //     // Kalau Public Holiday, auto-isi notes dari ph.remark (kecuali user isi notes manual)
    //     $notes = $request->notes;
    //     if ($request->day_type === 'Public Holiday') {
    //         $phRemark = $this->getPublicHolidayRemark($phMap, $request->date);
    //         if (empty($notes) && !empty($phRemark)) {
    //             $notes = $phRemark;
    //         }
    //     }

    //     // ── Sick: bukti WAJIB, upload ke S3 ──
    //     $sickAttachmentPath = null;
    //     if ($request->day_type === 'Sick') {
    //         $request->validate([
    //             'sick_attachment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
    //         ], [
    //             'sick_attachment.required' => 'Bukti sakit wajib di-upload untuk day type Sick.',
    //             'sick_attachment.mimes'    => 'File harus JPG, PNG, atau PDF.',
    //             'sick_attachment.max'      => 'Ukuran file maksimal 5MB.',
    //         ]);

    //         $file     = $request->file('sick_attachment');
    //         $ext      = strtolower($file->getClientOriginalExtension());
    //         $safeName = Str::slug($employee?->employee_name ?? 'employee');
    //         $fileName = $safeName . '-' . now()->timestamp . '-sick.' . $ext;
    //         $folder   = 'employee-sickness';

    //         Storage::disk('s3')->putFileAs($folder, $file, $fileName);
    //         $sickAttachmentPath = $folder . '/' . $fileName;

    //         Log::info('[SICK] Upload selesai', [
    //             'path'   => $sickAttachmentPath,
    //             'exists' => Storage::disk('s3')->exists($sickAttachmentPath),
    //         ]);
    //     }

    //     // ── PH TUKAR: kalau HR memilih saldo PH untuk dipakai di hari ini ──
    //     // Hari pengganti = day_type "Public Holiday" + ph_carryover_id dipilih.
    //     if ($request->day_type === 'Public Holiday' && $request->filled('ph_carryover_id')) {
    //         $carryover = RosterPHCarryover::where('id', $request->ph_carryover_id)
    //             ->where('employee_id', $request->employee_id)
    //             ->where('status', 'available')
    //             ->first();

    //         if (!$carryover) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Saldo PH tukar tidak ditemukan / sudah terpakai.',
    //             ], 422);
    //         }

    //         // Tandai terpakai, catat di tanggal mana dipakai
    //         $carryover->update([
    //             'status'    => 'used',
    //             'used_date' => $request->date,
    //         ]);

    //         // Pakai nama PH simpanan sebagai notes (kalau notes kosong)
    //         if (empty($notes)) {
    //             $notes = $carryover->ph_name;
    //         }
    //     }

    //     $roster = Roster::updateOrCreate(
    //         ['employee_id' => $request->employee_id, 'date' => $request->date],
    //         [
    //             'shift_id' => $request->day_type === 'Work' ? $request->shift_id : null,
    //             'day_type' => $request->day_type,
    //             'notes'    => $notes,
    //             'sick_attachment' => $sickAttachmentPath,
    //         ]
    //     );

    //     return response()->json([
    //         'success'     => true,
    //         'roster'      => $roster->load('shift'),
    //         'roster_name' => $roster->shift?->shift_name ?? $request->day_type,
    //         'roster_time' => $roster->shift
    //             ? substr($roster->shift->start_time, 0, 5) . '-' . substr($roster->shift->end_time, 0, 5)
    //             : '',
    //     ]);
    // }
    //  public function store(Request $request)
    // {
    //     if ($this->isSPVOnly() && !$this->checkRosterWindow()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Periode pengisian roster sedang ditutup.',
    //         ], 403);
    //     }

    //     if ($request->shift_id === '') {
    //         $request->merge(['shift_id' => null]);
    //     }

    //     $request->validate([
    //         'employee_id'     => 'required|exists:employees_tables,id',
    //         'shift_id'        => 'nullable|exists:shifts_tables,id',
    //         'date'            => 'required|date',
    //         'day_type'        => 'required|in:Work,Off,Public Holiday,Leave,Cuti Melahirkan,Sick,TOIL Off',
    //         'ph_carryover_id' => 'nullable|exists:roster_ph_carryovers,id',
    //     ]);

    //     $employee = Employee::with(['store' => fn($q) => $q->wherePivot('is_primary', true)])
    //         ->select('id', 'status_employee', 'religion', 'employee_name', 'store_id')
    //         ->find($request->employee_id);

    //     $allowed = $this->allowedDayTypes($employee?->status_employee);

    //     if (!in_array($request->day_type, $allowed)) {
    //         $status = strtoupper($employee?->status_employee ?? '-');
    //         return response()->json([
    //             'success' => false,
    //             'message' => "Karyawan dengan status {$status} tidak dapat di-assign day type \"{$request->day_type}\".",
    //         ], 422);
    //     }

    //     $toilApproved = ToilLeaveRequests::where('employee_id', $request->employee_id)
    //         ->whereDate('leave_date', $request->date)
    //         ->where('status', 'Approved')
    //         ->exists();

    //     if ($toilApproved) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => "Karyawan punya TOIL Leave yang sudah Approved di tanggal ini. Cancel TOIL leave dulu via menu Approval kalau mau ubah jadwal.",
    //         ], 422);
    //     }

    //     $phMap = $this->getPublicHolidaysMap($request->date, $request->date);
    //     $isPH  = $this->isPublicHolidayForEmployee($phMap, $request->date, $employee?->religion);

    //     // PH di Minggu HANGUS untuk store statis → ambil primary store ID dari pivot
    //     $primaryStoreId = $employee?->store->first()?->id;
    //     if ($isPH && $this->isPhVoidedOnSunday($request->date, $primaryStoreId)) {
    //         $isPH = false;
    //     }

    //     // ── PH TUKAR: kalau hari ini PH tapi HR set Work → SIMPAN saldo PH ──
    //     if ($isPH && $request->day_type === 'Work') {
    //         $phName = $this->getPublicHolidayRemark($phMap, $request->date) ?? 'Public Holiday';

    //         // Anti-duplikat: 1 PH per karyawan per tanggal asal
    //         RosterPHCarryover::firstOrCreate(
    //             [
    //                 'employee_id' => $request->employee_id,
    //                 'ph_date'     => $request->date,
    //             ],
    //             [
    //                 'ph_name'    => $phName,
    //                 'expired_at' => $this->phCarryoverExpiry($request->date)->toDateString(),
    //                 'status'     => 'available',
    //             ]
    //         );
    //     }
    //     // Kalau PH asli tapi di-set Public Holiday (tidak kerja) →
    //     // batalkan carryover yang ada untuk tanggal ini karena PH dinikmati langsung
    //     elseif ($isPH && $request->day_type === 'Public Holiday') {
    //         RosterPHCarryover::where('employee_id', $request->employee_id)
    //             ->where('ph_date', $request->date)
    //             ->where('status', 'available')
    //             ->update(['status' => 'cancelled']);
    //     }
    //     // Kalau bukan Work dan bukan Public Holiday, baru tolak (PH wajib diakui)
    //     elseif ($isPH && $request->day_type !== 'Public Holiday') {
    //         return response()->json([
    //             'success' => false,
    //             'message' => "Tanggal ini adalah Public Holiday. Pilih \"Work\" (PH disimpan) atau \"Public Holiday\".",
    //         ], 422);
    //     }

    //     // Kalau Public Holiday, auto-isi notes dari ph.remark (kecuali user isi notes manual)
    //     $notes = $request->notes;
    //     if ($request->day_type === 'Public Holiday') {
    //         $phRemark = $this->getPublicHolidayRemark($phMap, $request->date);
    //         if (empty($notes) && !empty($phRemark)) {
    //             $notes = $phRemark;
    //         }
    //     }

    //     // ── Sick: bukti WAJIB, upload ke S3 ──
    //     $sickAttachmentPath = null;
    //     if ($request->day_type === 'Sick') {
    //         $request->validate([
    //             'sick_attachment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
    //         ], [
    //             'sick_attachment.required' => 'Bukti sakit wajib di-upload untuk day type Sick.',
    //             'sick_attachment.mimes'    => 'File harus JPG, PNG, atau PDF.',
    //             'sick_attachment.max'      => 'Ukuran file maksimal 5MB.',
    //         ]);

    //         $file     = $request->file('sick_attachment');
    //         $ext      = strtolower($file->getClientOriginalExtension());
    //         $safeName = Str::slug($employee?->employee_name ?? 'employee');
    //         $fileName = $safeName . '-' . now()->timestamp . '-sick.' . $ext;
    //         $folder   = 'employee-sickness';

    //         Storage::disk('s3')->putFileAs($folder, $file, $fileName);
    //         $sickAttachmentPath = $folder . '/' . $fileName;

    //         Log::info('[SICK] Upload selesai', [
    //             'path'   => $sickAttachmentPath,
    //             'exists' => Storage::disk('s3')->exists($sickAttachmentPath),
    //         ]);
    //     }

    //     // ── PH TUKAR: kalau HR memilih saldo PH untuk dipakai di hari ini ──
    //     // Hari pengganti = day_type "Public Holiday" + ph_carryover_id dipilih.
    //     if ($request->day_type === 'Public Holiday' && $request->filled('ph_carryover_id')) {
    //         $carryover = RosterPHCarryover::where('id', $request->ph_carryover_id)
    //             ->where('employee_id', $request->employee_id)
    //             ->where('status', 'available')
    //             ->first();

    //         if (!$carryover) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Saldo PH tukar tidak ditemukan / sudah terpakai.',
    //             ], 422);
    //         }

    //         // Tandai terpakai, catat di tanggal mana dipakai
    //         $carryover->update([
    //             'status'    => 'used',
    //             'used_date' => $request->date,
    //         ]);

    //         // Pakai nama PH simpanan sebagai notes (kalau notes kosong)
    //         if (empty($notes)) {
    //             $notes = $carryover->ph_name;
    //         }
    //     }

    //     $roster = Roster::updateOrCreate(
    //         ['employee_id' => $request->employee_id, 'date' => $request->date],
    //         [
    //             'shift_id'        => $request->day_type === 'Work' ? $request->shift_id : null,
    //             'day_type'        => $request->day_type,
    //             'notes'           => $notes,
    //             'sick_attachment' => $sickAttachmentPath,
    //         ]
    //     );

    //     return response()->json([
    //         'success'     => true,
    //         'roster'      => $roster->load('shift'),
    //         'roster_name' => $roster->shift?->shift_name ?? $request->day_type,
    //         'roster_time' => $roster->shift
    //             ? substr($roster->shift->start_time, 0, 5) . '-' . substr($roster->shift->end_time, 0, 5)
    //             : '',
    //     ]);
    // }

//     public function store(Request $request)
//     {
//         if ($this->isSPVOnly() && !$this->checkRosterWindow()) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Periode pengisian roster sedang ditutup.',
//             ], 403);
//         }

//         if ($request->shift_id === '') {
//             $request->merge(['shift_id' => null]);
//         }

//         $request->validate([
//             'employee_id'     => 'required|exists:employees_tables,id',
//             'shift_id'        => 'nullable|exists:shifts_tables,id',
//             'date'            => 'required|date',
//             'day_type'        => 'required|in:Work,Off,Public Holiday,Leave,Cuti Melahirkan,Sick,TOIL Off',
//             'ph_carryover_id' => 'nullable|exists:roster_ph_carryovers,id',
//         ]);

//         $employee = Employee::with(['store' => fn($q) => $q->wherePivot('is_primary', true)])
//             ->select('id', 'status_employee', 'religion', 'employee_name', 'store_id')
//             ->find($request->employee_id);

//         $allowed = $this->allowedDayTypes($employee?->status_employee);

//         if (!in_array($request->day_type, $allowed)) {
//             $status = strtoupper($employee?->status_employee ?? '-');
//             return response()->json([
//                 'success' => false,
//                 'message' => "Karyawan dengan status {$status} tidak dapat di-assign day type \"{$request->day_type}\".",
//             ], 422);
//         }

//         $toilApproved = ToilLeaveRequests::where('employee_id', $request->employee_id)
//             ->whereDate('leave_date', $request->date)
//             ->where('status', 'Approved')
//             ->exists();

//         if ($toilApproved) {
//             return response()->json([
//                 'success' => false,
//                 'message' => "Karyawan punya TOIL Leave yang sudah Approved di tanggal ini. Cancel TOIL leave dulu via menu Approval kalau mau ubah jadwal.",
//             ], 422);
//         }

//         // $phMap = $this->getPublicHolidaysMap($request->date, $request->date);
//         // $isPH  = $this->isPublicHolidayForEmployee($phMap, $request->date, $employee?->religion);
// $phMap = $this->getPublicHolidaysMap($request->date, $request->date);
// $isPH  = $this->isPublicHolidayForEmployee($phMap, $request->date, $employee?->religion);

// Log::info('[ROSTER STORE DEBUG]', [
//     'date'      => $request->date,
//     'day_type'  => $request->day_type,
//     'religion'  => $employee?->religion,
//     'isPH'      => $isPH,
//     'phMap'     => $phMap,
// ]);
//         // PH di Minggu HANGUS untuk store statis → ambil primary store ID dari pivot
//         $primaryStoreId = $employee?->store->first()?->id;
//         if ($isPH && $this->isPhVoidedOnSunday($request->date, $primaryStoreId)) {
//             $isPH = false;
//         }

//         // ── PH TUKAR: kalau hari ini PH tapi HR set Work → SIMPAN saldo PH ──
//         if ($isPH && $request->day_type === 'Work') {
//             $phName = $this->getPublicHolidayRemark($phMap, $request->date) ?? 'Public Holiday';

//             // Anti-duplikat: 1 PH per karyawan per tanggal asal
//             RosterPHCarryover::firstOrCreate(
//                 [
//                     'employee_id' => $request->employee_id,
//                     'ph_date'     => $request->date,
//                 ],
//                 [
//                     'ph_name'    => $phName,
//                     'expired_at' => $this->phCarryoverExpiry($request->date)->toDateString(),
//                     'status'     => 'available',
//                 ]
//             );
//         }
//         // Kalau PH asli tapi di-set Public Holiday (tidak kerja) →
//         // batalkan carryover yang ada untuk tanggal ini karena PH dinikmati langsung
//         elseif ($isPH && $request->day_type === 'Public Holiday') {
//             RosterPHCarryover::where('employee_id', $request->employee_id)
//                 ->where('ph_date', $request->date)
//                 ->where('status', 'available')
//                 ->update(['status' => 'cancelled']);
//         }
//         // Kalau bukan Work dan bukan Public Holiday, baru tolak (PH wajib diakui)
//         elseif ($isPH && $request->day_type !== 'Public Holiday') {
//             return response()->json([
//                 'success' => false,
//                 'message' => "Tanggal ini adalah Public Holiday. Pilih \"Work\" (PH disimpan) atau \"Public Holiday\".",
//             ], 422);
//         }

//         // Kalau Public Holiday, auto-isi notes dari ph.remark (kecuali user isi notes manual)
//         $notes = $request->notes;
//         if ($request->day_type === 'Public Holiday') {
//             $phRemark = $this->getPublicHolidayRemark($phMap, $request->date);
//             if (empty($notes) && !empty($phRemark)) {
//                 $notes = $phRemark;
//             }
//         }

//         // ── Sick: bukti WAJIB, upload ke S3 ──
//         $sickAttachmentPath = null;
//         if ($request->day_type === 'Sick') {
//             $request->validate([
//                 'sick_attachment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
//             ], [
//                 'sick_attachment.required' => 'Bukti sakit wajib di-upload untuk day type Sick.',
//                 'sick_attachment.mimes'    => 'File harus JPG, PNG, atau PDF.',
//                 'sick_attachment.max'      => 'Ukuran file maksimal 5MB.',
//             ]);

//             $file     = $request->file('sick_attachment');
//             $ext      = strtolower($file->getClientOriginalExtension());
//             $safeName = Str::slug($employee?->employee_name ?? 'employee');
//             $fileName = $safeName . '-' . now()->timestamp . '-sick.' . $ext;
//             $folder   = 'employee-sickness';

//             Storage::disk('s3')->putFileAs($folder, $file, $fileName);
//             $sickAttachmentPath = $folder . '/' . $fileName;

//             Log::info('[SICK] Upload selesai', [
//                 'path'   => $sickAttachmentPath,
//                 'exists' => Storage::disk('s3')->exists($sickAttachmentPath),
//             ]);
//         }

//         // ── PH TUKAR: kalau HR memilih saldo PH untuk dipakai di hari ini ──
//         // Hari pengganti = day_type "Public Holiday" + ph_carryover_id dipilih.
//         if ($request->day_type === 'Public Holiday' && $request->filled('ph_carryover_id')) {
//             $carryover = RosterPHCarryover::where('id', $request->ph_carryover_id)
//                 ->where('employee_id', $request->employee_id)
//                 ->where('status', 'available')
//                 ->first();

//             if (!$carryover) {
//                 return response()->json([
//                     'success' => false,
//                     'message' => 'Saldo PH tukar tidak ditemukan / sudah terpakai.',
//                 ], 422);
//             }

//             // Tandai terpakai, catat di tanggal mana dipakai
//             $carryover->update([
//                 'status'    => 'used',
//                 'used_date' => $request->date,
//             ]);

//             // Pakai nama PH simpanan sebagai notes (kalau notes kosong)
//             if (empty($notes)) {
//                 $notes = $carryover->ph_name;
//             }
//         }

//         $roster = Roster::updateOrCreate(
//             ['employee_id' => $request->employee_id, 'date' => $request->date],
//             [
//                 'shift_id'        => $request->day_type === 'Work' ? $request->shift_id : null,
//                 'day_type'        => $request->day_type,
//                 'notes'           => $notes,
//                 'sick_attachment' => $sickAttachmentPath,
//             ]
//         );

//         return response()->json([
//             'success'     => true,
//             'roster'      => $roster->load('shift'),
//             'roster_name' => $roster->shift?->shift_name ?? $request->day_type,
//             'roster_time' => $roster->shift
//                 ? substr($roster->shift->start_time, 0, 5) . '-' . substr($roster->shift->end_time, 0, 5)
//                 : '',
//         ]);
//     }
 public function store(Request $request)
    {
        if ($this->isSPVOnly() && !$this->checkRosterWindow()) {
            return response()->json([
                'success' => false,
                'message' => 'Periode pengisian roster sedang ditutup.',
            ], 403);
        }
 
        if ($request->shift_id === '') {
            $request->merge(['shift_id' => null]);
        }
 
        $request->validate([
            'employee_id'     => 'required|exists:employees_tables,id',
            'shift_id'        => 'nullable|exists:shifts_tables,id',
            'date'            => 'required|date',
            'day_type'        => 'required|in:Work,Off,Public Holiday,Leave,Cuti Melahirkan,Sick,TOIL Off',
            'ph_carryover_id' => 'nullable|exists:roster_ph_carryovers,id',
        ]);
 
        $employee = Employee::with(['store' => fn($q) => $q->wherePivot('is_primary', true)])
            ->select('id', 'status_employee', 'religion', 'employee_name', 'store_id')
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
 
        // PH di Minggu HANGUS untuk store statis → ambil primary store ID dari pivot
        $primaryStoreId = $employee?->store->first()?->id;
        if ($isPH && $this->isPhVoidedOnSunday($request->date, $primaryStoreId)) {
            $isPH = false;
        }
 
        // ── PH TUKAR: kalau hari ini PH tapi HR set Work → SIMPAN saldo PH ──
        if ($isPH && $request->day_type === 'Work') {
            $phName = $this->getPublicHolidayRemark($phMap, $request->date) ?? 'Public Holiday';
 
            // Jika sudah ada carryover (misal status cancelled karena sebelumnya di-set PH),
            // reset kembali ke available. Jika belum ada, buat baru.
            RosterPHCarryover::updateOrCreate(
                [
                    'employee_id' => $request->employee_id,
                    'ph_date'     => $request->date,
                ],
                [
                    'ph_name'    => $phName,
                    'expired_at' => $this->phCarryoverExpiry($request->date)->toDateString(),
                    'status'     => 'available',
                ]
            );
        }
        // Kalau PH asli tapi di-set Public Holiday (tidak kerja) →
        // batalkan carryover yang ada untuk tanggal ini karena PH dinikmati langsung
        elseif ($isPH && $request->day_type === 'Public Holiday') {
            RosterPHCarryover::where('employee_id', $request->employee_id)
                ->where('ph_date', $request->date)
                ->where('status', 'available')
                ->update(['status' => 'cancelled']);
        }
        // Kalau bukan Work dan bukan Public Holiday, baru tolak (PH wajib diakui)
        elseif ($isPH && $request->day_type !== 'Public Holiday') {
            return response()->json([
                'success' => false,
                'message' => "Tanggal ini adalah Public Holiday. Pilih \"Work\" (PH disimpan) atau \"Public Holiday\".",
            ], 422);
        }
        // Kalau bukan PH asli tapi di-set Public Holiday → WAJIB pilih saldo PH tukar
        elseif (!$isPH && $request->day_type === 'Public Holiday' && !$request->filled('ph_carryover_id')) {
            return response()->json([
                'success' => false,
                'message' => "Tanggal ini bukan Public Holiday. Pilih saldo PH Tukar (Simpanan) untuk menjadikan hari ini sebagai pengganti PH.",
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
 
        // ── Sick: bukti WAJIB, upload ke S3 ──
        $sickAttachmentPath = null;
        if ($request->day_type === 'Sick') {
            $request->validate([
                'sick_attachment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            ], [
                'sick_attachment.required' => 'Bukti sakit wajib di-upload untuk day type Sick.',
                'sick_attachment.mimes'    => 'File harus JPG, PNG, atau PDF.',
                'sick_attachment.max'      => 'Ukuran file maksimal 5MB.',
            ]);
 
            $file     = $request->file('sick_attachment');
            $ext      = strtolower($file->getClientOriginalExtension());
            $safeName = Str::slug($employee?->employee_name ?? 'employee');
            $fileName = $safeName . '-' . now()->timestamp . '-sick.' . $ext;
            $folder   = 'employee-sickness';
 
            Storage::disk('s3')->putFileAs($folder, $file, $fileName);
            $sickAttachmentPath = $folder . '/' . $fileName;
 
            Log::info('[SICK] Upload selesai', [
                'path'   => $sickAttachmentPath,
                'exists' => Storage::disk('s3')->exists($sickAttachmentPath),
            ]);
        }
 
        // ── PH TUKAR: kalau HR memilih saldo PH untuk dipakai di hari ini ──
        // Hari pengganti = day_type "Public Holiday" + ph_carryover_id dipilih.
        if ($request->day_type === 'Public Holiday' && $request->filled('ph_carryover_id')) {
            $carryover = RosterPHCarryover::where('id', $request->ph_carryover_id)
                ->where('employee_id', $request->employee_id)
                ->where('status', 'available')
                ->first();
 
            if (!$carryover) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo PH tukar tidak ditemukan / sudah terpakai.',
                ], 422);
            }
 
            // Tandai terpakai, catat di tanggal mana dipakai
            $carryover->update([
                'status'    => 'used',
                'used_date' => $request->date,
            ]);
 
            // Pakai nama PH simpanan sebagai notes (kalau notes kosong)
            if (empty($notes)) {
                $notes = $carryover->ph_name;
            }
        }
 
        $roster = Roster::updateOrCreate(
            ['employee_id' => $request->employee_id, 'date' => $request->date],
            [
                'shift_id'        => $request->day_type === 'Work' ? $request->shift_id : null,
                'day_type'        => $request->day_type,
                'notes'           => $notes,
                'sick_attachment' => $sickAttachmentPath,
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
    //  AMBIL daftar saldo PH tukar yang masih tersedia utk 1 karyawan
    // ─────────────────────────────────────────────────────────────
    public function availablePhCarryovers(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees_tables,id',
            'date'        => 'required|date',
        ]);
 
        // Ambil semua carryover available milik employee ini yang belum expired
        // Status 'used' sudah di-set saat import (RosterImport) maupun saat store() via ph_carryover_id
        $items = RosterPHCarryover::where('employee_id', $request->employee_id)
            ->where('status', 'available')
            ->whereDate('expired_at', '>=', $request->date)
            ->orderBy('ph_date')
            ->get(['id', 'ph_name', 'ph_date', 'expired_at']);
 
        return response()->json([
            'success' => true,
            'data'    => $items->map(fn($it) => [
                'id'         => $it->id,
                'ph_name'    => $it->ph_name,
                'ph_date'    => Carbon::parse($it->ph_date)->toDateString(),
                'expired_at' => Carbon::parse($it->expired_at)->toDateString(),
            ]),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  AMBIL daftar saldo PH tukar yang masih tersedia utk 1 karyawan ini benar
    // ─────────────────────────────────────────────────────────────
    // public function availablePhCarryovers(Request $request)
    // {
    //     $request->validate([
    //         'employee_id' => 'required|exists:employees_tables,id',
    //         'date'        => 'required|date',
    //     ]);

    //     // Ambil semua carryover available milik employee ini yang belum expired
    //     // Status 'used' sudah di-set saat import (RosterImport) maupun saat store() via ph_carryover_id
    //     $items = RosterPHCarryover::where('employee_id', $request->employee_id)
    //         ->where('status', 'available')
    //         ->whereDate('expired_at', '>=', $request->date)
    //         ->orderBy('ph_date')
    //         ->get(['id', 'ph_name', 'ph_date', 'expired_at']);

    //     return response()->json([
    //         'success' => true,
    //         'data'    => $items->map(fn($it) => [
    //             'id'         => $it->id,
    //             'ph_name'    => $it->ph_name,
    //             'ph_date'    => Carbon::parse($it->ph_date)->toDateString(),
    //             'expired_at' => Carbon::parse($it->expired_at)->toDateString(),
    //         ]),
    //     ]);
    // }
     

    // ─────────────────────────────────────────────────────────────
    //  AMBIL daftar saldo PH tukar yang masih tersedia utk 1 karyawan
    //  Dipakai dropdown di modal cell. jangan pakai ini
    // ─────────────────────────────────────────────────────────────
    // public function availablePhCarryovers(Request $request)
    // {
    //     $request->validate([
    //         'employee_id' => 'required|exists:employees_tables,id',
    //         'date'        => 'required|date',
    //     ]);

    //     // Hanya saldo: milik karyawan ini, status available, belum kedaluwarsa
    //     // (kedaluwarsa = expired_at >= tanggal hari pengganti yang dipilih)
    //     $items = RosterPHCarryover::where('employee_id', $request->employee_id)
    //         ->where('status', 'available')
    //         ->whereDate('expired_at', '>=', $request->date)
    //         ->orderBy('ph_date')
    //         ->get(['id', 'ph_name', 'ph_date', 'expired_at']);

    //     return response()->json([
    //         'success' => true,
    //         'data'    => $items->map(fn($it) => [
    //             'id'         => $it->id,
    //             'ph_name'    => $it->ph_name,
    //             'ph_date'    => Carbon::parse($it->ph_date)->toDateString(),
    //             'expired_at' => Carbon::parse($it->expired_at)->toDateString(),
    //         ]),
    //     ]);
    // }
//    public function availablePhCarryovers(Request $request)
//     {
//         $request->validate([
//             'employee_id' => 'required|exists:employees_tables,id',
//             'date'        => 'required|date',
//         ]);

//         // Ambil semua carryover available milik employee ini yang belum expired
//         // Status 'used' sudah di-set saat import (RosterImport) maupun saat store() via ph_carryover_id
//         $items = RosterPHCarryover::where('employee_id', $request->employee_id)
//             ->where('status', 'available')
//             ->whereDate('expired_at', '>=', $request->date)
//             ->orderBy('ph_date')
//             ->get(['id', 'ph_name', 'ph_date', 'expired_at']);

//         return response()->json([
//             'success' => true,
//             'data'    => $items->map(fn($it) => [
//                 'id'         => $it->id,
//                 'ph_name'    => $it->ph_name,
//                 'ph_date'    => Carbon::parse($it->ph_date)->toDateString(),
//                 'expired_at' => Carbon::parse($it->expired_at)->toDateString(),
//             ]),
//         ]);
//     }

    // ─────────────────────────────────────────────────────────────
    //  DESTROY (hapus 1 cell)
    // ─────────────────────────────────────────────────────────────

    // public function destroy(Request $request)
    // {
    //     if ($this->isSPVOnly() && !$this->checkRosterWindow()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Periode pengisian roster sedang ditutup.',
    //         ], 403);
    //     }
    //     $request->validate([
    //         'employee_id' => 'required|exists:employees_tables,id',
    //         'date'        => 'required|date',
    //     ]);

    //     $toilApproved = ToilLeaveRequests::where('employee_id', $request->employee_id)
    //         ->whereDate('leave_date', $request->date)
    //         ->where('status', 'Approved')
    //         ->exists();

    //     if ($toilApproved) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Tidak bisa hapus roster — ada TOIL Leave yang sudah Approved untuk tanggal ini.',
    //         ], 422);
    //     }


       
    //     //  Sesudah - trigger Eloquent events
    //     $rosters = Roster::where('employee_id', $request->employee_id)
    //         ->where('date', $request->date)
    //         ->get();
    //     Log::info('Rosters found: ' . $rosters->count());
    //     Log::info($rosters->pluck('id')->toArray());

    //     // ── REFUND PH TUKAR: kalau cell ini memakai saldo PH, kembalikan ──
    //     RosterPHCarryover::where('employee_id', $request->employee_id)
    //         ->where('status', 'used')
    //         ->whereDate('used_date', $request->date)
    //         ->update(['status' => 'available', 'used_date' => null]);

    //     foreach ($rosters as $roster) {
    //          if ($roster->sick_attachment) {
    //     Storage::disk('s3')->delete($roster->sick_attachment);
    // }
    //         $roster->delete();
    //     }

    //     return response()->json(['success' => true]);
    // }
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
 
        $rosters = Roster::where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->get();
 
        // ── REFUND PH TUKAR: carryover 'used' di tanggal ini → available kembali ──
        RosterPHCarryover::where('employee_id', $request->employee_id)
            ->where('status', 'used')
            ->whereDate('used_date', $request->date)
            ->update(['status' => 'available', 'used_date' => null]);
 
        // ── RESET PH ASLI: carryover 'cancelled' di ph_date ini → available kembali ──
        // (roster PH dihapus, jadi PH belum tentu dinikmati)
        RosterPHCarryover::where('employee_id', $request->employee_id)
            ->where('status', 'cancelled')
            ->where('ph_date', $request->date)
            ->update(['status' => 'available']);
 
        foreach ($rosters as $roster) {
            // Hapus file S3 jika ada sick_attachment
            if ($roster->sick_attachment) {
                Storage::disk('s3')->delete($roster->sick_attachment);
            }
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

    // ─────────────────────────────────────────────────────────────
    //  HELPER: Public Holiday map untuk rentang tanggal
    // ─────────────────────────────────────────────────────────────
    private function getPublicHolidaysMap(string $startDate, string $endDate): array
    {
        $holidays = PublicHoliday::whereBetween('date', [$startDate, $endDate])->get();

        $map = [];
        foreach ($holidays as $ph) {
            $dateStr = Carbon::parse($ph->date)->toDateString();
            $map[$dateStr][] = [
                'type'   => $ph->type,    // 'Hindu' | 'Non Hindu' | 'All'
                'remark' => $ph->remark,
            ];
        }
        return $map;
    }

    // ─────────────────────────────────────────────────────────────
    //  HELPER: Cek apakah store termasuk "store statis"
    //  (Head Office / Holding / Distribution Center)
    // ─────────────────────────────────────────────────────────────
    private function isStaticStore(?string $storeName): bool
    {
        $staticStoreNames = [
        '019623ad-de58-7368-8873-e3cbff2b0aff',
        '019a230d-6146-7001-848d-046ccdbdf163',
        '019963a7-cdb8-7002-b10b-163645c199d0',
    ];
        return in_array($storeName ?? '', $staticStoreNames);
    }

    // ─────────────────────────────────────────────────────────────
    //  HELPER: Hitung akhir periode roster (tgl 25) untuk tanggal tertentu
    //  Periode roster: 26 bulan lalu → 25 bulan ini.
    //  Contoh: 17 Feb → akhir periode = 25 Feb.
    // ─────────────────────────────────────────────────────────────
    private function periodEndFor(Carbon $date): Carbon
    {
        // Kalau tanggal >= 26, periode berakhir tgl 25 BULAN BERIKUTNYA.
        // Kalau tanggal <= 25, periode berakhir tgl 25 BULAN INI.
        if ($date->day >= 26) {
            return $date->copy()->addMonth()->day(25);
        }
        return $date->copy()->day(25);
    }

    // ─────────────────────────────────────────────────────────────
    //  HELPER: Tanggal kedaluwarsa saldo PH tukar
    //  = akhir periode +2 dari periode tanggal PH asal.
    //  Contoh: PH 17 Feb → periode berakhir 25 Feb → +2 periode → 25 Apr.
    // ─────────────────────────────────────────────────────────────
    private function phCarryoverExpiry(string $phDate): Carbon
    {
        $end = $this->periodEndFor(Carbon::parse($phDate)); // 25 Feb
        return $end->copy()->addMonths(2);                   // 25 Apr
    }

    // ─────────────────────────────────────────────────────────────
    //  HELPER: PH HANGUS jika jatuh di Minggu DAN store statis
    //  → untuk store statis, Minggu sudah Off, jadi PH tidak berlaku
    // ─────────────────────────────────────────────────────────────
    private function isPhVoidedOnSunday(string $date, ?string $storeName): bool
    {
        return Carbon::parse($date)->isSunday() && $this->isStaticStore($storeName);
    }

    // Cek apakah tanggal ini PH yang relevan untuk agama karyawan
    private function isPublicHolidayForEmployee(array $phMap, string $date, ?string $religion): bool
    {
        $dateStr = Carbon::parse($date)->toDateString();
        if (!isset($phMap[$dateStr])) {
            return false;
        }

        $relevantTypes = $this->resolveRelevantPhTypes($religion); // ['Hindu','All'] atau ['Non Hindu','All']

        foreach ($phMap[$dateStr] as $ph) {
            if (in_array($ph['type'], $relevantTypes)) {
                return true;
            }
        }
        return false;
    }

    // Ambil remark PH pertama di tanggal tsb (untuk auto-isi notes)
    private function getPublicHolidayRemark(array $phMap, string $date): ?string
    {
        $dateStr = Carbon::parse($date)->toDateString();
        if (!isset($phMap[$dateStr]) || empty($phMap[$dateStr])) {
            return null;
        }
        return $phMap[$dateStr][0]['remark'] ?? null;
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
            'day_type'          => 'required|in:Work,Off,Public Holiday,Leave,Cuti Melahirkan,Sick,TOIL Off',
            'skip_weekend'      => 'boolean',
            'saturday_shift'    => 'boolean',
            'saturday_shift_id' => 'nullable|exists:shifts_tables,id',
        ]);


        $employees = Employee::with('store:id,name')
            ->select('id', 'employee_name', 'status_employee', 'religion', 'store_id')
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
                        ['shift_id' => null, 'day_type' => 'TOIL Off', 'notes' => null]
                    );
                    $skippedCount++;
                    continue;
                }

                if ($eligibleForPH && isset($publicHolidayMap[$dateStr])) {
                    $matchingPH = $publicHolidayMap[$dateStr]->first(
                        fn($ph) => in_array($ph->type, $relevantPhTypes)
                    );

                    // PH hangus kalau Minggu + store statis → jangan jadikan PH
                    if ($matchingPH && !$this->isPhVoidedOnSunday($dateStr, optional($emp->store)->name)) {
                        Log::info('Public Holiday Match', [
                            'employee' => $emp->employee_name,
                            'date'     => $dateStr,
                            'type'     => $matchingPH->type,
                            'remark'   => $matchingPH->remark,
                        ]);

                        $resolvedDayType = 'Public Holiday';
                        $resolvedShiftId = null;
                        $resolvedNotes   = $matchingPH->remark;
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
    //  DOWNLOAD TEMPLATE Excel (terisi karyawan + tanggal)
    // ─────────────────────────────────────────────────────────────
    public function downloadTemplate(Request $request)
    {
        $request->validate([
            'store_id'   => 'required|exists:stores_tables,id',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        /** @var \App\Models\User $user */
        $user         = auth()->user();
        $canManageAll = $user->hasPermissionTo('ManageRoster');
        $canManageSPV = $user->hasPermissionTo('ManageRosterSPVManager');

        // SPV (bukan admin): hanya boleh store yang ada di daftar miliknya
        if ($canManageSPV && !$canManageAll) {
            $myStoreIds = $this->userStoreIds($user);
            if (!in_array($request->store_id, $myStoreIds)) {
                abort(403, 'Anda tidak punya akses ke store ini.');
            }
        }

        $storeName = Stores::find($request->store_id)?->name ?? 'store';
        $filename  = 'template-roster-' . str($storeName)->slug() . '-'
            . $request->start_date . '-to-' . $request->end_date . '.xlsx';

        return Excel::download(
            new RosterTemplateExport($request->store_id, $request->start_date, $request->end_date),
            $filename
        );
    }

    // ─────────────────────────────────────────────────────────────
    //  HELPER: Daftar store_id milik user (multi-store).
    //  Utamakan pivot employee_stores; fallback ke kolom store_id
    //  tunggal kalau pivot belum diisi.
    // ─────────────────────────────────────────────────────────────
    private function userStoreIds($user): array
    {
        $employee = $user->employee;
        if (!$employee) return [];

        // Dari relasi multi-store (pivot employee_stores)
        $ids = $employee->store->pluck('id')->toArray();

        // Fallback: pivot kosong → pakai kolom store_id tunggal
        if (empty($ids) && $employee->store_id) {
            $ids = [$employee->store_id];
        }

        return $ids;
    }

    // ─────────────────────────────────────────────────────────────
    //  IMPORT EXCEL (matriks: baris=karyawan, kolom=tanggal)
    // ─────────────────────────────────────────────────────────────
    public function importExcel(Request $request)
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
            'store_id'   => 'required|exists:stores_tables,id',
            'start_date' => 'required|date',
            'file'       => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file.required' => 'File Excel wajib di-upload.',
            'file.mimes'    => 'File harus .xlsx, .xls, atau .csv.',
            'file.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        try {
            $import = new RosterImport($request->store_id, $request->start_date);
            Excel::import($import, $request->file('file'));

            $message = "Import selesai. {$import->created} jadwal tersimpan.";
            if (!empty($import->errors)) {
                $message .= " " . count($import->errors) . " baris dilewati.";
            }

            return response()->json([
                'success'  => true,
                'message'  => $message,
                'created'  => $import->created,
                'errors'   => $import->errors,
            ]);
        } catch (\Throwable $e) {
            Log::error('[ROSTER IMPORT] Gagal', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses file: ' . $e->getMessage(),
            ], 422);
        }
    }
    // ─────────────────────────────────────────────────────────────
    //  BULK DELETE
    //  Update: protect tanggal TOIL Approved (versi AMAN, loop per karyawan)
    // ─────────────────────────────────────────────────────────────

    // public function bulkDelete(Request $request)
    // {
    //     ini_set('memory_limit', '512M');
    //     set_time_limit(300);
    //     if ($this->isSPVOnly() && !$this->checkRosterWindow()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Periode pengisian roster sedang ditutup.',
    //         ], 403);
    //     }

    //     $request->validate([
    //         'employee_ids'   => 'required|array|min:1',
    //         'employee_ids.*' => 'exists:employees_tables,id',
    //         'start_date'     => 'required|date',
    //         'end_date'       => 'required|date|after_or_equal:start_date',
    //     ]);

    //     $toilApprovedMap = $this->getToilApprovedDatesMap(
    //         $request->employee_ids,
    //         $request->start_date,
    //         $request->end_date
    //     );

    //     $count          = 0;
    //     $protectedCount = 0;

    //     foreach ($request->employee_ids as $empId) {
    //         $protectedDates = $toilApprovedMap[$empId] ?? [];

    //         $query = Roster::where('employee_id', $empId)
    //             ->whereBetween('date', [$request->start_date, $request->end_date]);

    //         if (!empty($protectedDates)) {
    //             $query->whereNotIn('date', $protectedDates);
    //             $protectedCount += count($protectedDates);
    //         }

    //         //  Chunk agar tidak timeout kalau data banyak
    //         $query->chunk(100, function ($rosters) use (&$count) {
    //             foreach ($rosters as $roster) {
    //                 $roster->delete(); // trigger Eloquent events → activity log tercatat
    //                 $count++;
    //             }
    //         });
    //     }

    //     $message = "Berhasil menghapus {$count} jadwal.";
    //     if ($protectedCount > 0) {
    //         $message .= " {$protectedCount} jadwal di-protect karena ada TOIL Leave yang sudah Approved.";
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => $message,
    //     ]);
    // }
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
 
            // ── REFUND PH TUKAR: carryover 'used' dalam rentang → available kembali ──
            RosterPHCarryover::where('employee_id', $empId)
                ->where('status', 'used')
                ->whereBetween('used_date', [$request->start_date, $request->end_date])
                ->when(!empty($protectedDates), fn($q) => $q->whereNotIn('used_date', $protectedDates))
                ->update(['status' => 'available', 'used_date' => null]);
 
            // ── RESET PH ASLI: carryover 'cancelled' dalam rentang → available kembali ──
            RosterPHCarryover::where('employee_id', $empId)
                ->where('status', 'cancelled')
                ->whereBetween('ph_date', [$request->start_date, $request->end_date])
                ->when(!empty($protectedDates), fn($q) => $q->whereNotIn('ph_date', $protectedDates))
                ->update(['status' => 'available']);
 
            // ── Hapus roster + file S3 ──
            $query->chunk(100, function ($rosters) use (&$count) {
                foreach ($rosters as $roster) {
                    if ($roster->sick_attachment) {
                        Storage::disk('s3')->delete($roster->sick_attachment);
                    }
                    $roster->delete();
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

        $employees = Employee::with('store:id,name')
            ->select('id', 'religion', 'store_id')
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
                    } elseif (
                        $this->isPublicHolidayForEmployee($phMap, $dateStr, $religion)
                        && !$this->isPhVoidedOnSunday($dateStr, optional($employees[$empId]?->store)->name)
                    ) {
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
                } elseif (
                    $this->isPublicHolidayForEmployee($phMap, $newDate, $religion)
                    && !$this->isPhVoidedOnSunday($newDate, optional($employees[$src->employee_id]?->store)->name)
                ) {
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
        $myStoreId = $myEmployee->store_id;


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
                        $q->where('store_id', $request->store_id);
                    }
                } elseif ($canManageSPV) {
                    $q->where('store_id', $myStoreId);
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

        $myStore      = $myEmployee->store()->wherePivot('is_primary', true)->first();
        $myStoreId    = $myStore?->id;
        $myDepartment   = $myEmployee->department()->wherePivot('is_primary', true)->first();
        $myDepartmentId = $myDepartment?->id;
        $myCompanyId    = $myEmployee->company_id;


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

           $storeName = $request->store_id
    ? Stores::find($request->store_id)?->name
    : ($employeeIdFilter
        ? ($myEmployee->store_id ? Stores::find($myEmployee->store_id)?->name : null)
        : 'All Locations');

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
        myDepartmentId: $canManageSPV && !$canManageAll ? $myDepartmentId : null,
        myCompanyId: $canManageSPV && !$canManageAll ? $myCompanyId : null,
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

       
        if (!$canManageAll && $canManageSPV) {
            $storeId = $user->employee->store_id;
            $query->whereHas('causer.employee', function ($q) use ($storeId) {
                $q->where('store_id', $storeId);
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('causer_name', function ($row) {
                if (!$row->causer) return 'System';
                return optional($row->causer->employee)->employee_name ?? $row->causer->name ?? 'System';
            })
            ->addColumn('properties', function ($row) {
                // $properties = $row->properties;
                $properties = $row->properties;

    $formatValue = function ($val) {
        if (is_null($val)) return '-';

        // Coba parse sebagai datetime (ISO 8601 atau Y-m-d)
        if (is_string($val) && preg_match('/^\d{4}-\d{2}-\d{2}/', $val)) {
            try {
                return \Carbon\Carbon::parse($val)->format('d M Y');
            } catch (\Exception $e) {
                return $val;
            }
        }

        return $val;
    };

                if ($row->event === 'created') {
                    return collect($properties->get('attributes', []))
                        // ->map(fn($val, $key) => "<b>{$key}</b>: {$val}")
                        ->map(fn($val, $key) => "<b>{$key}</b>: " . $formatValue($val))
                        ->implode('<br>');
                }

                if ($row->event === 'updated') {
                    $old        = $properties->get('old', []);
                    $attributes = $properties->get('attributes', []);

                    return collect($attributes)
                        // ->map(fn($val, $key) => "<b>{$key}</b>: " . ($old[$key] ?? '-') . " → {$val}")
                        ->map(fn($val, $key) => "<b>{$key}</b>: " . $formatValue($old[$key] ?? null) . " → " . $formatValue($val))
                        ->implode('<br>');
                }

                if ($row->event === 'deleted') {
                    return collect($properties->get('attributes', []))
                        // ->map(fn($val, $key) => "<b>{$key}</b>: {$val}")
                        ->map(fn($val, $key) => "<b>{$key}</b>: " . $formatValue($val))
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
