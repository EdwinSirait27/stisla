<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Daftar karyawan untuk aplikasi mobile, hanya baca. Cakupan sama dengan
 * EmployeeController@getEmployees di web:
 * - ManageEmployee           : semua karyawan
 * - ManageEmployeeSPVManager : store + departemen yang sama, ditambah bawahan langsung
 * - ViewEmployee             : rekan di store & departemen utama yang sama
 *
 * Data pribadi (NIK, KK, rekening, BPJS, alamat, tanggal lahir, dll.) tidak pernah dikirim ke
 * aplikasi. Kontak kerja, status kontrak, dan masa kerja hanya untuk ManageEmployee.
 * Foto karyawan sengaja tidak dikirim supaya daftar ringan dimuat; aplikasi menampilkan inisial.
 */
class EmployeeDirectoryController extends Controller
{
    private const ACTIVE_STATUSES = ['Active', 'Pending', 'On Leave', 'Mutation'];

    /** @return 'manage'|'spv'|'view'|null */
    public static function accessLevel(User $user): ?string
    {
        return match (true) {
            $user->can('ManageEmployee')           => 'manage',
            $user->can('ManageEmployeeSPVManager') => 'spv',
            $user->can('ViewEmployee')             => 'view',
            default                                => null,
        };
    }

    /** Batasi query Employee ke cakupan user. False kalau user tidak punya cakupan sama sekali. */
    private function applyScope($query, User $user, string $level): bool
    {
        if ($level === 'manage') return true;
        $me = $user->employee;
        if (!$me) return false;

        if ($level === 'spv') {
            $storeIds = $me->store()->pluck('stores_tables.id')->toArray();
            $deptIds  = $me->department()->pluck('departments_tables.id')->toArray();
            $bawahan  = $me->bawahanList()->pluck('employees_tables.id')->toArray();

            if (empty($storeIds) || empty($deptIds)) {
                if (empty($bawahan)) return false;
                $query->whereIn('employees_tables.id', $bawahan);
                return true;
            }
            $query->where(function ($q) use ($storeIds, $deptIds, $bawahan) {
                $q->where(function ($q1) use ($storeIds, $deptIds) {
                    $q1->whereExists(fn($sq) => $sq->select(DB::raw(1))->from('employee_stores')
                        ->whereColumn('employee_stores.employee_id', 'employees_tables.id')->whereIn('employee_stores.store_id', $storeIds))
                        ->whereExists(fn($sq) => $sq->select(DB::raw(1))->from('employee_departments')
                            ->whereColumn('employee_departments.employee_id', 'employees_tables.id')->whereIn('employee_departments.department_id', $deptIds));
                });
                if (!empty($bawahan)) $q->orWhereIn('employees_tables.id', $bawahan);
            });
            return true;
        }

        $storeId = $me->primaryStore()->first()?->id;
        $deptId  = $me->primaryDepartment()->first()?->id;
        if (!$storeId || !$deptId) return false;
        $query->whereExists(fn($q) => $q->select(DB::raw(1))->from('employee_stores')
            ->whereColumn('employee_stores.employee_id', 'employees_tables.id')->where('employee_stores.store_id', $storeId)->where('employee_stores.is_primary', true))
            ->whereExists(fn($q) => $q->select(DB::raw(1))->from('employee_departments')
                ->whereColumn('employee_departments.employee_id', 'employees_tables.id')->where('employee_departments.department_id', $deptId)->where('employee_departments.is_primary', true));
        return true;
    }

    private function denied()
    {
        return response()->json(['success' => false, 'message' => 'Anda tidak punya akses ke data karyawan', 'code' => 'NO_ACCESS'], 403);
    }

    private function primaryName($relation, string $column): ?string
    {
        $primary = $relation->first(fn($r) => (bool) $r->pivot?->is_primary) ?? $relation->first();
        return $primary?->{$column};
    }

    private function listItem(Employee $e): array
    {
        return [
            'id'          => $e->id,
            'name'        => $e->employee_name,
            'nip'         => $e->employee_pengenal,
            'position'    => $this->primaryName($e->position, 'name'),
            'department'  => $this->primaryName($e->department, 'department_name'),
            'store'       => $this->primaryName($e->store, 'name'),
            'company'     => $e->company?->name,
            'status'      => $e->status,
        ];
    }

    public function index(Request $request)
    {
        $user  = $request->user();
        $level = self::accessLevel($user);
        if (!$level) return $this->denied();

        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|in:active,inactive,all',
            'page'   => 'nullable|integer|min:1',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Filter tidak valid', 'errors' => $validator->errors()], 422);
        }

