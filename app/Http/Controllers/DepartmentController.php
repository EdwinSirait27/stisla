<?php

namespace App\Http\Controllers;

use App\Models\Departments;
use App\Rules\NoXSSInput;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

/**
 * Master departemen. Isi tiap departemen (karyawan, manager, jabatan, lokasi) dihitung dari kolom
 * departemen/jabatan/lokasi saat ini di employees_tables (department_id, position_id, store_id).
 * Tabel riwayat employee_departments belum lengkap untuk semua karyawan, jadi tidak dipakai di sini;
 * command SyncPrimaryDepartmentToEmployee menjaga keduanya tetap sama.
 *
 * Akses: ManageDepartments (route group di web.php).
 */
class DepartmentController extends Controller
{
    /** Status karyawan yang dihitung "aktif" dan "belum/tidak sedang aktif". */
    private const ACTIVE = 'Active';
    private const OTHER  = ['Pending', 'On Leave'];

    private function hashId(string $id): string
    {
        return substr(hash('sha256', $id . config('app.key')), 0, 8);
    }

    private function findByHash(string $hashedId): ?Departments
    {
        return Departments::all()->first(fn($d) => hash_equals($this->hashId($d->id), $hashedId));
    }

    /**
     * Ringkasan isi departemen: [department_id => ['active','other','managers'=>[], 'positions'=>[name=>n], 'stores'=>[name=>n]]].
     */
    private function usage(?string $departmentId = null): Collection
    {
        $base = fn() => DB::table('employees_tables as e')
            ->whereNotNull('e.department_id')
            ->whereNull('e.deleted_at')
            ->when($departmentId, fn($q) => $q->where('e.department_id', $departmentId));

        $counts = $base()->groupBy('e.department_id')->get([
            'e.department_id',
            DB::raw("sum(e.status = '" . self::ACTIVE . "') as active"),
            DB::raw("sum(e.status in ('" . implode("','", self::OTHER) . "')) as other"),
        ])->keyBy('department_id');

        $managers = $base()->where('e.status', self::ACTIVE)->where('e.is_manager', 1)
            ->orderBy('e.employee_name')->get(['e.department_id', 'e.employee_name'])
            ->groupBy('department_id')->map(fn($g) => $g->pluck('employee_name')->all());

        $positions = $base()->where('e.status', self::ACTIVE)
            ->join('position_tables as p', 'p.id', '=', 'e.position_id')
            ->groupBy('e.department_id', 'p.name')
            ->orderByDesc(DB::raw('count(*)'))->orderBy('p.name')
            ->get(['e.department_id', 'p.name', DB::raw('count(*) as c')])
            ->groupBy('department_id')->map(fn($g) => $g->pluck('c', 'name')->map(fn($c) => (int) $c)->all());

        $stores = $base()->where('e.status', self::ACTIVE)
            ->join('stores_tables as s', 's.id', '=', 'e.store_id')
            ->groupBy('e.department_id', 's.name')
            ->orderByDesc(DB::raw('count(*)'))->orderBy('s.name')
            ->get(['e.department_id', 's.name', DB::raw('count(*) as c')])
            ->groupBy('department_id')->map(fn($g) => $g->pluck('c', 'name')->map(fn($c) => (int) $c)->all());

        return $counts->keys()->merge($managers->keys())->unique()->mapWithKeys(fn($id) => [$id => [
            'active'    => (int) ($counts[$id]->active ?? 0),
            'other'     => (int) ($counts[$id]->other ?? 0),
            'managers'  => $managers[$id] ?? [],
            'positions' => $positions[$id] ?? [],
            'stores'    => $stores[$id] ?? [],
        ]]);
    }

    private function emptyUsage(): array
    {
        return ['active' => 0, 'other' => 0, 'managers' => [], 'positions' => [], 'stores' => []];
    }

    /** Daftar departemen yang sudah ada, untuk cek nama/singkatan mirip di form. */
    private function existing(?string $exceptId = null): array
    {
        return Departments::orderBy('department_name')
            ->when($exceptId, fn($q) => $q->where('id', '!=', $exceptId))
            ->get(['department_name', 'nickname'])
            ->map(fn($d) => ['name' => $d->department_name, 'nickname' => $d->nickname])
            ->all();
    }

    public function index()
    {
        return view('pages.Department.Department');
    }

