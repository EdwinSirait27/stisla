<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Company;
use App\Models\Departments;
use App\Models\Employee;
use App\Models\Roster;
use App\Models\Stores;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

/**
 * Log absensi dari aplikasi mobile (selfie + GPS), plus entri manual.
 *
 * Akses:
 * - ManageAttendanceMobile           : semua karyawan, semua filter, bisa setujui/tandai
 * - ManageAttendanceMobileSPVManager : store+departemen yang sama + bawahan langsung, entri manual masuk pending
 * - ViewAttendanceMobile             : rekan di store & departemen utama yang sama, hanya lihat
 */
class AttendanceMobileController extends Controller
{
    private function perms($user): array
    {
        return [
            $user->hasPermissionTo('ManageAttendanceMobile'),
            $user->hasPermissionTo('ManageAttendanceMobileSPVManager'),
            $user->hasPermissionTo('ViewAttendanceMobile'),
        ];
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
        } elseif ($canSpvManager && $user->employee) {
            $stores = Stores::whereIn('id', $user->employee->store()->pluck('stores_tables.id'))->select('id', 'name')->orderBy('name')->get();
        }

        if ($canManage || $canSpvManager) {
            $q = Employee::select('id', 'employee_name', 'employee_pengenal')->whereNull('deleted_at')
                ->whereIn('status', ['Active', 'Pending', 'On Leave', 'Mutation'])->orderBy('employee_name');
            $employees = $this->applyScope($q, $user, 'employees_tables.id', $canManage, $canSpvManager, false) ? $q->get() : collect();
        }

        $first = today()->startOfMonth();
        [$defaultFrom, $defaultTo] = today()->day >= 26
            ? [today()->day(26)->toDateString(), $first->copy()->addMonthNoOverflow()->day(25)->toDateString()]
            : [$first->copy()->subMonthNoOverflow()->day(26)->toDateString(), today()->day(25)->toDateString()];

