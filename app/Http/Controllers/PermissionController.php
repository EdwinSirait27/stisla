<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\DataTables;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public function index()
    {

        return view('permissions.index');
    }
    public function getPermissions()
    {
        $permissions = Permission::with('permissions')
            ->select(['id', 'name'])
            ->get()
            ->map(function ($permission) {
                $permission->id_hashed = substr(hash('sha256', $permission->id . env('APP_KEY')), 0, 8);
                $permission->action = '
                <a href="' . route('permissions.edit', $permission->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit permission" title="Edit Permission: ' . e($permission->name) . '">
                    <i class="fas fa-user-edit text-secondary"></i>
                </a>
               ';
                return $permission;
            });
        return DataTables::of($permissions)
            ->addIndexColumn()
            ->rawColumns(['action'])
            ->make(true);
    }
    public function create()
    {
        return view('permissions.create');
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
            ], [
                'name.required' => 'The email is required.',
                'name.unique' => 'The roles already exist.',
    
            ]

        ]);

        try {
            DB::beginTransaction();

            Permission::create(['name' => $request->name, 'guard_name' => 'web']);


            DB::commit();

            return redirect()->route('permissions.index')
                ->with('success', 'Permission created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Permission creation failed: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create Permission. : ' . $e->getMessage());
        }
    }

    public function edit($hashedId)
    {
        // Cari role berdasarkan hashed ID
        $permission = Permission::get()->first(function ($permission) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $permission->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$permission) {
            abort(404, 'permission not found.');
        }
        // Ambil semua permissions
        $permissions = Permission::all();

        return view('permissions.edit', compact('permission', 'permissions', 'hashedId'));
    }



    
    public function update(Request $request, $hashedId)
    {
        // Cari permission dengan hash yang lebih aman
        $permission = Permission::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        
        

        if (!$permission) {
            return redirect()->route('permissions.index')->with('error', 'ID tidak valid atau data tidak ditemukan.');
        }

        $validatedData = $request->validate([
            'name' => [
                'required', // Ubah dari nullable ke required jika field harus diisi
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('permissions')->ignore($permission->id),
                new NoXSSInput()
            ],
        ]);
        try {
            $permission->update($validatedData);
            return redirect()->route('permissions.index')->with('success', 'Permission berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui permission: ' . $e->getMessage());
        }
    }

}
