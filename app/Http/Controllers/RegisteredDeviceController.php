<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use App\Models\PushToken;
use App\Models\RegisteredDevice;
use App\Models\Stores;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

/**
 * Device mobile yang terdaftar per akun (satu akun, satu device aktif; lihat Api\AuthController@login
 * dan middleware CheckDeviceBinding). HR me-reset device saat karyawan ganti HP: device lama
 * dinonaktifkan, token login & push token-nya diputus, lalu login berikutnya mendaftarkan HP baru.
 *
 * Akses:
 * - ManageRegisteredDevice           : semua karyawan, bisa reset
 * - ManageRegisteredDeviceSPVManager : store+departemen yang sama + bawahan langsung, hanya lihat
 * - ViewRegisteredDevice             : rekan di store & departemen utama yang sama, hanya lihat
 */
class RegisteredDeviceController extends Controller
{
    private function perms($user): array
    {
        return [
            $user->can('ManageRegisteredDevice'),
            $user->can('ManageRegisteredDeviceSPVManager'),
            $user->can('ViewRegisteredDevice'),
        ];
    }

    private static function hashId(string $id): string
    {
        return substr(hash('sha256', 'device' . $id . config('app.key')), 0, 10);
    }

    /** Batasi query ke karyawan yang boleh dilihat. $col = kolom employee_id pada query. */
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

