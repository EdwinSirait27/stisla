<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\DataTables;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\PermissionRegistrar;


class RoleController extends Controller
{
    public function index()
    {
        return view('roles.index');
    }
    public function getRoles()
    {
        // dd(Role::with('permissions')->get()->toArray());
        $roles = Role::with('permissions')
            ->select(['id', 'name'])
            ->get()
            ->map(function ($role) {
                $role->id_hashed = substr(hash('sha256', $role->id . env('APP_KEY')), 0, 8);
                $role->action = '
                <a href="' . route('roles.edit', $role->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit role" title="Edit Role: ' . e($role->name) . '">
                    <i class="fas fa-user-edit text-secondary"></i>
                </a>
            ';
                return $role;
            });
        return DataTables::of($roles)
            ->addIndexColumn()
           
            ->addColumn('permissions', function ($role) {
                return $role->permissions->count()
                    ? $role->permissions->pluck('name')->implode(', ')
                    : 'Empty';
            })

            ->rawColumns(['action'])
            ->make(true);
    }
    
    public function create()
    {
        try {
            $permissions = Permission::orderBy('name')->get();
            
            // Debug output untuk melihat jumlah permissions yang diambil
            \Log::debug('Total permissions fetched:', [
                'count' => $permissions->count()
            ]);
        
            // Debug untuk melihat beberapa sample permission IDs
            \Log::debug('Sample permission IDs:', [
                'sample_ids' => $permissions->take(3)->pluck('id')
            ]);
        
            $role = new Role();
            $rolePermissions = collect();
        
            return view('roles.create', compact('permissions', 'role', 'rolePermissions'));
        
        } catch (\Exception $e) {
            \Log::error('Error accessing role creation page:', [
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('roles.index')
                ->with('error', 'Failed to access role creation page. Please try again.');
        }
    }
    
    
    // public function store(Request $request)
    // {
    //     \Log::info('Attempting to create new role', ['user_id' => auth()->id()]);
    
    //     // Debug: Log permissions yang ada di database
    //     $dbPermissions = Permission::all()->pluck('id');
    //     \Log::debug('Database permissions:', $dbPermissions->toArray());
    
    //     // Debug: Log data request yang diterima
    //     \Log::debug('Incoming request data:', [
    //         'name' => $request->name,
    //         'permissions' => $request->permissions,
    //         'all_input' => $request->except('_token')
    //     ]);
        
    //     try {
    //         // Validasi input
    //         $validatedData = $request->validate([
    //             'name' => [
    //                 'required',
    //                 'unique:roles,name',
    //                 'max:255',
    //                 new NoXSSInput()
    //             ],
    //             'permissions' => 'required|array',
    //             'permissions.*' => [
    //                 'required',
    //                 'exists:permissions,id',
    //                 'distinct'
    //             ]
    //         ], [
    //             'permissions.*.exists' => 'The selected permission is invalid.',
    //             'permissions.*.distinct' => 'Duplicate permissions are not allowed.'
    //         ]);
    
    //         // Debug: Log hasil validasi sebelum transaksi
    //         \Log::debug('Validated data:', $validatedData);
    
    //         DB::transaction(function () use ($validatedData) {
    //             $role = Role::create([
    //                 'name' => $validatedData['name'],
    //                 'guard_name' => 'web'
    //             ]);
    
    //             if (!empty($validatedData['permissions'])) {
    //                 // Pastikan semua permission ID adalah UUID yang valid
    //                 $permissionIds = $validatedData['permissions'];
    //                 \Log::debug('Permissions to sync:', $permissionIds);
    
    //                 $role->syncPermissions($permissionIds);
    //                 \Log::info('Permissions synced', [
    //                     'role_id' => $role->id,
    //                     'permission_count' => count($permissionIds)
    //                 ]);
    //             }
    //         });
    
    //         return redirect()->route('roles.index')
    //             ->with('success', 'Role created successfully');
    
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         \Log::warning('Validation failed', [
    //             'errors' => $e->errors(),
    //             'input' => $request->except('_token')
    //         ]);
    //         return redirect()->back()
    //             ->withErrors($e->errors())
    //             ->withInput();
    //     } catch (\Exception $e) {
    //         \Log::error('Role creation failed', [
    //             'error_message' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);
    //         return redirect()->back()
    //             ->withInput()
    //             ->with('error', 'Role creation failed: ' . $e->getMessage());
    //     }
    // }
    public function store(Request $request)
{
    \Log::info('Attempting to create new role', ['user_id' => auth()->id()]);

    // Debug: Log permissions yang ada di database
    $dbPermissions = Permission::all()->pluck('id');
    \Log::debug('Database permissions:', $dbPermissions->toArray());

    // Debug: Log data request yang diterima
    \Log::debug('Incoming request data:', [
        'name' => $request->name,
        'permissions' => $request->permissions,
        'all_input' => $request->except('_token')
    ]);

    try {
        // Validasi input
        $validatedData = $request->validate([
            'name' => [
                'required',
                'unique:roles,name',
                'max:255',
                new NoXSSInput()
            ],
            'permissions' => 'required|array',
            'permissions.*' => [
                'required',
                'exists:permissions,id',
                'distinct'
            ]
        ], [
            'permissions.*.exists' => 'The selected permission is invalid.',
            'permissions.*.distinct' => 'Duplicate permissions are not allowed.'
        ]);

        // Debug: Log hasil validasi sebelum transaksi
        \Log::debug('Validated data:', $validatedData);

        DB::transaction(function () use ($validatedData) {
            $role = Role::create([
                'name' => $validatedData['name'],
                'guard_name' => 'web'
            ]);

            if (!empty($validatedData['permissions'])) {
                $permissionIds = $validatedData['permissions'];

                // Ambil model Permission berdasarkan ID
                $permissions = Permission::whereIn('id', $permissionIds)->get();

                // Debug: Log permission models yang akan disinkronkan
                \Log::debug('Permissions to sync:', $permissions->pluck('id')->toArray());

                $role->syncPermissions($permissions);

                \Log::info('Permissions synced', [
                    'role_id' => $role->id,
                    'permission_count' => $permissions->count()
                ]);
            }
        });

        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully');

    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::warning('Validation failed', [
            'errors' => $e->errors(),
            'input' => $request->except('_token')
        ]);
        return redirect()->back()
            ->withErrors($e->errors())
            ->withInput();
    } catch (\Exception $e) {
        \Log::error('Role creation failed', [
            'error_message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return redirect()->back()
            ->withInput()
            ->with('error', 'Role creation failed: ' . $e->getMessage());
    }
}

    
    public function edit($hashedId)
    {
        Log::info('Masuk ke method editRole', ['hashedId' => $hashedId]);
    
        $role = Role::with('permissions')->get()->first(function ($role) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $role->id . env('APP_KEY')), 0, 8);
            Log::info('Cek hash role', ['roleId' => $role->id, 'expectedHash' => $expectedHash, 'hashedId' => $hashedId]);
            return $expectedHash === $hashedId;
        });
    
        if (!$role) {
            Log::warning('Role tidak ditemukan di method edit', ['hashedId' => $hashedId]);
            abort(404, 'Role not found.');
        }
    
        Log::info('Role ditemukan di method edit', ['roleId' => $role->id, 'roleName' => $role->name]);
    
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();
    
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions', 'hashedId'));
    }
    
    
    // public function update(Request $request, $hashedId)
    // {
    //     Log::info('Masuk ke method updateRole', ['roleId' => $hashedId]);
    
    //     // Coba cari role dengan menggunakan where untuk UUID
    //     $roles = Role::where('id', $hashedId)->first();
    
    //     if (!$roles) {
    //         Log::warning('Role tidak ditemukan', ['roleId' => $hashedId]);
    //         return redirect()->route('roles.index')->with('error', 'Role tidak ditemukan.');
    //     }
    
    //     // Validasi data dari request
    //     $validatedData = $request->validate([
    //         'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($roles->id)],
    //         'permissions' => ['required', 'array'],
    //     ]);
    
    //     Log::info('Data berhasil divalidasi', ['validatedData' => $validatedData]);
    
    //     // Update nama role
    //     $roles->update(['name' => $validatedData['name']]);
    
    //     // Ambil daftar permission yang baru
    //     $permissions = $validatedData['permissions'] ?? [];
    //     Log::info('Permission yang diminta:', ['permissions' => $permissions]);
    
    //     // Ambil permission yang terhubung dengan role sebelum update
    //     $before = $roles->permissions->pluck('id')->toArray();
    //     Log::info('Permission sebelum update:', $before);
    
    //     // Sinkronisasi permission dengan role
    //     $roles->syncPermissions($permissions);
    
    //     // Ambil permission yang terhubung dengan role setelah update
    //     $after = $roles->permissions()->pluck('id')->toArray();
    //     Log::info('Permission sesudah update:', $after);
    
    //     // Clear cache permission
    //     app()[PermissionRegistrar::class]->forgetCachedPermissions();
    
    //     return redirect()->route('roles.index')->with('success', 'Role berhasil diupdate.');
    // }
//     public function update(Request $request, $hashedId)
// {
//     Log::info('Masuk ke method updateRole', ['hashedId' => $hashedId]);

//     // Cari role dengan matching hash (sama seperti di edit)
//     $role = Role::all()->first(function ($role) use ($hashedId) {
//         $expectedHash = substr(hash('sha256', $role->id . env('APP_KEY')), 0, 8);
//         return $expectedHash === $hashedId;
//     });

//     if (!$role) {
//         Log::warning('Role tidak ditemukan', ['hashedId' => $hashedId]);
//         return redirect()->route('roles.index')->with('error', 'Role tidak ditemukan.');
//     }

//     $validatedData = $request->validate([
//         'name' => ['required', 'string', 'max:255'],
//         'permissions' => ['required', 'array'],
//     ]);

//     Log::info('Data berhasil divalidasi', ['validatedData' => $validatedData]);

//     $role->update(['name' => $validatedData['name']]);

//     $permissions = $validatedData['permissions'];
//     Log::info('Permission yang diminta:', ['permissions' => $permissions]);

//     $before = $role->permissions->pluck('id')->toArray();
//     Log::info('Permission sebelum update:', $before);

//     $role->syncPermissions($permissions);

//     $after = $role->permissions()->pluck('id')->toArray();
//     Log::info('Permission sesudah update:', $after);

//     app()[PermissionRegistrar::class]->forgetCachedPermissions();

//     return redirect()->route('roles.index')->with('success', 'Role berhasil diupdate.');
// }
public function update(Request $request, $hashedId)
{
    Log::info('Masuk ke method updateRole', ['hashedId' => $hashedId]);

    // Cari role dengan matching hash (seperti di method edit)
    $role = Role::all()->first(function ($role) use ($hashedId) {
        $expectedHash = substr(hash('sha256', $role->id . env('APP_KEY')), 0, 8);
        return $expectedHash === $hashedId;
    });

    if (!$role) {
        Log::warning('Role tidak ditemukan', ['hashedId' => $hashedId]);
        return redirect()->route('roles.index')->with('error', 'Role tidak ditemukan.');
    }

    try {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'permissions' => ['required', 'array'],
            'permissions.*' => [
                'required',
                'exists:permissions,id',
                'distinct'
            ]
        ], [
            'permissions.*.exists' => 'Permission yang dipilih tidak ditemukan.',
            'permissions.*.distinct' => 'Permission duplikat tidak diperbolehkan.'
        ]);

        Log::info('Data berhasil divalidasi', ['validatedData' => $validatedData]);

        // Update nama role
        $role->update(['name' => $validatedData['name']]);

        // Ambil permission berdasarkan ID UUID
        $permissions = Permission::whereIn('id', $validatedData['permissions'])->get();
        Log::info('Permission yang diminta:', ['permissions' => $permissions->pluck('id')->toArray()]);

        $before = $role->permissions->pluck('id')->toArray();
        Log::info('Permission sebelum update:', $before);

        $role->syncPermissions($permissions);

        $after = $role->permissions()->pluck('id')->toArray();
        Log::info('Permission sesudah update:', $after);

        // Bersihkan cache permission (Spatie)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('success', 'Role berhasil diupdate.');

    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::warning('Validasi gagal', ['errors' => $e->errors()]);
        return redirect()->back()->withErrors($e->errors())->withInput();
    } catch (\Exception $e) {
        Log::error('Gagal update role', [
            'error_message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return redirect()->back()->with('error', 'Terjadi kesalahan saat mengupdate role.')->withInput();
    }
}

}