        $primary = fn($q) => $q->wherePivot('is_primary', true);
        $query = Employee::query()
            ->select('employees_tables.id', 'employee_name', 'employee_pengenal', 'status', 'company_id')
            ->with(['position' => $primary, 'department' => $primary, 'store' => $primary, 'company:id,name'])
            ->whereNull('employees_tables.deleted_at');

        if (!$this->applyScope($query, $user, $level)) {
            return response()->json(['success' => true, 'data' => ['access' => $level, 'items' => [], 'page' => 1, 'has_more' => false, 'total' => 0]]);
        }

        $status = $request->status ?? 'active';
        if ($status === 'active')   $query->whereIn('employees_tables.status', self::ACTIVE_STATUSES);
        if ($status === 'inactive') $query->whereNotIn('employees_tables.status', self::ACTIVE_STATUSES);

        if ($request->filled('search')) {
            $words = array_filter(explode(' ', trim($request->search)));
            $query->where(function ($q) use ($words, $request) {
                $q->where(function ($n) use ($words) {
                    foreach ($words as $w) $n->where('employee_name', 'like', "%{$w}%");
                })->orWhere('employee_pengenal', 'like', '%' . trim($request->search) . '%');
            });
        }

        $page = $query->orderBy('employee_name')->paginate(25);

        return response()->json([
            'success' => true,
            'data' => [
                'access'   => $level,
                'items'    => collect($page->items())->map(fn($e) => $this->listItem($e))->values(),
                'page'     => $page->currentPage(),
                'has_more' => $page->hasMorePages(),
                'total'    => $page->total(),
            ],
        ]);
    }

    public function show(Request $request, string $id)
    {
        $user  = $request->user();
        $level = self::accessLevel($user);
        if (!$level) return $this->denied();

        // Detail hanya untuk karyawan dalam cakupan (web show() belum membatasi ini)
        $scoped = Employee::where('employees_tables.id', $id)->whereNull('employees_tables.deleted_at');
        if (!$this->applyScope($scoped, $user, $level) || !$scoped->exists()) {
            return response()->json(['success' => false, 'message' => 'Karyawan tidak ditemukan atau di luar cakupan Anda', 'code' => 'NOT_FOUND'], 404);
        }

        $activeOnly = fn($q) => $q->whereIn('employees_tables.status', self::ACTIVE_STATUSES)->orderBy('employee_name')
            ->with(['position' => fn($p) => $p->wherePivot('is_primary', true)]);
        $e = Employee::with([
            'position', 'department', 'store', 'company:id,name', 'grading', 'group',
            'atasanList'  => fn($q) => $q->with(['position' => fn($p) => $p->wherePivot('is_primary', true)]),
            'bawahanList' => $activeOnly,
        ])->find($id);

        $assignments = fn($relation, string $column) => $relation
            // Selain ManageEmployee hanya melihat penempatan utama, sama dengan web
            ->filter(fn($r) => $level === 'manage' || (bool) $r->pivot?->is_primary)
            ->sortByDesc(fn($r) => (bool) $r->pivot?->is_primary)
            ->map(fn($r) => ['name' => $r->{$column}, 'is_primary' => (bool) $r->pivot?->is_primary])
            ->values();

        $person = fn(Employee $p) => [
            'id'        => $p->id,
            'name'      => $p->employee_name,
            'nip'       => $p->employee_pengenal,
            'position'  => $p->position->first()?->name,
        ];

        $data = $this->listItem($e) + [
            'positions'   => $assignments($e->position, 'name'),
            'departments' => $assignments($e->department, 'department_name'),
            'stores'      => $assignments($e->store, 'name'),
            'superiors'   => $e->atasanList->map($person)->values(),
            'subordinates' => $e->bawahanList->map($person)->values(),
            'work'        => null,
        ];

        if ($level === 'manage') {
            $join = $e->join_date ? Carbon::parse($e->join_date) : null;
            $diff = $join?->diff(now());
            $data['work'] = [
                'status_employee'   => $e->status_employee,
                'grading'           => $e->grading?->grading_name,
                'group'             => $e->group?->remark,
                'join_date'         => $join?->toDateString(),
                'end_date'          => $e->end_date ? Carbon::parse($e->end_date)->toDateString() : null,
                'length_of_service' => $diff ? ['years' => $diff->y, 'months' => $diff->m] : null,
                'company_email'     => $e->company_email,
                'telp_number'       => $e->telp_number,
                'attendance_type'   => $e->attendance_type,
            ];
        }

        return response()->json(['success' => true, 'data' => ['access' => $level] + $data]);
    }
}
