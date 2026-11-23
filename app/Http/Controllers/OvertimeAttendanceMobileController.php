<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Departments;
use App\Models\Employee;
use App\Models\OvertimeAttendanceLog;
use App\Models\Stores;
use App\Models\Toilbalances;
use App\Services\ImageCompressor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

/**
 * Log absensi lembur dari aplikasi mobile (selfie + GPS), plus entri manual, dengan persetujuan bertingkat:
 *
 *   pengaju (entri manual) ──► pending ──checker──► approved ──HR──► approved_hr (final)
 *   absen aplikasi yang lolos verifikasi wajah & GPS langsung approved (sistem = checker), menunggu HR.
 *   HR bisa menandai (flagged) atau mengembalikan ke tahap sebelumnya.
 *
 * Hanya karyawan yang punya saldo lembur di Toilbalances (status selain cancelled) yang tampil
 * dan bisa dibuatkan entri; setiap log terikat ke lembur yang disetujui di tanggal itu.
 *
 * Akses:
 * - ManageOvertimeAttendanceMobile           : HR. Semua karyawan; bulk setujui approved → approved_hr; ubah status apa pun;
 *                                              entri manual bisa langsung final; lihat riwayat aktivitas.
 * - ManageOvertimeAttendanceMobileSPVManager : checker. Store+departemen yang sama + bawahan langsung;
 *                                              bulk setujui pending → approved (bukan entri yang ia ajukan sendiri).
 * - ViewOvertimeAttendanceMobile             : pengaju. Melihat rekan di store & departemen utama yang sama; mengajukan
 *                                              entri manual untuk dirinya sendiri (harus punya saldo di Toilbalances), masuk pending.
 */
class OvertimeAttendanceMobileController extends Controller
{
    private const BULK_MAX = 200;

    private function perms($user): array
    {
        return [
            $user->can('ManageOvertimeAttendanceMobile'),
            $user->can('ManageOvertimeAttendanceMobileSPVManager'),
            $user->can('ViewOvertimeAttendanceMobile'),
        ];
    }

    private static function hashId(string $id): string
    {
        return substr(hash('sha256', 'ot' . $id . config('app.key')), 0, 8);
    }

    /** Hanya karyawan yang tercatat di Toilbalances (punya saldo lembur yang tidak dibatalkan). */
    private function onlyToilEmployees($query, string $col): void
    {
        $query->whereExists(fn($q) => $q->select(DB::raw(1))->from('toil_balances_tables')
            ->whereColumn('toil_balances_tables.employee_id', $col)
            ->where('toil_balances_tables.status', '!=', 'cancelled'));
    }

    /**
     * Batasi query ke karyawan yang boleh dilihat. $col = kolom employee_id pada query.
     * Mengembalikan false kalau user tidak punya cakupan sama sekali.
     */
    private function applyScope($query, $user, string $col, bool $canManage, bool $canSpvManager, bool $canView): bool
    {
        $this->onlyToilEmployees($query, $col);
        if ($canManage) return true;
        $employee = $user->employee;
        if (!$employee) return false;

        if ($canSpvManager) {
            $storeIds = $employee->store()->pluck('stores_tables.id')->toArray();
            $deptIds  = $employee->department()->pluck('departments_tables.id')->toArray();
            $bawahan  = $employee->bawahanList()->pluck('employees_tables.id')->toArray();

            if (empty($storeIds) || empty($deptIds)) {
                if (empty($bawahan)) return false;
                $query->whereIn($col, $bawahan);
                return true;
            }
            $query->where(function ($q) use ($col, $storeIds, $deptIds, $bawahan) {
                $q->where(function ($q1) use ($col, $storeIds, $deptIds) {
                    $q1->whereExists(fn($sq) => $sq->select(DB::raw(1))->from('employee_stores')
                        ->whereColumn('employee_stores.employee_id', $col)->whereIn('employee_stores.store_id', $storeIds))
                        ->whereExists(fn($sq) => $sq->select(DB::raw(1))->from('employee_departments')
                            ->whereColumn('employee_departments.employee_id', $col)->whereIn('employee_departments.department_id', $deptIds));
                });
                if (!empty($bawahan)) $q->orWhereIn($col, $bawahan);
            });
            return true;
        }

        if ($canView) {
            $storeId = $employee->primaryStore()->first()?->id;
            $deptId  = $employee->primaryDepartment()->first()?->id;
            if (!$storeId || !$deptId) return false;
            $query->whereExists(fn($q) => $q->select(DB::raw(1))->from('employee_stores')
                ->whereColumn('employee_stores.employee_id', $col)->where('employee_stores.store_id', $storeId)->where('employee_stores.is_primary', true))
                ->whereExists(fn($q) => $q->select(DB::raw(1))->from('employee_departments')
                    ->whereColumn('employee_departments.employee_id', $col)->where('employee_departments.department_id', $deptId)->where('employee_departments.is_primary', true));
            return true;
        }

        return false;
    }

