<?php

namespace App\Http\Controllers;

use App\Rules\NoXSSInput;
use App\Services\PermissionUsageScanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Yajra\DataTables\DataTables;

class PermissionController extends Controller
{
    /** Huruf/angka di awal, lalu huruf, angka, titik, underscore, atau strip. Mis. payroll.view, ManageRoster. */
    private const NAME_REGEX = '/^[A-Za-z][A-Za-z0-9._-]*$/';

    public function __construct(protected PermissionUsageScanner $scanner) {}

    public function index()
    {
        $all = Permission::withCount('roles')->orderBy('name')->get(['id', 'name']);
        $usage = $this->scanner->usages($all->pluck('name')->all());

        $stats = [
            'total'    => $all->count(),
            'no_role'  => $all->where('roles_count', 0)->count(),
            'unused'   => $all->filter(fn($p) => empty($usage[$p->name]))->count(),
            'roles'    => \Spatie\Permission\Models\Role::count(),
        ];
        $groups = $all->map(fn($p) => $this->groupOf($p->name))->unique()->sort()->values();

        return view('permissions.index', compact('stats', 'groups'));
    }

    public function getPermissions(Request $request)
    {
        $permissions = Permission::with('roles:id,name')->orderBy('name')->get(['id', 'name', 'created_at']);
        $usage = $this->scanner->usages($permissions->pluck('name')->all());

        $rows = $permissions->map(function ($permission) use ($usage) {
            $locations = $usage[$permission->name] ?? [];
            $hashed = substr(hash('sha256', $permission->id . env('APP_KEY')), 0, 8);

            return [
                'id_hashed'   => $hashed,
                'name'        => $permission->name,
                'group'       => $this->groupOf($permission->name),
                'roles'       => $permission->roles->pluck('name')->sort()->values()->all(),
                'roles_count' => $permission->roles->count(),
                'files_count' => count(array_unique(array_column($locations, 'file'))),
                'uses_count'  => count($locations),
                'edit_url'    => route('permissions.edit', $hashed),
            ];
        });

        $rows = match ($request->input('flag')) {
            'no_role' => $rows->where('roles_count', 0),
            'unused'  => $rows->where('uses_count', 0),
            default   => $rows,
        };
        if ($request->filled('group')) {
            $rows = $rows->where('group', $request->input('group'));
        }

        return DataTables::of($rows->values())
            ->filter(function ($instance) use ($request) {
                $q = mb_strtolower(trim((string) $request->input('search.value')));
                if ($q !== '') {
                    $instance->collection = $instance->collection->filter(
                        fn($row) => str_contains(mb_strtolower($row['name']), $q)
                            || collect($row['roles'])->contains(fn($r) => str_contains(mb_strtolower($r), $q))
                    );
                }
            })
            ->make(true);
    }

    public function create()
    {
        $existing = Permission::orderBy('name')->pluck('name');
        $groups = $existing->map(fn($n) => $this->groupOf($n))->unique()->sort()->values();

        return view('permissions.create', compact('existing', 'groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'max:125',
                'regex:' . self::NAME_REGEX,
                Rule::unique('permissions', 'name')->where('guard_name', 'web'),
                new NoXSSInput(),
            ],
        ], $this->messages());

        try {
            DB::beginTransaction();
            Permission::create(['name' => $request->name, 'guard_name' => 'web']);
            DB::commit();
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return redirect()->route('permissions.index')
                ->with('success', "Permission {$request->name} berhasil dibuat. Tambahkan ke role lewat halaman Roles.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Permission creation failed: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Gagal membuat permission. Coba lagi.');
        }
    }

    public function edit($hashedId)
    {
        $permission = $this->findByHash($hashedId);
        if (!$permission) {
            abort(404, 'Permission not found.');
        }

        $permission->load('roles:id,name');
        $locations = $this->scanner->usages([$permission->name])[$permission->name];

        return view('permissions.edit', [
            'permission' => $permission,
            'hashedId'   => $hashedId,
            'locations'  => $locations,
            'filesCount' => count(array_unique(array_column($locations, 'file'))),
            'group'      => $this->groupOf($permission->name),
        ]);
    }

    public function update(Request $request, $hashedId)
    {
        $permission = $this->findByHash($hashedId);
        if (!$permission) {
            return redirect()->route('permissions.index')->with('error', 'ID tidak valid atau data tidak ditemukan.');
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'max:125',
                'regex:' . self::NAME_REGEX,
                Rule::unique('permissions', 'name')->where('guard_name', 'web')->ignore($permission->id),
                new NoXSSInput(),
            ],
        ], $this->messages());

        $oldName = $permission->name;

        try {
            $permission->update(['name' => $validated['name']]);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $this->scanner->forget();

            $message = $oldName === $validated['name']
                ? 'Tidak ada perubahan.'
                : "Permission {$oldName} diganti menjadi {$validated['name']}. Pastikan kode yang memakai nama lama sudah ikut diubah.";

            return redirect()->route('permissions.index')->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Permission update failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui permission. Coba lagi.');
        }
    }

    /** Grup = bagian sebelum titik pertama (payroll.view → payroll). Nama gaya lama tanpa titik masuk "format lama". */
    private function groupOf(string $name): string
    {
        return str_contains($name, '.') ? strtolower(strtok($name, '.')) : 'format lama';
    }

    private function findByHash(string $hashedId): ?Permission
    {
        return Permission::get()->first(
            fn($p) => substr(hash('sha256', $p->id . env('APP_KEY')), 0, 8) === $hashedId
        );
    }

    private function messages(): array
    {
        return [
            'name.required' => 'Nama permission wajib diisi.',
            'name.max'      => 'Nama permission maksimal 125 karakter.',
            'name.regex'    => 'Nama permission diawali huruf dan hanya boleh berisi huruf, angka, titik, underscore, atau strip (mis. payroll.view).',
            'name.unique'   => 'Permission dengan nama ini sudah ada.',
        ];
    }
}
