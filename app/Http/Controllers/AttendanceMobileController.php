<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Company;
use App\Models\Departments;
use App\Models\Employee;
use App\Models\Roster;
use App\Models\Stores;
use App\Services\ImageCompressor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

/**
 * Log absensi dari aplikasi mobile (selfie + GPS), plus entri manual, dengan persetujuan bertingkat:
 *
 *   pengaju (entri manual) ──► pending ──checker──► approved ──HR──► approved_hr (final)
 *   absen aplikasi yang lolos verifikasi wajah & GPS langsung approved (sistem = checker), menunggu HR.
 *   HR bisa menandai (flagged) atau mengembalikan ke pending.
 *
 * Akses:
 * - ManageAttendanceMobile           : HR. Semua karyawan; bulk setujui approved → approved_hr; ubah status apa pun;
 *                                      entri manual bisa langsung final; lihat riwayat aktivitas.
 * - ManageAttendanceMobileSPVManager : checker. Store+departemen yang sama + bawahan langsung;
 *                                      bulk setujui pending → approved (bukan entri yang ia ajukan sendiri).
 * - ViewAttendanceMobile             : pengaju. Hanya absensi miliknya sendiri; bisa mengajukan entri manual
 *                                      untuk dirinya (masuk pending), selebihnya hanya lihat.
 */
class AttendanceMobileController extends Controller
{
    private const BULK_MAX = 200;

    private function perms($user): array
    {
        return [
            $user->hasPermissionTo('ManageAttendanceMobile'),
            $user->hasPermissionTo('ManageAttendanceMobileSPVManager'),
            $user->hasPermissionTo('ViewAttendanceMobile'),
        ];
    }

    /**
     * Periode absensi yang sedang berjalan. Cut off setiap tanggal 25, periode baru mulai tanggal 26:
     * - tanggal 1-25  → 26 bulan lalu s/d 25 bulan ini
     * - tanggal 26-31 → 26 bulan ini s/d 25 bulan depan
     *
     * @return array{0: Carbon, 1: Carbon} awal (00:00) dan akhir (23:59:59) periode
     */
    private static function currentPeriod(): array
    {
        $today = today();
        $start = $today->day >= 26
            ? $today->copy()->day(26)
            : $today->copy()->startOfMonth()->subMonthNoOverflow()->day(26);
        $end = $start->copy()->startOfMonth()->addMonthNoOverflow()->day(25)->endOfDay();

        return [$start->startOfDay(), $end];
    }

    /** Toleransi check-out setelah shift malam berakhir, sama dengan Api\AttendanceController. */
    private const NIGHT_SHIFT_GRACE_HOURS = 6;

    /**
     * Pengecualian cut off untuk shift malam tanggal 25: check-out-nya terjadi dini hari tanggal 26,
     * jadi baru bisa diajukan saat periode sudah berganti. Diizinkan hanya kalau:
     * - tipe check-out, tanggal kerja = hari terakhir periode sebelumnya (tanggal 25);
     * - diajukan pada hari pertama periode baru (tanggal 26);
     * - roster karyawan di tanggal 25 adalah shift malam (jam pulang lebih kecil dari jam masuk);
     * - jam absen paling lambat NIGHT_SHIFT_GRACE_HOURS jam setelah shift berakhir.
     */
    private function isCutoffNightCheckout(string $employeeId, string $type, Carbon $workDate, Carbon $loggedAt, Carbon $periodStart): bool
    {
        if ($type !== 'checkout' || !today()->isSameDay($periodStart) || !$workDate->isSameDay($periodStart->copy()->subDay())) {
            return false;
        }

        $shift = Roster::with('shift:id,start_time,end_time,is_split,break_start,break_end')
            ->where('employee_id', $employeeId)->whereDate('date', $workDate)->first()?->shift;
        if (!$shift || !($shift->end_time < $shift->start_time)) {
            return false;
        }

        $shiftEnd = Carbon::parse($periodStart->toDateString() . ' ' . $shift->end_time, config('app.timezone'));
        return $loggedAt->gte($workDate) && $loggedAt->lte($shiftEnd->copy()->addHours(self::NIGHT_SHIFT_GRACE_HOURS));
    }

    private static function hashId(string $id): string
    {
        return substr(hash('sha256', $id . config('app.key')), 0, 8);
    }