    /**
     * Karyawan yang boleh dibuatkan entri manual oleh user ini (selalu yang punya saldo di Toilbalances).
     * HR semua, checker timnya, pengaju (View saja) hanya dirinya sendiri.
     */
    private function applyEntryScope($query, $user, string $col, bool $canManage, bool $canSpvManager, bool $canView): bool
    {
        if ($canManage || $canSpvManager) {
            return $this->applyScope($query, $user, $col, $canManage, $canSpvManager, false);
        }
        if ($canView && $user->employee) {
            $this->onlyToilEmployees($query, $col);
            $query->where($col, $user->employee->id);
            return true;
        }
        return false;
    }

    /**
     * Tingkat persetujuan yang boleh dilakukan user atas satu log, atau null.
     * - 'hr'      : HR menyetujui approved → approved_hr
     * - 'checker' : SPV/manager menyetujui pending → approved, bukan entri yang ia ajukan sendiri
     */
    private function approvalLevelFor(OvertimeAttendanceLog $log, $user, bool $canManage, bool $canSpvManager): ?string
    {
        if ($canManage && $log->status === OvertimeAttendanceLog::STATUS_APPROVED) return 'hr';
        if ($canSpvManager && $log->status === OvertimeAttendanceLog::STATUS_PENDING && $log->created_by !== $user->id) return 'checker';
        return null;
    }

    private function logActivity($user, OvertimeAttendanceLog $log, string $event, string $description, array $props): void
    {
        activity(OvertimeAttendanceLog::LOG_NAME)->causedBy($user)->performedOn($log)
            ->withProperties($props)->event($event)->log($description);
    }

    public function index()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        [$canManage, $canSpvManager, $canView] = $this->perms($user);
        if (!$canManage && !$canSpvManager && !$canView) abort(403, 'Unauthorized');

        $stores = $departments = $companies = $employees = collect();

        if ($canManage) {
            $stores      = Stores::select('id', 'name')->whereNotNull('name')->orderBy('name')->get();
            $departments = Departments::select('id', 'department_name')->orderBy('department_name')->get();
            $companies   = Company::select('id', 'name')->orderBy('name')->get();
        } elseif ($user->employee) {
            $stores = Stores::whereIn('id', $user->employee->store()->pluck('stores_tables.id'))->select('id', 'name')->orderBy('name')->get();
        }

        // Karyawan yang bisa dibuatkan entri manual: HR semua, checker timnya, pengaju (View) hanya dirinya
        $q = Employee::select('id', 'employee_name', 'employee_pengenal')->whereNull('deleted_at')
            ->whereIn('status', ['Active', 'Pending', 'On Leave', 'Mutation'])->orderBy('employee_name');
        $employees = $this->applyEntryScope($q, $user, 'employees_tables.id', $canManage, $canSpvManager, $canView) ? $q->get() : collect();
        // Pengaju tanpa saldo lembur tidak bisa mengajukan entri
        $canCreate = $canManage || $canSpvManager || ($canView && $employees->isNotEmpty());

        $first = today()->startOfMonth();
        [$defaultFrom, $defaultTo] = today()->day >= 26
            ? [today()->day(26)->toDateString(), $first->copy()->addMonthNoOverflow()->day(25)->toDateString()]
            : [$first->copy()->subMonthNoOverflow()->day(26)->toDateString(), today()->day(25)->toDateString()];