    public function getDepartments(Request $request)
    {
        $usage = $this->usage();

        $rows = Departments::orderBy('department_name')->get(['id', 'department_name', 'nickname'])
            ->map(function (Departments $d) use ($usage) {
                $u = $usage->get($d->id, $this->emptyUsage());
                return [
                    'name'      => $d->department_name,
                    'nickname'  => $d->nickname,
                    'employees' => $u['active'],
                    'employees_other' => $u['other'],
                    'managers'  => $u['managers'],
                    'manager_text' => implode(', ', $u['managers']),
                    'positions' => count($u['positions']),
                    'top_positions' => array_slice(array_keys($u['positions']), 0, 3),
                    'stores'    => count($u['stores']),
                    'top_stores' => array_slice(array_keys($u['stores']), 0, 2),
                    'edit_url'  => route('Department.edit', $this->hashId($d->id)),
                ];
            });

        $stats = [
            'total'      => $rows->count(),
            'used'       => $rows->where('employees', '>', 0)->count(),
            'employees'  => $rows->sum('employees'),
            'no_manager' => $rows->where('employees', '>', 0)->filter(fn($r) => !$r['managers'])->count(),
            'unused'     => $rows->where('employees', 0)->where('employees_other', 0)->count(),
            'no_nickname' => $rows->filter(fn($r) => blank($r['nickname']) || $r['nickname'] === '-')->count(),
        ];

        $rows = match ($request->input('flag')) {
            'used'       => $rows->where('employees', '>', 0),
            'no_manager' => $rows->where('employees', '>', 0)->filter(fn($r) => !$r['managers']),
            'unused'     => $rows->where('employees', 0)->where('employees_other', 0),
            default      => $rows,
        };

        // Teks di-escape di browser (esc), jadi tidak di-escape dua kali.
        return DataTables::of($rows->values())->escapeColumns([])->with('stats', $stats)->make(true);
    }

    public function create()
    {
        return view('pages.Department.create', [
            'existing' => $this->existing(),
        ]);
    }

    public function edit($hashedId)
    {
        $department = $this->findByHash($hashedId);
        abort_if(!$department, 404, 'Departemen tidak ditemukan.');

        return view('pages.Department.edit', [
            'department' => $department,
            'hashedId'   => $hashedId,
            'usage'      => $this->usage($department->id)->get($department->id, $this->emptyUsage()),
            'existing'   => $this->existing($department->id),
        ]);
    }

    /** Singkatan disimpan huruf kapital tanpa spasi, sama dengan singkatan perusahaan. */
    private function validated(Request $request, ?Departments $department = null): array
    {
        $request->merge([
            'department_name' => trim(preg_replace('/\s+/', ' ', (string) $request->input('department_name'))),
            'nickname'        => strtoupper(preg_replace('/\s+/', '', (string) $request->input('nickname'))),
        ]);

        return $request->validate([
            'department_name' => [
                'required', 'string', 'max:255',
                Rule::unique('departments_tables', 'department_name')->ignore($department?->id),
                new NoXSSInput(),
            ],
            'nickname' => [
                'required', 'string', 'min:2', 'max:10', 'alpha_num',
                Rule::unique('departments_tables', 'nickname')->ignore($department?->id),
                new NoXSSInput(),
            ],
        ], [
            'department_name.required' => 'Nama departemen wajib diisi.',
            'department_name.max'      => 'Nama departemen maksimal 255 karakter.',
            'department_name.unique'   => 'Nama departemen sudah ada.',
            'nickname.required'        => 'Singkatan wajib diisi.',
            'nickname.min'             => 'Singkatan minimal 2 karakter.',
            'nickname.max'             => 'Singkatan maksimal 10 karakter.',
            'nickname.alpha_num'       => 'Singkatan hanya huruf dan angka, tanpa spasi.',
            'nickname.unique'          => 'Singkatan sudah dipakai departemen lain.',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        try {
            $department = DB::transaction(fn() => Departments::create([
                'department_name' => $data['department_name'],
                'nickname'        => $data['nickname'],
            ]));
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['error' => 'Departemen gagal disimpan, coba lagi.'])->withInput();
        }

        activity('department')->causedBy(auth()->user())->performedOn($department)->event('created')
            ->withProperties(['attributes' => $department->only(['department_name', 'nickname'])])
            ->log("Departemen dibuat: {$department->department_name} ({$department->nickname})");

        return redirect()->route('pages.Department')->with('success', "Departemen {$department->department_name} ditambahkan.");
    }

    public function update(Request $request, $hashedId)
    {
        $department = $this->findByHash($hashedId);
        if (!$department) {
            return redirect()->route('pages.Department')->with('error', 'Departemen tidak ditemukan.');
        }

        $data = $this->validated($request, $department);
        $old  = $department->only(['department_name', 'nickname']);

        $department->update([
            'department_name' => $data['department_name'],
            'nickname'        => $data['nickname'],
        ]);

        if ($department->wasChanged()) {
            activity('department')->causedBy(auth()->user())->performedOn($department)->event('updated')
                ->withProperties(['old' => $old, 'attributes' => $department->only(['department_name', 'nickname'])])
                ->log("Departemen diubah: {$old['department_name']} ({$old['nickname']}) menjadi {$department->department_name} ({$department->nickname})");
        }

        return redirect()->route('pages.Department')->with('success', "Departemen {$department->department_name} disimpan.");
    }
}