    /**
     * Batasi query ke karyawan yang boleh dilihat. $col = kolom employee_id pada query.
     * Mengembalikan false kalau user tidak punya cakupan sama sekali.
     */
    private function applyScope($query, $user, string $col, bool $canManage, bool $canSpvManager, bool $canView): bool
    {
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

        // ViewAttendanceMobile: hanya dirinya sendiri
        if ($canView) {
            $query->where($col, $employee->id);
            return true;
        }

        return false;
    }

    /**
     * Tingkat persetujuan yang boleh dilakukan user atas satu log, atau null.
     * - 'hr'      : HR menyetujui approved → approved_hr
     * - 'checker' : SPV/manager menyetujui pending → approved, bukan entri yang ia ajukan sendiri
     */
    private function approvalLevelFor(AttendanceLog $log, $user, bool $canManage, bool $canSpvManager): ?string
    {
        if ($canManage && $log->status === AttendanceLog::STATUS_APPROVED) return 'hr';
        if ($canSpvManager && $log->status === AttendanceLog::STATUS_PENDING && $log->created_by !== $user->id) return 'checker';
        return null;
    }

    private function logActivity($user, AttendanceLog $log, string $event, string $description, array $props): void
    {
        activity(AttendanceLog::LOG_NAME)->causedBy($user)->performedOn($log)
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
        $q = Employee::select('id', 'employee_name', 'employee_pengenal', 'attendance_type')->whereNull('deleted_at')
            ->whereIn('status', ['Active', 'Pending', 'On Leave', 'Mutation'])->orderBy('employee_name');
        $employees = $this->applyScope($q, $user, 'employees_tables.id', $canManage, $canSpvManager, $canView) ? $q->get() : collect();

        // Lokasi entri manual harus salah satu lokasi karyawan itu: [employee_id => [store_id, ...]]
        $assigned = DB::table('employee_stores')->whereIn('employee_id', $employees->pluck('id'))->get(['employee_id', 'store_id']);
        $employeeStores = $assigned->groupBy('employee_id')->map(fn($rows) => $rows->pluck('store_id')->values());
        $entryStores = Stores::whereIn('id', $assigned->pluck('store_id')->unique())->select('id', 'name')->orderBy('name')->get();

        [$periodStart, $periodEnd] = self::currentPeriod();
        $defaultFrom = $periodStart->toDateString();
        $defaultTo   = $periodEnd->toDateString();

        $canCreate = $canManage || $canSpvManager || $canView;
        // Entri manual selain HR dibatasi periode berjalan (dan tidak ke masa depan); HR tidak dibatasi
        // Pada tanggal 26 (hari pertama periode), tanggal 25 masih bisa dipilih untuk check-out shift malam
        $entryMin = $canManage ? null : (today()->isSameDay($periodStart) ? $periodStart->copy()->subDay()->toDateString() : $periodStart->toDateString());
        $entryMax = $canManage ? today()->toDateString() : min($periodEnd->toDateString(), today()->toDateString());
        $periodLabel = $periodStart->locale('id')->translatedFormat('d M Y') . ' sampai ' . $periodEnd->locale('id')->translatedFormat('d M Y');

        return view('pages.AttendanceMobile.index', compact(
            'stores', 'employees', 'departments', 'companies', 'canManage', 'canSpvManager', 'canView', 'canCreate', 'defaultFrom', 'defaultTo',
            'employeeStores', 'entryStores', 'entryMin', 'entryMax', 'periodLabel'
        ));
    }

