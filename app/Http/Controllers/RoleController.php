<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\DataTables;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Validation\Rule;


class RoleController extends Controller
{
    public function index()
    {
        return view('roles.index');
    }


    public function getRoles()
    {
        $roles = Role::with('permissions')
            ->select(['id', 'name'])
            ->get()
            ->map(function ($role) {
                $role->id_hashed = substr(hash('sha256', $role->id . env('APP_KEY')), 0, 8);
                $role->action = '
                <a href="' . route('roles.edit', $role->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit role" title="Edit Role: ' . e($role->name) . '">
                    <i class="fas fa-user-edit text-secondary"></i>
                </a>
                <a href="#" onclick="deleteRole(\'' . route('roles.destroy', $role->id_hashed) . '\')" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Delete role"title="Delete Role: ' . e($role->name) . '">
                    <i class="fas fa-trash text-danger"></i>
                </a>';
                return $role;
            });
        return DataTables::of($roles)
            ->addIndexColumn()
            // ->addColumn('permissions', function($role) {
            //     return $role->permissions->pluck('name')->implode(', ');
            // })
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
            $role = new Role();
            $rolePermissions = []; // Inisialisasi array kosong

            return view('roles.create', compact('permissions', 'role', 'rolePermissions'));

        } catch (\Exception $e) {
            \Log::error('Error accessing role creation page: ' . $e->getMessage());

            return redirect()->route('roles.index')
                ->with('error', 'Failed to access role creation page. Please try again.');
        }
    }



    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => [
                'required',
                'regex:/^[a-zA-Z0-9_-]+$/',
                'unique:roles,name',
                'max:255',
                new NoXSSInput()
            ],
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        try {
            DB::beginTransaction();

            // Buat role baru
            $role = Role::create([
                'name' => $validatedData['name'],
                'guard_name' => 'web'
            ]);

            // Convert permission IDs to names dan sync
            $permissions = Permission::whereIn('id', $request->permissions ?? [])
                ->pluck('name')
                ->toArray();

            $role->syncPermissions($permissions);

            DB::commit();

            return redirect()->route('roles.index')
                ->with('success', 'Role created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Role creation failed: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create role. Please try again.');
        }
    }



    public function edit($hashedId)
    {
        // Cari role berdasarkan hashed ID
        $role = Role::with('permissions')->get()->first(function ($role) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $role->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$role) {
            abort(404, 'Role not found.');
        }
        // Ambil semua permissions
        $permissions = Permission::all();
        // Ambil permission IDs yang dimiliki role
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions', 'hashedId'));
    }

    public function update(Request $request, $hashedId)
    {
        $roles = Role::with('permissions')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$roles) {
            return redirect()->route('roles.index')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'name' => [
                'nullable',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('roles')->ignore($roles->id),
                new NoXSSInput()
            ],
            'permissions' => 'array',
            'regex:/^[a-zA-Z0-9_-]+$/',
            new NoXSSInput()
        ]);

        $roles->update(['name' => $request->name]);

        // Convert permission IDs to names
        $permissions = Permission::whereIn('id', $request->permissions)
            ->pluck('name')
            ->toArray();
        $roles->syncPermissions($permissions);
        return redirect()->route('roles.index')->with('success', 'Role updated successfully');
    }















    // public function destroy(Role $role)
    // {
    //     $role->delete();
    //     return redirect()->route('roles.index')->with('success', 'Role deleted successfully');
    // }
    public function destroy($id_hashed)
    {
        try {
            // Decode hashed ID
            $roleId = null;
            $roles = Role::all();
            foreach ($roles as $role) {
                $hashed = substr(hash('sha256', $role->id . env('APP_KEY')), 0, 8);
                if ($hashed === $id_hashed) {
                    $roleId = $role->id;
                    break;
                }
            }

            if (!$roleId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Role not found'
                ], 404);
            }

            // Cek apakah role sedang digunakan oleh user (cara Spatie)
            $usersCount = DB::table('model_has_roles')
                ->where('role_id', $roleId)
                ->count();

            if ($usersCount > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete role because it is assigned to ' . $usersCount . ' user(s)'
                ], 400);
            }

            // Hapus role
            $role = Role::findOrFail($roleId);
            $role->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Role deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete role: ' . $e->getMessage()
            ], 500);
        }
    }
}
