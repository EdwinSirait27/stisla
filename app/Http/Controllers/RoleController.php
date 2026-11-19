<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\NoXSSInput;
use App\Services\PermissionUsageScanner;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Yajra\DataTables\DataTables;

class RoleController extends Controller
{
    public function __construct(protected PermissionUsageScanner $scanner) {}

    public function index()
    {
        $roles = Role::withCount(['permissions', 'users'])->get();

        $stats = [
            'roles'        => $roles->count(),
            'users'        => (int) DB::table('model_has_roles')->distinct('model_id')->count('model_id'),
            'empty_roles'  => $roles->where('users_count', 0)->count(),
            'permissions'  => Permission::count(),
        ];

        return view('roles.index', compact('stats'));
    }

    public function getRoles(Request $request)
    {
        $roles = Role::with('permissions:id,name')->withCount('users')->orderBy('name')->get(['id', 'name']);
        $usage = $this->scanner->usages($roles->pluck('name')->all());
        $totalPermissions = Permission::count();

        $rows = $roles->map(function ($role) use ($usage, $totalPermissions) {
            $hashed = substr(hash('sha256', $role->id . env('APP_KEY')), 0, 8);
            $groups = $role->permissions
                ->groupBy(fn($p) => $this->groupOf($p->name))
                ->map->count()
                ->sortDesc();

            return [
                'name'              => $role->name,
                'users_count'       => $role->users_count,
                'permissions_count' => $role->permissions->count(),
                'permissions_total' => $totalPermissions,
                'groups'            => $groups->map(fn($n, $g) => ['group' => $g, 'count' => $n])->values()->all(),
                'code_files'        => count(array_unique(array_column($usage[$role->name] ?? [], 'file'))),
                'edit_url'          => route('roles.edit', $hashed),
            ];
        });

        return DataTables::of($rows)
            ->filter(function ($instance) use ($request) {
                $q = mb_strtolower(trim((string) $request->input('search.value')));
                if ($q !== '') {
                    $instance->collection = $instance->collection->filter(
                        fn($row) => str_contains(mb_strtolower($row['name']), $q)
                    );
                }
            })
            ->make(true);
    }

    public function create()
    {
        return view('roles.create', [
            'groupedPermissions' => $this->groupedPermissions(),
            'rolePermissions'    => [],
            'existingNames'      => Role::pluck('name'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        try {
            DB::transaction(function () use ($validated) {
                $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
                $role->syncPermissions(Permission::whereIn('id', $validated['permissions'])->get());
            });
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            Log::info('Role dibuat', ['name' => $validated['name'], 'permissions' => count($validated['permissions']), 'by' => auth()->id()]);

            return redirect()->route('roles.index')
                ->with('success', "Role {$validated['name']} dibuat dengan " . count($validated['permissions']) . ' permission. Berikan role ini ke user lewat halaman Users.');
        } catch (\Exception $e) {
            Log::error('Role creation failed', ['error' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'Gagal membuat role. Coba lagi.');
        }
    }

    public function edit($hashedId)
    {
        $role = $this->findByHash($hashedId);
        if (!$role) {
            abort(404, 'Role not found.');
        }

        $role->load('permissions:id,name');
        $locations = $this->scanner->usages([$role->name])[$role->name];

        $users = User::role($role->name)->with('Employee:id,employee_name')->limit(12)->get(['id', 'username', 'employee_id']);

        return view('roles.edit', [
            'role'               => $role,
            'hashedId'           => $hashedId,
            'groupedPermissions' => $this->groupedPermissions(),
            'rolePermissions'    => $role->permissions->pluck('id')->map(fn($id) => (string) $id)->all(),
            'existingNames'      => Role::where('id', '!=', $role->id)->pluck('name'),
            'usersCount'         => User::role($role->name)->count(),
            'users'              => $users,
            'locations'          => $locations,
            'codeFiles'          => count(array_unique(array_column($locations, 'file'))),
        ]);
    }

    public function update(Request $request, $hashedId)
    {
        $role = $this->findByHash($hashedId);
        if (!$role) {
            return redirect()->route('roles.index')->with('error', 'Role tidak ditemukan.');
        }

        $validated = $request->validate($this->rules($role->id), $this->messages());
        $oldName = $role->name;
        $renamed = $oldName !== $validated['name'];

        try {
            DB::transaction(function () use ($role, $validated, $oldName, $renamed) {
                $role->update(['name' => $validated['name']]);
                $role->syncPermissions(Permission::whereIn('id', $validated['permissions'])->get());

                // Nama role juga disimpan di users.active_role_hrx / all_roles_hrx → ikut diganti
                if ($renamed) {
                    User::where('active_role_hrx', $oldName)->update(['active_role_hrx' => $validated['name']]);
                    User::whereJsonContains('all_roles_hrx', $oldName)->get()->each(function ($user) use ($oldName, $validated) {
                        $user->all_roles_hrx = collect($user->all_roles_hrx)
                            ->map(fn($r) => $r === $oldName ? $validated['name'] : $r)->values()->all();
                        $user->saveQuietly();
                    });
                }
            });

            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $this->scanner->forget();

            Log::info('Role diupdate', ['role_id' => $role->id, 'old' => $oldName, 'new' => $validated['name'], 'by' => auth()->id()]);

            $message = $renamed
                ? "Role {$oldName} diganti menjadi {$validated['name']}. Pastikan kode yang memakai nama lama ikut diubah."
                : "Role {$validated['name']} diperbarui.";

            return redirect()->route('roles.index')->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Gagal update role', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengupdate role.')->withInput();
        }
    }

    // ── Helpers ──

    /** Permission dikelompokkan per grup (bagian sebelum titik pertama). */
    private function groupedPermissions(): Collection
    {
        return Permission::orderBy('name')->get(['id', 'name'])
            ->groupBy(fn($p) => $this->groupOf($p->name))
            ->sortKeysUsing(fn($a, $b) => $a === 'format lama' ? 1 : ($b === 'format lama' ? -1 : strcmp($a, $b)));
    }

    private function groupOf(string $name): string
    {
        return str_contains($name, '.') ? strtolower(strtok($name, '.')) : 'format lama';
    }

    private function findByHash(string $hashedId): ?Role
    {
        return Role::get()->first(fn($r) => substr(hash('sha256', $r->id . env('APP_KEY')), 0, 8) === $hashedId);
    }

    private function rules($ignoreId = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:125',
                'regex:/^[A-Za-z][A-Za-z0-9 ._-]*$/',
                Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($ignoreId),
                new NoXSSInput(),
            ],
            'permissions'   => ['required', 'array', 'min:1'],
            'permissions.*' => ['required', 'exists:permissions,id', 'distinct'],
        ];
    }

    private function messages(): array
    {
        return [
            'name.required'          => 'Nama role wajib diisi.',
            'name.max'               => 'Nama role maksimal 125 karakter.',
            'name.regex'             => 'Nama role diawali huruf dan hanya boleh berisi huruf, angka, spasi, titik, underscore, atau strip.',
            'name.unique'            => 'Role dengan nama ini sudah ada.',
            'permissions.required'   => 'Pilih minimal satu permission.',
            'permissions.min'        => 'Pilih minimal satu permission.',
            'permissions.*.exists'   => 'Permission yang dipilih tidak ditemukan.',
            'permissions.*.distinct' => 'Permission duplikat tidak diperbolehkan.',
        ];
    }
}