        return view('pages.AttendanceMobile.index', compact('stores', 'employees', 'departments', 'companies', 'canManage', 'canSpvManager', 'canView', 'defaultFrom', 'defaultTo'));
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
            'status'    => 'nullable|in:approved,pending,flagged',
            'source'    => 'nullable|in:app,manual',
        ]);

        $query = AttendanceLog::with([
            'employee:id,employee_name,employee_pengenal,company_id,status_employee',
            'store:id,name,geofence_radius',
            'employee.company:id,name',
            'employee.department' => fn($q) => $q->wherePivot('is_primary', true),
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
        } elseif ($canSpvManager && $request->filled('store_id')) {
            $query->where('attendance_logs.store_id', $request->store_id);
        }
        if (($canManage || $canSpvManager) && $request->filled('employee_id')) {
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
                SUM(attendance_logs.status = 'flagged') as flagged,
                SUM(attendance_logs.latitude IS NULL) as manual_entries,
                COUNT(DISTINCT attendance_logs.employee_id) as employees")
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
            ->addColumn('logged_time', fn($log) => optional($log->logged_at)->timezone('Asia/Makassar')->format('H:i'))
            ->addColumn('logged_date', fn($log) => optional($log->logged_at)->timezone('Asia/Makassar')->toDateString())
            ->addColumn('logged_full', fn($log) => optional($log->logged_at)->timezone('Asia/Makassar')->format('d M Y H:i'))
            ->editColumn('work_date', fn($log) => $log->work_date ? Carbon::parse($log->work_date)->toDateString() : null)
            ->editColumn('distance_from_store', fn($log) => $log->distance_from_store !== null ? (float) $log->distance_from_store : null)
            ->addColumn('geofence_radius', fn($log) => $log->store?->geofence_radius)
            ->addColumn('show_url', fn($log) => route('attendancemobile.show', self::hashId($log->id)))
            ->removeColumn('photo_path', 'device_id', 'latitude', 'longitude', 'employee', 'store')
            ->filterColumn('employee_name', fn($q, $k) => $q->whereHas('employee', fn($e) => $e->where('employee_name', 'like', "%{$k}%")->orWhere('employee_pengenal', 'like', "%{$k}%")))
            ->orderColumn('employee_name', fn($q, $o) => $q->orderBy(Employee::select('employee_name')->whereColumn('employees_tables.id', 'attendance_logs.employee_id'), $o))
            ->orderColumn('store_name', fn($q, $o) => $q->orderBy(Stores::select('name')->whereColumn('stores_tables.id', 'attendance_logs.store_id'), $o))
            ->with(['stats' => $stats])
            ->make(true);
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        [$canManage, $canSpvManager] = $this->perms($user);
        if (!$canManage && !$canSpvManager) abort(403, 'Unauthorized');

        $rules = [
            'employee_id' => 'required|exists:employees_tables,id',
            'store_id'    => 'nullable|exists:stores_tables,id',
            'type'        => 'required|in:checkin,checkout',
            'work_date'   => 'required|date|before_or_equal:today',
            'logged_at'   => 'required|date',
            'flag_reason' => 'required|string|min:5|max:255',
            'photo_path'  => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
        if ($canManage) $rules['status'] = 'required|in:approved,pending';

        $validated = $request->validate($rules, [
            'work_date.before_or_equal' => 'Tanggal kerja tidak boleh di masa depan.',
            'flag_reason.required'      => 'Alasan wajib diisi.',
            'photo_path.required'       => 'Foto atau surat pendukung wajib diunggah.',
        ]);

        // SPV hanya untuk karyawan dalam cakupannya
        if (!$canManage) {
            $q = Employee::where('employees_tables.id', $validated['employee_id']);
            if (!$this->applyScope($q, $user, 'employees_tables.id', false, true, false) || !$q->exists()) {
                return response()->json(['success' => false, 'message' => 'Karyawan ini di luar cakupan Anda.'], 403);
            }
            if (!empty($validated['store_id']) && !$user->employee->store()->where('stores_tables.id', $validated['store_id'])->exists()) {
                return response()->json(['success' => false, 'message' => 'Lokasi ini bukan lokasi Anda.'], 403);
            }
        }

        // Jam absen harus di tanggal kerja, atau dini hari besoknya (shift malam)
        $loggedAt = Carbon::parse($validated['logged_at'], 'Asia/Makassar');
        $workDate = Carbon::parse($validated['work_date'], 'Asia/Makassar')->startOfDay();
        if ($loggedAt->lt($workDate) || $loggedAt->gte($workDate->copy()->addDays(2)) || $loggedAt->isFuture()) {
            return response()->json(['success' => false, 'message' => 'Jam absen harus di tanggal kerja atau keesokan harinya (shift malam), dan tidak di masa depan.'], 422);
        }

        $status = $canManage ? $validated['status'] : 'pending';

        // Upload di luar transaksi supaya lock tidak menahan durasi upload
        $file      = $request->file('photo_path');
        $filename  = 'attendance-manual/' . now()->format('Y/m') . '/' . \Ramsey\Uuid\Uuid::uuid7()->toString() . '.' . strtolower($file->getClientOriginalExtension());
        try {
            $photoPath = $file->storeAs('', $filename, 's3');
        } catch (\Throwable $e) {
            Log::error('AttendanceMobile store: photo upload failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Gagal mengunggah foto/surat pendukung.'], 500);
        }

        try {
            $log = DB::transaction(function () use ($validated, $status, $photoPath, $loggedAt) {
                $duplicate = AttendanceLog::where('employee_id', $validated['employee_id'])
                    ->where('work_date', $validated['work_date'])
                    ->where('type', $validated['type'])
                    ->lockForUpdate()->exists();
                if ($duplicate) {
                    throw new \App\Exceptions\DuplicateAttendanceException('Sudah ada ' . ($validated['type'] === 'checkin' ? 'check-in' : 'check-out') . ' untuk karyawan dan tanggal ini.');
                }
                return AttendanceLog::create([
                    'employee_id'         => $validated['employee_id'],
                    'store_id'            => $validated['store_id'] ?? null,
                    'type'                => $validated['type'],
                    'work_date'           => $validated['work_date'],
                    'logged_at'           => $loggedAt,
                    'status'              => $status,
                    'flag_reason'         => $validated['flag_reason'],
                    'photo_path'          => $photoPath,
                    'is_within_geofence'  => true,
                    'is_mock_location'    => false,
                    'liveness_passed'     => true,
                ]);
            });
        } catch (\App\Exceptions\DuplicateAttendanceException $e) {
            Storage::disk('s3')->delete($photoPath);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            Storage::disk('s3')->delete($photoPath);
            if ($e->getCode() === '23000') {
                return response()->json(['success' => false, 'message' => 'Sudah ada absen dengan tipe ini untuk karyawan dan tanggal tersebut.'], 422);
            }
            Log::error('AttendanceMobile store: query exception', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            throw $e;
        }

        activity('attendance')->causedBy($user)->performedOn($log)
            ->withProperties(['attributes' => array_merge($validated, ['status' => $status, 'photo_path' => $photoPath])])
            ->event('created')
            ->log('Entri absensi manual dibuat');

        return response()->json([
            'success' => true,
            'message' => $status === 'pending' ? 'Entri tersimpan dan menunggu persetujuan HR.' : 'Entri absensi tersimpan.',
        ]);
    }

    /** Cari log dari hash tanpa memuat seluruh tabel. */
    private function findByHash(string $hash): ?AttendanceLog
    {
        foreach (AttendanceLog::query()->select('id')->orderByDesc('logged_at')->cursor() as $row) {
            if (hash_equals(self::hashId($row->id), $hash)) {
                return AttendanceLog::with(['employee', 'store'])->find($row->id);
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
            ->where('id', '!=', $log->id)
            ->first();
        $pairUrl = $pair ? route('attendancemobile.show', self::hashId($pair->id)) : null;

        $roster = Roster::with('shift:id,shift_name,start_time,end_time')
            ->where('employee_id', $log->employee_id)->whereDate('date', $log->work_date)->first();

        $history = \Spatie\Activitylog\Models\Activity::with('causer.employee')
            ->where('subject_type', AttendanceLog::class)->where('subject_id', $log->id)
            ->latest()->limit(10)->get();

        return view('pages.AttendanceMobile.show', [
            'log'       => $log,
            'hash'      => $hash,
            'photoUrl'  => $photoUrl,
            'isPdf'     => $isPdf,
            'pair'      => $pair,
            'pairUrl'   => $pairUrl,
            'roster'    => $roster,
            'history'   => $history,
            'isManual'  => $log->latitude === null,
            'canManage' => $canManage,
        ]);
    }

    /** HR menyetujui / menandai / mengembalikan ke pending. */
    public function updateStatus(Request $request, string $hash)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (!$user->hasPermissionTo('ManageAttendanceMobile')) abort(403, 'Hanya HR yang bisa mengubah status absensi.');

        $data = $request->validate([
            'status' => 'required|in:approved,pending,flagged',
            'note'   => 'nullable|string|max:255|required_if:status,flagged',
        ], ['note.required_if' => 'Alasan wajib diisi saat menandai absensi.']);

        $log = $this->findByHash($hash);
        if (!$log) abort(404, 'Log absensi tidak ditemukan.');

        $old = $log->status;
        $log->update([
            'status'      => $data['status'],
            // Alasan asli (mis. entri manual) tetap disimpan, catatan HR ditambahkan
            'flag_reason' => $data['status'] === 'flagged'
                ? trim(($log->flag_reason ? $log->flag_reason . ' | ' : '') . 'Ditandai HR: ' . $data['note'])
                : $log->flag_reason,
        ]);

        activity('attendance')->causedBy($user)->performedOn($log)
            ->withProperties(['old' => ['status' => $old], 'attributes' => ['status' => $data['status'], 'note' => $data['note'] ?? null]])
            ->event('updated')
            ->log("Status absensi diubah dari {$old} ke {$data['status']}");

        return back()->with('success', 'Status absensi diperbarui.');
    }
}