    /** Subquery login yang ditolak karena device berbeda, selama device ini aktif. */
    private function blockedSql(string $agg): string
    {
        $userClass = DB::getPdo()->quote(User::class);
        return "(SELECT {$agg} FROM activity_log al
            WHERE al.subject_type = {$userClass} AND al.subject_id = registered_devices.user_id AND al.event = 'login_blocked'
            AND al.created_at >= COALESCE(registered_devices.registered_at, registered_devices.created_at)
            AND (registered_devices.reset_at IS NULL OR al.created_at <= registered_devices.reset_at))";
    }

    /** Query dasar: device + akun + karyawan, dengan waktu terakhir dipakai dan percobaan login dari HP lain. */
    private function baseQuery()
    {
        $userClass = DB::getPdo()->quote(User::class);

        return RegisteredDevice::query()
            ->with('resetBy.employee:id,employee_name')
            ->join('users', 'users.id', '=', 'registered_devices.user_id')
            ->leftJoin('employees_tables', 'employees_tables.id', '=', 'users.employee_id')
            ->select('registered_devices.*', 'users.username', 'users.employee_id',
                'employees_tables.employee_name', 'employees_tables.employee_pengenal', 'employees_tables.company_id')
            // Token Sanctum hanya ada untuk device aktif (login memutus token lama)
            ->selectRaw("(SELECT MAX(pat.last_used_at) FROM personal_access_tokens pat
                    WHERE pat.tokenable_type = {$userClass} AND pat.tokenable_id = registered_devices.user_id) AS last_used_at")
            ->selectRaw($this->blockedSql('COUNT(*)') . ' AS blocked_count')
            ->selectRaw($this->blockedSql('MAX(al.created_at)') . ' AS last_blocked_at');
    }

    public function index()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        [$canManage, $canSpvManager, $canView] = $this->perms($user);
        if (!$canManage && !$canSpvManager && !$canView) abort(403, 'Unauthorized');

        $companies = $canManage ? Company::select('id', 'name')->orderBy('name')->get() : collect();
        $stores = $canManage
            ? Stores::select('id', 'name')->whereNotNull('name')->orderBy('name')->get()
            : ($canSpvManager && $user->employee
                ? Stores::whereIn('id', $user->employee->store()->pluck('stores_tables.id'))->select('id', 'name')->orderBy('name')->get()
                : collect());

        return view('pages.RegisteredDevice.index', compact('companies', 'stores', 'canManage', 'canSpvManager', 'canView'));
    }

    public function getRegisteredDevices(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        [$canManage, $canSpvManager, $canView] = $this->perms($user);
        if (!$canManage && !$canSpvManager && !$canView) abort(403, 'Unauthorized');

        $request->validate([
            'status'  => 'nullable|in:active,reset,blocked',
            'company' => 'nullable|string',
            'store'   => 'nullable|string',
        ]);

        $query = $this->baseQuery();
        if (!$this->applyScope($query, $user, 'users.employee_id', $canManage, $canSpvManager, $canView)) {
            return DataTables::of(collect())->with(['stats' => null])->make(true);
        }

        if ($canManage && $request->filled('company')) $query->where('employees_tables.company_id', $request->company);
        if (($canManage || $canSpvManager) && $request->filled('store')) {
            $query->whereExists(fn($q) => $q->select(DB::raw(1))->from('employee_stores')
                ->whereColumn('employee_stores.employee_id', 'users.employee_id')
                ->where('employee_stores.store_id', $request->store)->where('employee_stores.is_primary', true));
        }

        // Ringkasan sebelum filter status, supaya angka di atas tetap utuh saat filter dipakai
        $row = DB::query()->fromSub((clone $query)->getQuery(), 'd')->selectRaw("
                SUM(d.is_active = 1) AS active,
                SUM(d.reset_at >= ?) AS reset_month,
                SUM(d.is_active = 1 AND d.blocked_count > 0) AS blocked,
                SUM(d.is_active = 1 AND d.last_used_at IS NULL) AS never_used", [now()->startOfMonth()])
            ->first();
        $stats = collect((array) $row)->map(fn($v) => (int) $v);

        if ($request->status === 'active')  $query->where('registered_devices.is_active', true);
        if ($request->status === 'reset')   $query->where('registered_devices.is_active', false);
        if ($request->status === 'blocked') $query->where('registered_devices.is_active', true)->whereRaw($this->blockedSql('COUNT(*)') . ' > 0');

        $tz = config('app.timezone');
        $fmt = fn($v) => $v ? Carbon::parse($v)->timezone($tz)->format('d M Y H:i') : null;

        return DataTables::eloquent($query)
            ->addColumn('hash', fn($d) => self::hashId($d->id))
            ->addColumn('masked_device_id', fn($d) => $d->masked_device_id)
            ->editColumn('employee_name', fn($d) => $d->employee_name ?? $d->username ?? '-')
            ->editColumn('registered_at', fn($d) => $fmt($d->registered_at ?? $d->created_at))
            ->editColumn('last_used_at', fn($d) => $d->is_active ? $fmt($d->last_used_at) : null)
            ->editColumn('last_blocked_at', fn($d) => $fmt($d->last_blocked_at))
            ->editColumn('reset_at', fn($d) => $fmt($d->reset_at))
            ->editColumn('blocked_count', fn($d) => (int) $d->blocked_count)
            ->addColumn('reset_by_name', fn($d) => $d->reset_by ? ($d->resetBy?->employee?->employee_name ?? $d->resetBy?->username ?? '-') : null)
            ->addColumn('can_reset', fn($d) => $canManage && $d->is_active)
            ->removeColumn('device_id', 'user_id', 'reset_by', 'employee_id', 'company_id')
            ->filterColumn('employee_name', fn($q, $k) => $q->where(fn($w) => $w->where('employees_tables.employee_name', 'like', "%{$k}%")
                ->orWhere('employees_tables.employee_pengenal', 'like', "%{$k}%")
                ->orWhere('users.username', 'like', "%{$k}%")
                ->orWhere('registered_devices.device_name', 'like', "%{$k}%")))
            ->orderColumn('employee_name', fn($q, $o) => $q->orderBy('employees_tables.employee_name', $o))
            ->orderColumn('registered_at', fn($q, $o) => $q->orderBy('registered_devices.registered_at', $o))
            ->orderColumn('last_used_at', fn($q, $o) => $q->orderBy('last_used_at', $o))
            ->with(['stats' => $stats])
            ->make(true);
    }

    private function findByHash(string $hash): ?RegisteredDevice
    {
        foreach (RegisteredDevice::query()->select('id')->cursor() as $row) {
            if (hash_equals(self::hashId($row->id), $hash)) {
                return RegisteredDevice::with('user.employee')->find($row->id);
            }
        }
        return null;
    }

    /** Nonaktifkan device, putus token login & push token, supaya karyawan bisa login dari HP baru. */
    public function reset(Request $request, string $hash)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (!$user->can('ManageRegisteredDevice')) abort(403, 'Hanya HR yang bisa me-reset device.');

        $data = $request->validate([
            'reason' => 'required|string|min:5|max:255',
        ], ['reason.required' => 'Alasan reset wajib diisi.', 'reason.min' => 'Alasan minimal 5 karakter.']);

        $device = $this->findByHash($hash);
        if (!$device) return response()->json(['success' => false, 'message' => 'Device tidak ditemukan.'], 404);
        if (!$device->is_active) return response()->json(['success' => false, 'message' => 'Device ini sudah di-reset sebelumnya.'], 422);

        $owner = $device->user;
        DB::transaction(function () use ($device, $owner, $user, $data) {
            $device->update([
                'is_active'    => false,
                'reset_at'     => now(),
                'reset_by'     => $user->id,
                'reset_reason' => $data['reason'],
            ]);
            // Sesi aplikasi di HP lama langsung berakhir; notifikasi tidak dikirim lagi ke sana
            $owner?->tokens()->delete();
            if ($owner) PushToken::where('user_id', $owner->id)->update(['is_active' => false]);
        });

        $props = [
            'device_name'      => $device->device_name,
            'masked_device_id' => $device->masked_device_id,
            'reason'           => $data['reason'],
            'owner_user_id'    => $owner?->id,
        ];
        activity('device')->causedBy($user)->performedOn($device)->event('reset')
            ->withProperties($props)->log('Device mobile di-reset');
        if ($owner) {
            activity('auth')->causedBy($user)->performedOn($owner)->event('device_reset')
                ->withProperties($props + ['channel' => 'web'])->log('Device mobile di-reset oleh HR');
        }

        $name = $owner?->employee?->employee_name ?? $owner?->username ?? 'karyawan';
        return response()->json([
            'success' => true,
            'message' => "Device {$name} di-reset. Login berikutnya dari aplikasi akan mendaftarkan HP baru.",
        ]);
    }
}