    public function getAttendanceMobiles(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        [$canManage, $canSpvManager, $canView] = $this->perms($user);
        if (!$canManage && !$canSpvManager && !$canView) abort(403, 'Unauthorized');

        $request->validate([
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
            'type'      => 'nullable|in:checkin,checkout',
            'status'    => 'nullable|in:' . implode(',', AttendanceLog::STATUSES),
            'source'    => 'nullable|in:app,manual,offsite',
        ]);

        $query = AttendanceLog::with([
            'employee:id,employee_name,employee_pengenal,company_id,status_employee,attendance_type',
            'store:id,name,geofence_radius',
            'employee.company:id,name',
            'employee.department' => fn($q) => $q->wherePivot('is_primary', true),
            'createdBy.employee:id,employee_name',
        ])->select('attendance_logs.*');

        if (!$this->applyScope($query, $user, 'attendance_logs.employee_id', $canManage, $canSpvManager, $canView)) {
            return DataTables::of(collect())->with(['stats' => null])->make(true);
        }

        if ($request->filled('date_from')) $query->where('attendance_logs.work_date', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->where('attendance_logs.work_date', '<=', $request->date_to);
        if ($request->filled('type'))      $query->where('attendance_logs.type', $request->type);
        if ($request->filled('status'))    $query->where('attendance_logs.status', $request->status);
        // Entri manual tidak punya koordinat GPS
        if ($request->source === 'manual') $query->whereNull('attendance_logs.latitude');
        if ($request->source === 'app')    $query->whereNotNull('attendance_logs.latitude');
        // Absen karyawan bebas lokasi (location_flexible) yang dilakukan di luar radius store
        if ($request->source === 'offsite') $query->where('attendance_logs.is_offsite', true);

        if ($canManage) {
            if ($request->filled('company_id')) {
                $query->whereHas('employee', fn($q) => $q->where('company_id', $request->company_id));
            }
            if ($request->filled('department_id')) {
                $query->whereExists(fn($q) => $q->select(DB::raw(1))->from('employee_departments')
                    ->whereColumn('employee_departments.employee_id', 'attendance_logs.employee_id')
                    ->where('employee_departments.department_id', $request->department_id)
                    ->where('employee_departments.is_primary', true));
            }
            if ($request->filled('store_id')) $query->where('attendance_logs.store_id', $request->store_id);
        } elseif ($request->filled('store_id')) {
            $query->where('attendance_logs.store_id', $request->store_id);
        }
        if ($request->filled('employee_id')) {
            $query->where('attendance_logs.employee_id', $request->employee_id);
        }

        // Ringkasan untuk filter yang sama (sebelum paging)
        $base = clone (clone $query)->getQuery();
        $base->columns = null;
        $base->orders  = null;
        $s = $base->selectRaw("COUNT(*) as total,
                SUM(attendance_logs.type = 'checkin') as checkin,
                SUM(attendance_logs.type = 'checkout') as checkout,
                SUM(attendance_logs.status = 'pending') as pending,
                SUM(attendance_logs.status = 'approved') as approved,
                SUM(attendance_logs.status = 'approved_hr') as approved_hr,
                SUM(attendance_logs.status = 'flagged') as flagged,
                SUM(attendance_logs.latitude IS NULL) as manual_entries,
                SUM(attendance_logs.is_offsite = 1) as offsite,
                SUM(attendance_logs.status = 'pending' AND (attendance_logs.created_by IS NULL OR attendance_logs.created_by != ?)) as pending_checkable,
                COUNT(DISTINCT attendance_logs.employee_id) as employees", [$user->id])
            ->first();
        $stats = collect((array) $s)->map(fn($v) => (int) $v);

        return DataTables::eloquent($query)
            ->addColumn('hash', fn($log) => self::hashId($log->id))
            ->addColumn('employee_name', fn($log) => $log->employee?->employee_name ?? '-')
            ->addColumn('employee_pengenal', fn($log) => $log->employee?->employee_pengenal ?? '-')
            ->addColumn('company_name', fn($log) => $log->employee?->company?->name ?? '-')
            ->addColumn('department_name', fn($log) => $log->employee?->department?->first()?->department_name ?? '-')
            ->addColumn('store_name', fn($log) => $log->store?->name ?? 'Work From Anywhere')
            ->addColumn('is_manual', fn($log) => $log->latitude === null)
            // Label blok hanya untuk sesi 2 split shift; sesi 1 tampil seperti absen biasa
            ->addColumn('is_split_session', fn($log) => (int) $log->session > 1)
            ->addColumn('attendance_mode_label', fn($log) => Employee::getAttendanceTypeLabels()[$log->attendance_mode] ?? null)
            ->addColumn('submitted_by', fn($log) => $log->createdBy?->employee?->employee_name ?? $log->createdBy?->username)
            ->addColumn('is_mine', fn($log) => $log->created_by === $user->id)
            // Tingkat persetujuan yang bisa dilakukan user ini atas baris ini (untuk checkbox bulk)
            ->addColumn('approve_level', fn($log) => $this->approvalLevelFor($log, $user, $canManage, $canSpvManager))
            ->addColumn('logged_time', fn($log) => optional($log->logged_at)->timezone(config('app.timezone'))->format('H:i'))
            ->addColumn('logged_date', fn($log) => optional($log->logged_at)->timezone(config('app.timezone'))->toDateString())
            ->addColumn('logged_full', fn($log) => optional($log->logged_at)->timezone(config('app.timezone'))->format('d M Y H:i'))
            ->editColumn('work_date', fn($log) => $log->work_date ? Carbon::parse($log->work_date)->toDateString() : null)
            ->editColumn('distance_from_store', fn($log) => $log->distance_from_store !== null ? (float) $log->distance_from_store : null)
            ->addColumn('geofence_radius', fn($log) => $log->store?->geofence_radius)
            ->addColumn('show_url', fn($log) => route('attendancemobile.show', self::hashId($log->id)))
            ->removeColumn('photo_path', 'device_id', 'latitude', 'longitude', 'employee', 'store', 'created_by', 'checked_by', 'hr_approved_by')
            ->filterColumn('employee_name', fn($q, $k) => $q->whereHas('employee', fn($e) => $e->where('employee_name', 'like', "%{$k}%")->orWhere('employee_pengenal', 'like', "%{$k}%")))
            ->orderColumn('employee_name', fn($q, $o) => $q->orderBy(Employee::select('employee_name')->whereColumn('employees_tables.id', 'attendance_logs.employee_id'), $o))
            ->orderColumn('store_name', fn($q, $o) => $q->orderBy(Stores::select('name')->whereColumn('stores_tables.id', 'attendance_logs.store_id'), $o))
            ->with(['stats' => $stats])
            ->make(true);
    }

    /** Entri manual. HR bisa langsung final (approved_hr); selain HR selalu masuk pending (menunggu checker). */
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
            'session'     => 'nullable|in:1,2',
            'work_date'   => 'required|date|before_or_equal:today',
            'logged_at'   => 'required|date',
            'flag_reason' => 'required|string|min:5|max:255',
            // Opsional; foto dikecilkan sebelum disimpan (ImageCompressor)
            'photo_path'  => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ];
        if ($canManage) $rules['status'] = 'required|in:' . AttendanceLog::STATUS_APPROVED_HR . ',' . AttendanceLog::STATUS_PENDING;

        $validated = $request->validate($rules, [
            'work_date.before_or_equal' => 'Tanggal kerja tidak boleh di masa depan.',
            'flag_reason.required'      => 'Alasan wajib diisi.',
            'photo_path.mimes'          => 'Bukti harus berupa foto (jpg, png, webp) atau pdf.',
            'photo_path.max'            => 'Ukuran bukti maksimal 10 MB.',
        ]);

        $validated['session'] = (int) ($validated['session'] ?? 1);

        $target = Employee::select('id', 'attendance_type')->find($validated['employee_id']);
        if ($target?->isAttendanceExempt()) {
            return response()->json(['success' => false, 'message' => 'Karyawan ini bertipe tidak wajib absen, entri absensi tidak diperlukan.'], 422);
        }

        // Selain HR hanya untuk karyawan dalam cakupannya (pengaju View: dirinya sendiri)
        if (!$canManage) {
            $q = Employee::where('employees_tables.id', $validated['employee_id']);
            if (!$this->applyScope($q, $user, 'employees_tables.id', false, $canSpvManager, $canView) || !$q->exists()) {
                return response()->json(['success' => false, 'message' => $canSpvManager ? 'Karyawan ini di luar cakupan Anda.' : 'Anda hanya bisa mengajukan entri untuk diri sendiri.'], 403);
            }
        }

        // Lokasi wajib dan harus salah satu lokasi karyawan itu. Tanpa lokasi (Work From Anywhere)
        // hanya untuk karyawan bertipe bebas lokasi, sama dengan aturan absen di aplikasi.
        $storeIds = DB::table('employee_stores')->where('employee_id', $validated['employee_id'])->pluck('store_id');
        if (empty($validated['store_id'])) {
            if ($target?->attendance_type !== Employee::ATTENDANCE_LOCATION_FLEXIBLE) {
                return response()->json(['success' => false, 'message' => 'Lokasi wajib dipilih.', 'errors' => ['store_id' => ['Lokasi wajib dipilih.']]], 422);
            }
        } elseif (!$storeIds->contains($validated['store_id'])) {
            return response()->json(['success' => false, 'message' => 'Lokasi ini bukan lokasi kerja karyawan tersebut.', 'errors' => ['store_id' => ['Pilih salah satu lokasi kerja karyawan ini.']]], 422);
        }

        // Jam absen harus di tanggal kerja, atau dini hari besoknya (shift malam)
        $loggedAt = Carbon::parse($validated['logged_at'], config('app.timezone'));
        $workDate = Carbon::parse($validated['work_date'], config('app.timezone'))->startOfDay();
        if ($loggedAt->lt($workDate) || $loggedAt->gte($workDate->copy()->addDays(2)) || $loggedAt->isFuture()) {
            return response()->json(['success' => false, 'message' => 'Jam absen harus di tanggal kerja atau keesokan harinya (shift malam), dan tidak di masa depan.'], 422);
        }

        // Cut off tanggal 25: pengaju (View) dan checker (SPV/manager) hanya untuk periode berjalan.
        // HR tidak dibatasi supaya tetap bisa mengoreksi periode yang sudah tutup.
        if (!$canManage) {
            [$periodStart, $periodEnd] = self::currentPeriod();
            $period = $periodStart->locale('id')->translatedFormat('d M Y') . ' sampai ' . $periodEnd->locale('id')->translatedFormat('d M Y');
            $errors = [];
            $outside = $workDate->lt($periodStart) || $workDate->gt($periodEnd) || $loggedAt->lt($periodStart) || $loggedAt->gt($periodEnd);
            if ($outside && $this->isCutoffNightCheckout($validated['employee_id'], $validated['type'], $workDate, $loggedAt, $periodStart)) {
                $outside = false; // shift malam tanggal 25, check-out dini hari tanggal 26
            }
            if ($outside && ($workDate->lt($periodStart) || $workDate->gt($periodEnd))) {
                $errors['work_date'] = ["Tanggal kerja harus di periode berjalan, {$period}."];
            }
            if ($outside && ($loggedAt->lt($periodStart) || $loggedAt->gt($periodEnd))) {
                $errors['logged_at'] = ["Jam absen harus di periode berjalan, {$period}."];
            }
            if ($errors) {
                return response()->json([
                    'success' => false,
                    'message' => "Entri manual hanya bisa untuk periode berjalan, {$period}. Periode sebelumnya sudah cut off, hubungi HR."
                        . ' (Pengecualian: check-out shift malam tanggal 25 masih bisa diajukan pada tanggal 26.)',
                    'errors'  => $errors,
                ], 422);
            }
        }

        // Blok 2 hanya ada di split shift
        if ($validated['session'] === 2) {
            $rosterShift = Roster::with('shift')->where('employee_id', $validated['employee_id'])
                ->whereDate('date', $validated['work_date'])->where('day_type', 'Work')->first()?->shift;
            if (!$rosterShift?->isSplit()) {
                return response()->json(['success' => false, 'message' => 'Blok 2 hanya untuk jadwal split shift. Roster karyawan ini di tanggal tersebut bukan split shift.', 'errors' => ['session' => ['Pilih blok 1 untuk shift biasa.']]], 422);
            }
        }

        $status = $canManage ? $validated['status'] : AttendanceLog::STATUS_PENDING;

        // Bukti opsional. Foto dikecilkan dulu, lalu diunggah di luar transaksi supaya lock tidak menahan durasi upload.
        $photoPath = null;
        if ($file = $request->file('photo_path')) {
            $packed = app(ImageCompressor::class)->compress($file);
            if ($packed['extension'] === 'pdf' && $packed['bytes'] > 5 * 1024 * 1024) {
                return response()->json(['success' => false, 'message' => 'File PDF maksimal 5 MB.', 'errors' => ['photo_path' => ['File PDF maksimal 5 MB.']]], 422);
            }
            $photoPath = 'attendance-manual/' . now()->format('Y/m') . '/' . \Ramsey\Uuid\Uuid::uuid7()->toString() . '.' . $packed['extension'];
            try {
                Storage::disk('s3')->put($photoPath, $packed['contents'], ['ContentType' => $packed['mime']]);
            } catch (\Throwable $e) {
                Log::error('AttendanceMobile store: photo upload failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
                return response()->json(['success' => false, 'message' => 'Gagal mengunggah foto/surat pendukung.'], 500);
            }
        }
        $deleteUpload = fn() => $photoPath ? Storage::disk('s3')->delete($photoPath) : null;

        try {
            $log = DB::transaction(function () use ($validated, $status, $photoPath, $loggedAt, $user) {
                $duplicate = AttendanceLog::where('employee_id', $validated['employee_id'])
                    ->where('work_date', $validated['work_date'])
                    ->where('type', $validated['type'])
                    ->where('session', $validated['session'])
                    ->lockForUpdate()->exists();
                if ($duplicate) {
                    throw new \App\Exceptions\DuplicateAttendanceException('Sudah ada ' . ($validated['type'] === 'checkin' ? 'check-in' : 'check-out')
                        . ($validated['session'] > 1 ? " blok {$validated['session']}" : '') . ' untuk karyawan dan tanggal ini.');
                }
                $final = $status === AttendanceLog::STATUS_APPROVED_HR;
                return AttendanceLog::create([
                    'employee_id'         => $validated['employee_id'],
                    'store_id'            => $validated['store_id'] ?? null,
                    'type'                => $validated['type'],
                    'session'             => $validated['session'],
                    'attendance_mode'     => 'manual',
                    'work_date'           => $validated['work_date'],
                    'logged_at'           => $loggedAt,
                    'status'              => $status,
                    'flag_reason'         => $validated['flag_reason'],
                    'photo_path'          => $photoPath,
                    'is_within_geofence'  => true,
                    'is_mock_location'    => false,
                    'liveness_passed'     => true,
                    'created_by'          => $user->id,
                    // HR yang langsung memfinalkan dianggap sekaligus checker
                    'checked_by'          => $final ? $user->id : null,
                    'checked_at'          => $final ? now() : null,
                    'hr_approved_by'      => $final ? $user->id : null,
                    'hr_approved_at'      => $final ? now() : null,
                ]);
            });
        } catch (\App\Exceptions\DuplicateAttendanceException $e) {
            $deleteUpload();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            $deleteUpload();
            if ($e->getCode() === '23000') {
                return response()->json(['success' => false, 'message' => 'Sudah ada absen dengan tipe ini untuk karyawan dan tanggal tersebut.'], 422);
            }
            Log::error('AttendanceMobile store: query exception', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            throw $e;
        }

        $this->logActivity($user, $log, 'created', 'Entri absensi manual diajukan', [
            'attributes' => array_merge($validated, ['status' => $status, 'photo_path' => $photoPath]),
        ]);

        return response()->json([
            'success' => true,
            'message' => $status === AttendanceLog::STATUS_PENDING
                ? 'Entri tersimpan dan menunggu dicek atasan, lalu disetujui HR.'
                : 'Entri absensi tersimpan dan langsung disetujui HR.',
        ]);
    }

    /**
     * Setujui banyak log sekaligus.
     * level=checker : pending → approved  (ManageAttendanceMobileSPVManager, dalam cakupan, bukan entri sendiri)
     * level=hr      : approved → approved_hr (ManageAttendanceMobile)
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
        ], ['hashes.max' => 'Maksimal ' . self::BULK_MAX . ' absensi sekali setujui.']);

        $isHr = $data['level'] === 'hr';
        if ($isHr && !$canManage) abort(403, 'Hanya HR yang bisa memberi persetujuan akhir.');
        if (!$isHr && !$canSpvManager) abort(403, 'Hanya checker (SPV/manager) yang bisa menyetujui tahap pertama.');

        $from = $isHr ? AttendanceLog::STATUS_APPROVED : AttendanceLog::STATUS_PENDING;
        $to   = $isHr ? AttendanceLog::STATUS_APPROVED_HR : AttendanceLog::STATUS_APPROVED;

        // Kandidat: status asal yang benar dan dalam cakupan; hash dicocokkan di sini
        $candidates = AttendanceLog::query()->select('attendance_logs.id')->where('attendance_logs.status', $from);
        if (!$isHr) {
            if (!$this->applyScope($candidates, $user, 'attendance_logs.employee_id', false, true, false)) {
                return response()->json(['success' => false, 'message' => 'Anda tidak punya cakupan karyawan.'], 403);
            }
            $candidates->where(fn($q) => $q->whereNull('attendance_logs.created_by')->orWhere('attendance_logs.created_by', '!=', $user->id));
        }
        $wanted = array_flip($data['hashes']);
        $ids = $candidates->pluck('attendance_logs.id')->filter(fn($id) => isset($wanted[self::hashId($id)]))->values();

        $approved = 0;
        DB::transaction(function () use ($ids, $from, $to, $isHr, $user, &$approved) {
            foreach (AttendanceLog::whereIn('id', $ids)->lockForUpdate()->get() as $log) {
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
                ? "{$approved} absensi " . ($isHr ? 'disetujui HR' : 'disetujui dan diteruskan ke HR') . '.' . ($skipped ? " {$skipped} dilewati karena statusnya sudah berubah" . ($isHr ? '.' : ' atau ajuan Anda sendiri.') : '')
                : 'Tidak ada absensi yang bisa disetujui. Statusnya mungkin sudah berubah' . ($isHr ? '.' : ', atau itu ajuan Anda sendiri yang harus dicek checker lain.'),
        ]);
    }

    /** Cari log dari hash tanpa memuat seluruh tabel. */
    private function findByHash(string $hash): ?AttendanceLog
    {
        foreach (AttendanceLog::query()->select('id')->orderByDesc('logged_at')->cursor() as $row) {
            if (hash_equals(self::hashId($row->id), $hash)) {
                return AttendanceLog::with(['employee', 'store', 'createdBy.employee', 'checkedBy.employee', 'hrApprovedBy.employee'])->find($row->id);
            }
        }
        return null;
    }

    private function canSeeLog($user, AttendanceLog $log, bool $canManage, bool $canSpvManager, bool $canView): bool
    {
        if ($canManage) return true;
        $q = AttendanceLog::where('attendance_logs.id', $log->id);
        return $this->applyScope($q, $user, 'attendance_logs.employee_id', false, $canSpvManager, $canView) && $q->exists();
    }

    public function show(string $hash)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        [$canManage, $canSpvManager, $canView] = $this->perms($user);
        if (!$canManage && !$canSpvManager && !$canView) abort(403, 'Unauthorized');

        $log = $this->findByHash($hash);
        if (!$log) abort(404, 'Log absensi tidak ditemukan.');
        if (!$this->canSeeLog($user, $log, $canManage, $canSpvManager, $canView)) abort(403, 'Anda tidak punya akses ke log absensi ini.');

        $photoUrl = null;
        $isPdf    = $log->photo_path && str_ends_with(strtolower($log->photo_path), '.pdf');
        if ($log->photo_path) {
            try {
                $photoUrl = Storage::disk('s3')->temporaryUrl($log->photo_path, now()->addMinutes(10));
            } catch (\Throwable $e) {
                Log::warning('ATTENDANCE_PHOTO_URL_FAILED', ['log_id' => $log->id, 'error' => $e->getMessage()]);
            }
        }

        // Pasangan check-in/check-out di tanggal kerja yang sama, dan roster hari itu
        $pair = AttendanceLog::where('employee_id', $log->employee_id)
            ->where('work_date', $log->work_date)
            ->where('session', $log->session ?? 1)
            ->where('id', '!=', $log->id)
            ->first();
        $pairUrl = $pair ? route('attendancemobile.show', self::hashId($pair->id)) : null;

        $roster = Roster::with('shift:id,shift_name,start_time,end_time,is_split,break_start,break_end')
            ->where('employee_id', $log->employee_id)->whereDate('date', $log->work_date)->first();

        $history = Activity::with('causer.employee')
            ->where('subject_type', AttendanceLog::class)->where('subject_id', $log->id)
            ->latest()->limit(10)->get();

        return view('pages.AttendanceMobile.show', [
            'log'           => $log,
            'hash'          => $hash,
            'photoUrl'      => $photoUrl,
            'isPdf'         => $isPdf,
            'pair'          => $pair,
            'pairUrl'       => $pairUrl,
            'roster'        => $roster,
            'history'       => $history,
            'isManual'      => $log->latitude === null,
            'canManage'     => $canManage,
            'approveLevel'  => $this->approvalLevelFor($log, $user, $canManage, $canSpvManager),
            'isOwnEntry'    => $log->created_by === $user->id,
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
            'status' => 'required|in:' . implode(',', AttendanceLog::STATUSES),
            'note'   => 'nullable|string|max:255|required_if:status,flagged',
        ], ['note.required_if' => 'Alasan wajib diisi saat menandai absensi.']);

        $log = $this->findByHash($hash);
        if (!$log) abort(404, 'Log absensi tidak ditemukan.');

        if (!$canManage) {
            $checkerOk = $data['status'] === AttendanceLog::STATUS_APPROVED
                && $this->approvalLevelFor($log, $user, false, $canSpvManager) === 'checker'
                && $this->canSeeLog($user, $log, false, $canSpvManager, $canView);
            if (!$checkerOk) abort(403, 'Anda hanya bisa menyetujui absensi yang menunggu checker, dan bukan ajuan Anda sendiri.');
        }

        $old = $log->status;
        $new = $data['status'];
        $update = [
            'status'      => $new,
            // Alasan asli (mis. entri manual) tetap disimpan, catatan HR ditambahkan
            'flag_reason' => $new === AttendanceLog::STATUS_FLAGGED
                ? trim(($log->flag_reason ? $log->flag_reason . ' | ' : '') . 'Ditandai HR: ' . $data['note'])
                : $log->flag_reason,
        ];
        if ($new === AttendanceLog::STATUS_APPROVED && $old === AttendanceLog::STATUS_PENDING) {
            $update += ['checked_by' => $user->id, 'checked_at' => now()];
        }
        if ($new === AttendanceLog::STATUS_APPROVED_HR) {
            $update += ['hr_approved_by' => $user->id, 'hr_approved_at' => now()];
            if (!$log->checked_by && $log->created_by) $update += ['checked_by' => $user->id, 'checked_at' => now()];
        }
        // Dikembalikan ke tahap sebelumnya: persetujuan tahap sesudahnya dibatalkan
        if (in_array($new, [AttendanceLog::STATUS_PENDING, AttendanceLog::STATUS_APPROVED, AttendanceLog::STATUS_FLAGGED], true)) {
            $update += ['hr_approved_by' => null, 'hr_approved_at' => null];
        }
        if ($new === AttendanceLog::STATUS_PENDING) {
            $update['checked_by'] = null;
            $update['checked_at'] = null;
        }
        $log->update($update);

        $labels = AttendanceLog::STATUS_LABELS;
        $this->logActivity($user, $log, 'updated', 'Status absensi diubah dari ' . ($labels[$old] ?? $old) . ' ke ' . ($labels[$new] ?? $new), [
            'old' => ['status' => $old], 'attributes' => ['status' => $new, 'note' => $data['note'] ?? null],
        ]);

        return back()->with('success', 'Status absensi diperbarui.');
    }

    /** Riwayat aktivitas absensi mobile (siapa mengajukan, menyetujui, menandai). Hanya HR. */
    public function getActivities(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (!$user->hasPermissionTo('ManageAttendanceMobile')) abort(403);

        // 'attendance' = nama log lama sebelum diganti ke 'attendance-mobile'
        $query = Activity::whereIn('log_name', [AttendanceLog::LOG_NAME, 'attendance'])
            ->where('subject_type', AttendanceLog::class)
            ->with(['causer.employee', 'subject.employee:id,employee_name,employee_pengenal'])
            ->select('activity_log.*');

        $labels = AttendanceLog::STATUS_LABELS;

        return DataTables::eloquent($query)
            ->addColumn('time', fn($a) => $a->created_at->timezone(config('app.timezone'))->locale('id')->translatedFormat('d M Y, H:i'))
            ->addColumn('causer_name', fn($a) => $a->causer?->employee?->employee_name ?? $a->causer?->username ?? 'Sistem')
            ->addColumn('employee_name', fn($a) => $a->subject?->employee?->employee_name ?? '-')
            ->addColumn('work_date', fn($a) => $a->subject?->work_date ? Carbon::parse($a->subject->work_date)->locale('id')->translatedFormat('d M Y') : null)
            ->addColumn('type', fn($a) => $a->subject?->type)
            ->addColumn('status_from', fn($a) => isset($a->properties['old']['status']) ? ($labels[$a->properties['old']['status']] ?? $a->properties['old']['status']) : null)
            ->addColumn('status_to', fn($a) => isset($a->properties['attributes']['status']) ? ($labels[$a->properties['attributes']['status']] ?? $a->properties['attributes']['status']) : null)
            ->addColumn('note', fn($a) => $a->properties['attributes']['note'] ?? null)
            ->addColumn('show_url', fn($a) => $a->subject ? route('attendancemobile.show', self::hashId($a->subject_id)) : null)
            ->removeColumn('properties', 'subject', 'causer')
            ->filter(function ($q) use ($request) {
                $k = trim((string) ($request->input('search.value') ?? ''));
                if ($k === '') return;
                $q->where(function ($w) use ($k) {
                    $w->where('activity_log.description', 'like', "%{$k}%")
                        ->orWhereHasMorph('causer', [\App\Models\User::class], fn($c) => $c->where('username', 'like', "%{$k}%")
                            ->orWhereHas('employee', fn($e) => $e->where('employee_name', 'like', "%{$k}%")))
                        ->orWhereHasMorph('subject', [AttendanceLog::class], fn($s) => $s->whereHas('employee', fn($e) => $e->where('employee_name', 'like', "%{$k}%")));
                });
            })
            ->orderColumn('time', fn($q, $o) => $q->orderBy('activity_log.created_at', $o)->orderBy('activity_log.id', $o))
            ->make(true);
    }
}
