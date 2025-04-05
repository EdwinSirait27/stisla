<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\DataTables;
use App\Rules\NoXSSInput;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        return view('roles.index');
    }
    // public function getRoles()
    // {
    //     $roles = Role::with('permissions')->get()
    //         ->map(function ($role) {
    //             $role->id_hashed = substr(hash('sha256', $role->id . env('APP_KEY')), 0, 8);
    //             $role->action = '
    //                 <div class="btn-group">
    //                     <a href="' . route('roles.edit', $role->id) . '" class="btn btn-sm btn-warning">
    //                         <i class="fas fa-edit"></i> Edit
    //                     </a>
    //                     <form action="' . route('roles.destroy', $role->id) . '" method="POST" class="d-inline">
    //                         ' . csrf_field() . '
    //                         ' . method_field('DELETE') . '
    //                         <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')">
    //                             <i class="fas fa-trash"></i> Delete
    //                         </button>
    //                     </form>
    //                 </div>
    //             ';
    //             // Format permissions
    //             $role->permission_list = $role->permissions->map(function ($permission) {
    //                 return '<span class="badge ">' . $permission->name . '</span>';
    //             })->implode(' ');
    //             return $role;
    //         });

    //     return DataTables::of($roles)
    //         ->addColumn('permissions', function ($role) {
    //             return $role->permission_list;
    //         })
    //         ->addColumn('action', function ($role) {
    //             return $role->action;
    //         })
    //         ->rawColumns(['permissions', 'action'])
    //         ->make(true);
    // }
    public function getRoles()
    {
        $roles = Role::with('permissions')
            ->select(['id', 'name'])
            ->get()
            ->map(function ($role) {
                $role->id_hashed = substr(hash('sha256', $role->id . env('APP_KEY')), 0, 8);
                $role->action = '
                    <a href="' . route('roles.edit', $role->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit roles">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
                return $role;
            });
        return DataTables::of($roles)
            ->addIndexColumn()
            ->addColumn('permissions', function($role) {
                return $role->permissions->pluck('name')->implode(', ');
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    public function create()
    {
        $permissions = Permission::all();
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);
        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')->with('success', 'Role created successfully');
    }

    // public function edit(Role $role)
    // {
    //     $permissions = Permission::all();
    //     $rolePermissions = $role->permissions->pluck('id')->toArray();
    //     return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    // }
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

//     public function update(Request $request, Role $role)
// {
//     $request->validate([
//         'name' => [
//             'required',
//             'unique',
//             'regex:/^[a-zA-Z0-9_-]+$/',
//             Rule::unique('roles')->ignore($role->id),
//             new NoXSSInput()
//         ],
//         'permissions' => 'array',
//     ]);

//     $role->update(['name' => $request->name]);

//     // Convert permission IDs to names
//     $permissions = Permission::whereIn('id', $request->permissions)
//         ->pluck('name')
//         ->toArray();

//     $role->syncPermissions($permissions);

//     return redirect()->route('roles.index')->with('success', 'Role updated successfully');
// }

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
        'permissions' => 'array','regex:/^[a-zA-Z0-9_-]+$/',
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















    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role deleted successfully');
    }
}