        return view('pages.OvertimeAttendanceMobile.index', compact('stores', 'employees', 'departments', 'companies', 'canManage', 'canSpvManager', 'canView', 'canCreate', 'defaultFrom', 'defaultTo'));
    }

    public function getOvertimeAttendanceMobiles(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        [$canManage, $canSpvManager, $canView] = $this->perms($user);
        if (!$canManage && !$canSpvManager && !$canView) abort(403, 'Unauthorized');

        $request->validate([
            'date_from'         => 'nullable|date',
            'date_to'           => 'nullable|date|after_or_equal:date_from',
            'type'              => 'nullable|in:checkin,checkout',
            'status'            => 'nullable|in:' . implode(',', OvertimeAttendanceLog::STATUSES),
            'source'            => 'nullable|in:app,manual,offsite',
            'compensation_type' => 'nullable|in:Toil,Cash',
        ]);

        $query = OvertimeAttendanceLog::with([
            'employee:id,employee_name,employee_pengenal,company_id,status_employee,attendance_type',
            'store:id,name,geofence_radius',
            'employee.company:id,name',
            'employee.department' => fn($q) => $q->wherePivot('is_primary', true),
            'overtimeSubmission:id,date,end_date,start_time,end_time,total_hours,compensation_type',
            'createdBy:id,username,employee_id',
            'createdBy.employee:id,employee_name',
        ])->select('overtime_attendance_logs.*');

        if (!$this->applyScope($query, $user, 'overtime_attendance_logs.employee_id', $canManage, $canSpvManager, $canView)) {
            return DataTables::of(collect())->with(['stats' => null])->make(true);
        }

        if ($request->filled('date_from')) $query->where('overtime_attendance_logs.work_date', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->where('overtime_attendance_logs.work_date', '<=', $request->date_to);
        if ($request->filled('type'))      $query->where('overtime_attendance_logs.type', $request->type);
        if ($request->filled('status'))    $query->where('overtime_attendance_logs.status', $request->status);
        // Entri manual tidak punya koordinat GPS
        if ($request->source === 'manual')  $query->whereNull('overtime_attendance_logs.latitude');
        if ($request->source === 'app')     $query->whereNotNull('overtime_attendance_logs.latitude');
        if ($request->source === 'offsite') $query->where('overtime_attendance_logs.is_offsite', true);
        if ($request->filled('compensation_type')) {
            $query->whereHas('overtimeSubmission', fn($q) => $q->where('compensation_type', $request->compensation_type));
        }

        if ($canManage) {
            if ($request->filled('company_id')) {
                $query->whereHas('employee', fn($q) => $q->where('company_id', $request->company_id));
            }
            if ($request->filled('department_id')) {
                $query->whereExists(fn($q) => $q->select(DB::raw(1))->from('employee_departments')
                    ->whereColumn('employee_departments.employee_id', 'overtime_attendance_logs.employee_id')
                    ->where('employee_departments.department_id', $request->department_id)
                    ->where('employee_departments.is_primary', true));
            }
            if ($request->filled('store_id')) $query->where('overtime_attendance_logs.store_id', $request->store_id);
        } elseif ($canSpvManager && $request->filled('store_id')) {
            $query->where('overtime_attendance_logs.store_id', $request->store_id);
        }
        if (($canManage || $canSpvManager) && $request->filled('employee_id')) {
            $query->where('overtime_attendance_logs.employee_id', $request->employee_id);
        }

        // Ringkasan untuk filter yang sama (sebelum paging)
        $base = clone (clone $query)->getQuery();
        $base->columns = null;
        $base->orders  = null;
        $s = $base->selectRaw("COUNT(*) as total,
                SUM(overtime_attendance_logs.type = 'checkin') as checkin,
                SUM(overtime_attendance_logs.type = 'checkout') as checkout,
                SUM(overtime_attendance_logs.status = 'pending') as pending,
                SUM(overtime_attendance_logs.status = 'approved') as approved,
                SUM(overtime_attendance_logs.status = 'approved_hr') as approved_hr,
                SUM(overtime_attendance_logs.status = 'flagged') as flagged,
                SUM(overtime_attendance_logs.latitude IS NULL) as manual_entries,
                SUM(overtime_attendance_logs.is_offsite = 1) as offsite,
                SUM(overtime_attendance_logs.status = 'pending' AND (overtime_attendance_logs.created_by IS NULL OR overtime_attendance_logs.created_by != ?)) as pending_checkable,
                COUNT(DISTINCT overtime_attendance_logs.employee_id) as employees", [$user->id])
            ->first();
        $stats = collect((array) $s)->map(fn($v) => (int) $v);

        $tz = config('app.timezone');
        return DataTables::eloquent($query)
            ->addColumn('hash', fn($log) => self::hashId($log->id))
            ->addColumn('employee_name', fn($log) => $log->employee?->employee_name ?? '-')
            ->addColumn('employee_pengenal', fn($log) => $log->employee?->employee_pengenal ?? '-')
            ->addColumn('company_name', fn($log) => $log->employee?->company?->name ?? '-')
            ->addColumn('department_name', fn($log) => $log->employee?->department?->first()?->department_name ?? '-')
            ->addColumn('store_name', fn($log) => $log->store?->name ?? 'Work From Anywhere')
            ->addColumn('is_manual', fn($log) => $log->latitude === null)
            ->addColumn('submitted_by', fn($log) => $log->createdBy?->employee?->employee_name ?? $log->createdBy?->username)
            ->addColumn('is_mine', fn($log) => $log->created_by === $user->id)
            // Tingkat persetujuan yang bisa dilakukan user ini atas baris ini (untuk checkbox bulk)
            ->addColumn('approve_level', fn($log) => $this->approvalLevelFor($log, $user, $canManage, $canSpvManager))
            ->addColumn('attendance_mode_label', fn($log) => Employee::getAttendanceTypeLabels()[$log->attendance_mode] ?? null)
            ->addColumn('overtime_plan', function ($log) {
                $ot = $log->overtimeSubmission;
                return $ot ? substr($ot->start_time, 0, 5) . '–' . substr($ot->end_time, 0, 5) : null;
            })
            ->addColumn('overtime_hours', fn($log) => $log->overtimeSubmission ? (float) $log->overtimeSubmission->total_hours : null)
            ->addColumn('compensation_type', fn($log) => $log->overtimeSubmission?->compensation_type)
            ->addColumn('logged_time', fn($log) => optional($log->logged_at)->timezone($tz)->format('H:i'))
            ->addColumn('logged_date', fn($log) => optional($log->logged_at)->timezone($tz)->toDateString())
            ->addColumn('logged_full', fn($log) => optional($log->logged_at)->timezone($tz)->format('d M Y H:i'))
            ->editColumn('work_date', fn($log) => $log->work_date ? Carbon::parse($log->work_date)->toDateString() : null)
            ->editColumn('distance_from_store', fn($log) => $log->distance_from_store !== null ? (float) $log->distance_from_store : null)
            ->addColumn('geofence_radius', fn($log) => $log->store?->geofence_radius)
            ->addColumn('show_url', fn($log) => route('overtimeattendancemobile.show', self::hashId($log->id)))
            ->removeColumn('photo_path', 'device_id', 'latitude', 'longitude', 'employee', 'store', 'overtime_submission', 'created_by', 'checked_by', 'hr_approved_by')
            ->filterColumn('employee_name', fn($q, $k) => $q->whereHas('employee', fn($e) => $e->where('employee_name', 'like', "%{$k}%")->orWhere('employee_pengenal', 'like', "%{$k}%")))
            ->orderColumn('employee_name', fn($q, $o) => $q->orderBy(Employee::select('employee_name')->whereColumn('employees_tables.id', 'overtime_attendance_logs.employee_id'), $o))
            ->orderColumn('store_name', fn($q, $o) => $q->orderBy(Stores::select('name')->whereColumn('stores_tables.id', 'overtime_attendance_logs.store_id'), $o))
            ->with(['stats' => $stats])
            ->make(true);
    }

    /** Lembur yang disetujui (punya saldo di Toilbalances) untuk karyawan & tanggal, dipakai form entri manual. */
    private function findBalance(string $employeeId, string $workDate): ?Toilbalances
    {
        return Toilbalances::with('overtimeSubmission')
            ->where('employee_id', $employeeId)
            ->where('status', '!=', 'cancelled')
            ->whereHas('overtimeSubmission', fn($q) => $q->where('status', 'Approved')->whereDate('date', $workDate))
            ->first();
    }

    /** JSON: lembur karyawan di tanggal itu, supaya form entri manual bisa menampilkan jadwalnya. */
    public function overtimeOf(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        [$canManage, $canSpvManager, $canView] = $this->perms($user);
        if (!$canManage && !$canSpvManager && !$canView) abort(403, 'Unauthorized');

        $data = $request->validate(['employee_id' => 'required|string', 'work_date' => 'required|date']);
        $q = Employee::where('employees_tables.id', $data['employee_id']);
        if (!$this->applyEntryScope($q, $user, 'employees_tables.id', $canManage, $canSpvManager, $canView) || !$q->exists()) {
            return response()->json(['success' => false, 'message' => ($canManage || $canSpvManager) ? 'Karyawan ini di luar cakupan Anda atau belum punya saldo lembur.' : 'Anda hanya bisa mengajukan entri untuk diri sendiri, dan harus punya saldo lembur.'], 403);
        }

        $balance = $this->findBalance($data['employee_id'], $data['work_date']);
        if (!$balance) {
            return response()->json(['success' => false, 'message' => 'Tidak ada lembur yang disetujui untuk karyawan ini di tanggal tersebut.']);
        }
        $ot = $balance->overtimeSubmission;
        $taken = OvertimeAttendanceLog::where('overtime_submission_id', $ot->id)->pluck('type');

        return response()->json([
            'success' => true,
            'data' => [
                'start_time'        => substr($ot->start_time, 0, 5),
                'end_time'          => substr($ot->end_time, 0, 5),
                'total_hours'       => (float) $ot->total_hours,
                'compensation_type' => $ot->compensation_type,
                'has_checkin'       => $taken->contains('checkin'),
                'has_checkout'      => $taken->contains('checkout'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        [$canManage, $canSpvManager, $canView] = $this->perms($user);
        if (!$canManage && !$canSpvManager && !$canView) abort(403, 'Unauthorized');

        $rules = [
            'employee_id' => 'required|exists:employees_tables,id',
            'store_id'    => 'nullable|exists:stores_tables,id',
            'type'        => 'required|in:checkin,checkout',
            'work_date'   => 'required|date|before_or_equal:today',
            'logged_at'   => 'required|date',
            'flag_reason' => 'required|string|min:5|max:255',
            // Foto dikecilkan sebelum disimpan (ImageCompressor), jadi batas unggah boleh lebih besar
            'photo_path'  => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ];
        if ($canManage) $rules['status'] = 'required|in:' . OvertimeAttendanceLog::STATUS_APPROVED_HR . ',' . OvertimeAttendanceLog::STATUS_PENDING;

        $validated = $request->validate($rules, [
            'work_date.before_or_equal' => 'Tanggal lembur tidak boleh di masa depan.',
            'flag_reason.required'      => 'Alasan wajib diisi.',
            'photo_path.required'       => 'Foto atau surat pendukung wajib diunggah.',
            'photo_path.mimes'          => 'Bukti harus berupa foto (jpg, png, webp) atau pdf.',
            'photo_path.max'            => 'Ukuran bukti maksimal 10 MB.',
        ]);

        // Cakupan + syarat Toilbalances berlaku untuk semua pengaju; pengaju (View) hanya dirinya sendiri
        $q = Employee::where('employees_tables.id', $validated['employee_id']);
        if (!$this->applyEntryScope($q, $user, 'employees_tables.id', $canManage, $canSpvManager, $canView) || !$q->exists()) {
            return response()->json(['success' => false, 'message' => ($canManage || $canSpvManager)
                ? 'Karyawan ini di luar cakupan Anda atau belum punya saldo lembur.'
                : 'Anda hanya bisa mengajukan entri untuk diri sendiri, dan harus punya saldo lembur di TOIL.'], 403);
        }
        if (!$canManage && !empty($validated['store_id']) && !$user->employee->store()->where('stores_tables.id', $validated['store_id'])->exists()) {
            return response()->json(['success' => false, 'message' => 'Lokasi ini bukan lokasi Anda.'], 403);
        }

        $balance = $this->findBalance($validated['employee_id'], $validated['work_date']);
        if (!$balance) {
            return response()->json(['success' => false, 'message' => 'Karyawan ini tidak punya lembur yang disetujui di tanggal tersebut.'], 422);
        }

        // Aturan jam sama dengan aplikasi:
        // check-in  : 1 jam sebelum mulai s/d jam selesai lembur
        // check-out : jam selesai lembur s/d akhir hari setelah tanggal lembur
        $loggedAt = Carbon::parse($validated['logged_at'], config('app.timezone'));
        $ot = $balance->overtimeSubmission;
        [$start, $end] = OvertimeAttendanceLog::overtimeWindow($ot);
        [$from, $until] = $validated['type'] === 'checkin'
            ? [$start->copy()->subMinutes(OvertimeAttendanceLog::CHECKIN_EARLY_MINUTES), $end]
            : [$end, OvertimeAttendanceLog::checkoutDeadline($ot)];
        if ($loggedAt->isFuture() || $loggedAt->lt($from) || $loggedAt->gt($until)) {
            return response()->json([
                'success' => false,
                'message' => ($validated['type'] === 'checkin' ? 'Jam check-in' : 'Jam check-out') . ' lembur harus antara '
                    . $from->format('d M H:i') . ' dan ' . $until->format('d M H:i') . ' (jadwal lembur ' . $start->format('H:i') . '–' . $end->format('H:i') . '), dan tidak di masa depan.',
            ], 422);
        }

        // Selain HR, entri manual selalu menunggu checker
        $status = $canManage ? $validated['status'] : OvertimeAttendanceLog::STATUS_PENDING;

        // Foto dikecilkan dulu, lalu diunggah di luar transaksi supaya lock tidak menahan durasi upload
        $packed = app(ImageCompressor::class)->compress($request->file('photo_path'));
        if ($packed['extension'] === 'pdf' && $packed['bytes'] > 5 * 1024 * 1024) {
            return response()->json(['success' => false, 'message' => 'File PDF maksimal 5 MB.', 'errors' => ['photo_path' => ['File PDF maksimal 5 MB.']]], 422);
        }
        $photoPath = 'attendance-overtime-manual/' . now()->format('Y/m') . '/' . \Ramsey\Uuid\Uuid::uuid7()->toString() . '.' . $packed['extension'];
        try {
            Storage::disk('s3')->put($photoPath, $packed['contents'], ['ContentType' => $packed['mime']]);
        } catch (\Throwable $e) {
            Log::error('OvertimeAttendanceMobile store: photo upload failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Gagal mengunggah foto/surat pendukung.'], 500);
        }

        try {
            $log = DB::transaction(function () use ($validated, $status, $photoPath, $loggedAt, $balance, $user) {
                $duplicate = OvertimeAttendanceLog::where('overtime_submission_id', $balance->overtime_submission_id)
                    ->where('type', $validated['type'])
                    ->lockForUpdate()->exists();
                if ($duplicate) {
                    throw new \App\Exceptions\DuplicateAttendanceException('Sudah ada ' . ($validated['type'] === 'checkin' ? 'check-in' : 'check-out') . ' untuk lembur ini.');
                }
                $final = $status === OvertimeAttendanceLog::STATUS_APPROVED_HR;
                return OvertimeAttendanceLog::create([
                    'employee_id'            => $validated['employee_id'],
                    'overtime_submission_id' => $balance->overtime_submission_id,
                    'toil_balance_id'        => $balance->id,
                    'store_id'               => $validated['store_id'] ?? null,
                    'type'                   => $validated['type'],
                    'attendance_mode'        => 'manual',
                    'work_date'              => $validated['work_date'],
                    'logged_at'              => $loggedAt,
                    'status'                 => $status,
                    'flag_reason'            => $validated['flag_reason'],
                    'photo_path'             => $photoPath,
                    'is_within_geofence'     => true,
                    'is_mock_location'       => false,
                    'liveness_passed'        => true,
                    'created_by'             => $user->id,
                    // HR yang langsung memfinalkan dianggap sekaligus checker
                    'checked_by'             => $final ? $user->id : null,
                    'checked_at'             => $final ? now() : null,
                    'hr_approved_by'         => $final ? $user->id : null,
                    'hr_approved_at'         => $final ? now() : null,
                ]);
            });
        } catch (\App\Exceptions\DuplicateAttendanceException $e) {
            Storage::disk('s3')->delete($photoPath);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            Storage::disk('s3')->delete($photoPath);
            if ($e->getCode() === '23000') {
                return response()->json(['success' => false, 'message' => 'Sudah ada absen dengan tipe ini untuk lembur tersebut.'], 422);
            }
            Log::error('OvertimeAttendanceMobile store: query exception', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            throw $e;
        }

        $this->logActivity($user, $log, 'created', 'Entri absensi lembur manual diajukan', [
            'attributes' => array_merge($validated, [
                'status'                 => $status,
                'photo_path'             => $photoPath,
                'overtime_submission_id' => $balance->overtime_submission_id,
                'photo_bytes'            => $packed['bytes'],
                'photo_original_bytes'   => $packed['original_bytes'],
            ]),
        ]);

        return response()->json([
            'success' => true,
            'message' => $status === OvertimeAttendanceLog::STATUS_PENDING
                ? 'Entri tersimpan dan menunggu dicek atasan, lalu disetujui HR.'
                : 'Entri absensi lembur tersimpan dan langsung disetujui HR.',
        ]);
    }

    /**
     * Setujui banyak log sekaligus.
     * level=checker : pending → approved     (ManageOvertimeAttendanceMobileSPVManager, dalam cakupan, bukan entri sendiri)
     * level=hr      : approved → approved_hr (ManageOvertimeAttendanceMobile)
     * Log yang statusnya sudah berubah sejak dipilih dilewati, bukan digagalkan.
     */
    public function bulkApprove(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        [$canManage, $canSpvManager] = $this->perms($user);

        $data = $request->validate([
            'level'    => 'required|in:checker,hr',
            'hashes'   => 'required|array|min:1|max:' . self::BULK_MAX,
            'hashes.*' => 'required|string|size:8',
        ], ['hashes.max' => 'Maksimal ' . self::BULK_MAX . ' absensi lembur sekali setujui.']);

        $isHr = $data['level'] === 'hr';
        if ($isHr && !$canManage) abort(403, 'Hanya HR yang bisa memberi persetujuan akhir.');
        if (!$isHr && !$canSpvManager) abort(403, 'Hanya checker (SPV/manager) yang bisa menyetujui tahap pertama.');

        $from = $isHr ? OvertimeAttendanceLog::STATUS_APPROVED : OvertimeAttendanceLog::STATUS_PENDING;
        $to   = $isHr ? OvertimeAttendanceLog::STATUS_APPROVED_HR : OvertimeAttendanceLog::STATUS_APPROVED;

        // Kandidat: status asal yang benar dan dalam cakupan; hash dicocokkan di sini
        $candidates = OvertimeAttendanceLog::query()->select('overtime_attendance_logs.id')->where('overtime_attendance_logs.status', $from);
        if (!$isHr) {
            if (!$this->applyScope($candidates, $user, 'overtime_attendance_logs.employee_id', false, true, false)) {
                return response()->json(['success' => false, 'message' => 'Anda tidak punya cakupan karyawan.'], 403);
            }
            $candidates->where(fn($q) => $q->whereNull('overtime_attendance_logs.created_by')->orWhere('overtime_attendance_logs.created_by', '!=', $user->id));
        }
        $wanted = array_flip($data['hashes']);
        $ids = $candidates->pluck('overtime_attendance_logs.id')->filter(fn($id) => isset($wanted[self::hashId($id)]))->values();

        $approved = 0;
        DB::transaction(function () use ($ids, $from, $to, $isHr, $user, &$approved) {
            foreach (OvertimeAttendanceLog::whereIn('id', $ids)->lockForUpdate()->get() as $log) {
                if ($log->status !== $from) continue; // sudah diubah orang lain
                $log->update($isHr
                    ? ['status' => $to, 'hr_approved_by' => $user->id, 'hr_approved_at' => now()]
                    : ['status' => $to, 'checked_by' => $user->id, 'checked_at' => now()]);
                $this->logActivity($user, $log, 'updated', $isHr ? 'Disetujui HR (bulk)' : 'Disetujui checker (bulk)', [
                    'old' => ['status' => $from], 'attributes' => ['status' => $to],
                ]);
                $approved++;
            }
        });

        $skipped = count($data['hashes']) - $approved;
        return response()->json([
            'success'  => true,
            'approved' => $approved,
            'skipped'  => $skipped,
            'message'  => $approved
                ? "{$approved} absensi lembur " . ($isHr ? 'disetujui HR' : 'disetujui dan diteruskan ke HR') . '.' . ($skipped ? " {$skipped} dilewati karena statusnya sudah berubah" . ($isHr ? '.' : ' atau ajuan Anda sendiri.') : '')
                : 'Tidak ada absensi lembur yang bisa disetujui. Statusnya mungkin sudah berubah' . ($isHr ? '.' : ', atau itu ajuan Anda sendiri yang harus dicek checker lain.'),
        ]);
    }

    /** Cari log dari hash tanpa memuat seluruh tabel. */
    private function findByHash(string $hash): ?OvertimeAttendanceLog
    {
        foreach (OvertimeAttendanceLog::query()->select('id')->orderByDesc('logged_at')->cursor() as $row) {
            if (hash_equals(self::hashId($row->id), $hash)) {
                return OvertimeAttendanceLog::with(['employee', 'store', 'overtimeSubmission', 'toilBalance', 'createdBy.employee', 'checkedBy.employee', 'hrApprovedBy.employee'])->find($row->id);
            }
        }
        return null;
    }

    private function canSeeLog($user, OvertimeAttendanceLog $log, bool $canManage, bool $canSpvManager, bool $canView): bool
    {
        $q = OvertimeAttendanceLog::where('overtime_attendance_logs.id', $log->id);
        return $this->applyScope($q, $user, 'overtime_attendance_logs.employee_id', $canManage, $canSpvManager, $canView) && $q->exists();
    }

    public function show(string $hash)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        [$canManage, $canSpvManager, $canView] = $this->perms($user);
        if (!$canManage && !$canSpvManager && !$canView) abort(403, 'Unauthorized');

        $log = $this->findByHash($hash);
        if (!$log) abort(404, 'Log absensi lembur tidak ditemukan.');
        if (!$this->canSeeLog($user, $log, $canManage, $canSpvManager, $canView)) abort(403, 'Anda tidak punya akses ke log absensi lembur ini.');

        $photoUrl = null;
        $isPdf    = $log->photo_path && str_ends_with(strtolower($log->photo_path), '.pdf');
        if ($log->photo_path) {
            try {
                $photoUrl = Storage::disk('s3')->temporaryUrl($log->photo_path, now()->addMinutes(10));
            } catch (\Throwable $e) {
                Log::warning('OVERTIME_ATTENDANCE_PHOTO_URL_FAILED', ['log_id' => $log->id, 'error' => $e->getMessage()]);
            }
        }

        // Pasangan check-in/check-out untuk lembur yang sama
        $pair = OvertimeAttendanceLog::where('overtime_submission_id', $log->overtime_submission_id)
            ->where('id', '!=', $log->id)
            ->first();
        $pairUrl = $pair ? route('overtimeattendancemobile.show', self::hashId($pair->id)) : null;

        $window = $log->overtimeSubmission ? OvertimeAttendanceLog::overtimeWindow($log->overtimeSubmission) : null;

        $history = Activity::with('causer.employee')
            ->where('subject_type', OvertimeAttendanceLog::class)->where('subject_id', $log->id)
            ->latest()->limit(10)->get();

        return view('pages.OvertimeAttendanceMobile.show', [
            'log'       => $log,
            'hash'      => $hash,
            'photoUrl'  => $photoUrl,
            'isPdf'     => $isPdf,
            'pair'      => $pair,
            'pairUrl'   => $pairUrl,
            'overtime'  => $log->overtimeSubmission,
            'window'    => $window,
            'history'   => $history,
            'isManual'     => $log->latitude === null,
            'canManage'    => $canManage,
            'approveLevel' => $this->approvalLevelFor($log, $user, $canManage, $canSpvManager),
            'isOwnEntry'   => $log->created_by === $user->id,
        ]);
    }

    /**
     * Ubah status satu log dari halaman detail.
     * - HR: status apa pun (setujui final, tandai, kembalikan ke tahap sebelumnya).
     * - Checker: hanya pending → approved untuk log dalam cakupan yang bukan ajuannya sendiri.
     */
    public function updateStatus(Request $request, string $hash)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        [$canManage, $canSpvManager, $canView] = $this->perms($user);

        $data = $request->validate([
            'status' => 'required|in:' . implode(',', OvertimeAttendanceLog::STATUSES),
            'note'   => 'nullable|string|max:255|required_if:status,flagged',
        ], ['note.required_if' => 'Alasan wajib diisi saat menandai absensi lembur.']);

        $log = $this->findByHash($hash);
        if (!$log) abort(404, 'Log absensi lembur tidak ditemukan.');

        if (!$canManage) {
            $checkerOk = $data['status'] === OvertimeAttendanceLog::STATUS_APPROVED
                && $this->approvalLevelFor($log, $user, false, $canSpvManager) === 'checker'
                && $this->canSeeLog($user, $log, false, $canSpvManager, $canView);
            if (!$checkerOk) abort(403, 'Anda hanya bisa menyetujui absensi lembur yang menunggu checker, dan bukan ajuan Anda sendiri.');
        }

        $old = $log->status;
        $new = $data['status'];
        $update = [
            'status'      => $new,
            // Alasan asli (mis. entri manual) tetap disimpan, catatan HR ditambahkan
            'flag_reason' => $new === OvertimeAttendanceLog::STATUS_FLAGGED
                ? trim(($log->flag_reason ? $log->flag_reason . ' | ' : '') . 'Ditandai HR: ' . $data['note'])
                : $log->flag_reason,
        ];
        if ($new === OvertimeAttendanceLog::STATUS_APPROVED && $old === OvertimeAttendanceLog::STATUS_PENDING) {
            $update += ['checked_by' => $user->id, 'checked_at' => now()];
        }
        if ($new === OvertimeAttendanceLog::STATUS_APPROVED_HR) {
            $update += ['hr_approved_by' => $user->id, 'hr_approved_at' => now()];
            if (!$log->checked_by && $log->created_by) $update += ['checked_by' => $user->id, 'checked_at' => now()];
        }
        // Dikembalikan ke tahap sebelumnya: persetujuan tahap sesudahnya dibatalkan
        if (in_array($new, [OvertimeAttendanceLog::STATUS_PENDING, OvertimeAttendanceLog::STATUS_APPROVED, OvertimeAttendanceLog::STATUS_FLAGGED], true)) {
            $update += ['hr_approved_by' => null, 'hr_approved_at' => null];
        }
        if ($new === OvertimeAttendanceLog::STATUS_PENDING) {
            $update['checked_by'] = null;
            $update['checked_at'] = null;
        }
        $log->update($update);

        $labels = OvertimeAttendanceLog::STATUS_LABELS;
        $this->logActivity($user, $log, 'updated', 'Status absensi lembur diubah dari ' . ($labels[$old] ?? $old) . ' ke ' . ($labels[$new] ?? $new), [
            'old' => ['status' => $old], 'attributes' => ['status' => $new, 'note' => $data['note'] ?? null],
        ]);

        return back()->with('success', 'Status absensi lembur diperbarui.');
    }

    /** Riwayat aktivitas absensi lembur (siapa mengajukan, mengecek, menyetujui, menandai). Hanya HR. */
    public function getActivities(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (!$user->can('ManageOvertimeAttendanceMobile')) abort(403);

        // 'overtime_attendance' = nama log lama sebelum diganti ke 'overtime-attendance-mobile'
        $query = Activity::whereIn('log_name', [OvertimeAttendanceLog::LOG_NAME, 'overtime_attendance'])
            ->where('subject_type', OvertimeAttendanceLog::class)
            ->with(['causer.employee', 'subject.employee:id,employee_name,employee_pengenal'])
            ->select('activity_log.*');

        $labels = OvertimeAttendanceLog::STATUS_LABELS;

        return DataTables::eloquent($query)
            ->addColumn('time', fn($a) => $a->created_at->timezone(config('app.timezone'))->locale('id')->translatedFormat('d M Y, H:i'))
            ->addColumn('causer_name', fn($a) => $a->causer?->employee?->employee_name ?? $a->causer?->username ?? 'Sistem')
            ->addColumn('employee_name', fn($a) => $a->subject?->employee?->employee_name ?? '-')
            ->addColumn('work_date', fn($a) => $a->subject?->work_date ? Carbon::parse($a->subject->work_date)->locale('id')->translatedFormat('d M Y') : null)
            ->addColumn('type', fn($a) => $a->subject?->type)
            ->addColumn('status_from', fn($a) => isset($a->properties['old']['status']) ? ($labels[$a->properties['old']['status']] ?? $a->properties['old']['status']) : null)
            ->addColumn('status_to', fn($a) => isset($a->properties['attributes']['status']) ? ($labels[$a->properties['attributes']['status']] ?? $a->properties['attributes']['status']) : null)
            ->addColumn('note', fn($a) => $a->properties['attributes']['note'] ?? null)
            ->addColumn('show_url', fn($a) => $a->subject ? route('overtimeattendancemobile.show', self::hashId($a->subject_id)) : null)
            ->removeColumn('properties', 'subject', 'causer')
            ->filter(function ($q) use ($request) {
                $k = trim((string) ($request->input('search.value') ?? ''));
                if ($k === '') return;
                $q->where(function ($w) use ($k) {
                    $w->where('activity_log.description', 'like', "%{$k}%")
                        ->orWhereHasMorph('causer', [\App\Models\User::class], fn($c) => $c->where('username', 'like', "%{$k}%")
                            ->orWhereHas('employee', fn($e) => $e->where('employee_name', 'like', "%{$k}%")))
                        ->orWhereHasMorph('subject', [OvertimeAttendanceLog::class], fn($s) => $s->whereHas('employee', fn($e) => $e->where('employee_name', 'like', "%{$k}%")));
                });
            })
            ->orderColumn('time', fn($q, $o) => $q->orderBy('activity_log.created_at', $o)->orderBy('activity_log.id', $o))
            ->make(true);
    }
}
