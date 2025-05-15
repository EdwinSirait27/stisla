<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Yajra\DataTables\DataTables;
use App\Models\Terms;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use App\Rules\NoXSSInput;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
class dashboardAdminController extends Controller
{
    public function index()
    {
        return view('pages.dashboardAdmin.dashboardAdmin');
    }
    public function getUsers()
    {
        $users = User::with('Terms', 'roles', 'Employee')
            ->select(['id', 'username', 'employee_id', 'password', 'terms_id', 'created_at'])
            ->get()
            ->map(function ($user) {
                $user->id_hashed = substr(hash('sha256', $user->id . env('APP_KEY')), 0, 8);
                $user->action = '
               
                    <a href="' . route('dashboardAdmin.edit', $user->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit User: ' . e($user->username) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
                return $user;
            });
        return DataTables::of($users)
            ->addColumn('roles', function ($user) {
                return !empty($user->roles->pluck('name')->toArray()) ? $user->roles->pluck('name')->implode(', ') : 'Empty';
            })
            ->addColumn('device_lan_mac', function ($user) {
                return !empty($user->Terms) && !empty($user->Terms->device_lan_mac)
                    ? $user->Terms->device_lan_mac
                    : 'Empty';
            })
            ->addColumn('employee_name', function ($user) {
                return !empty($user->Employee) && !empty($user->Employee->employee_name)
                    ? $user->Employee->employee_name
                    : 'Empty';
            })
            ->addColumn('device_wifi_mac', function ($user) {
                return !empty($user->Terms) && !empty($user->Terms->device_wifi_mac)
                    ? $user->Terms->device_wifi_mac
                    : 'Empty';
            })
            ->rawColumns(['device_lan_mac', 'device_wifi_mac', 'action'])
            ->make(true);
    }
    public function edit($hashedId)
    {
        $user = User::with('terms', 'roles.permissions', 'Employee')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$user) {
            abort(404, 'User not found.');
        }
        $userStatus = ['Active', 'Inactive'];
        $selectedStatus = old('status', $user->Employee->status ?? '');
        $roles = Role::pluck('name', 'name')->all();
        // Change selectedRole to use name instead of id
        $selectedRole = old('role', optional($user->roles->first())->name ?? '');
        return view('pages.dashboardAdmin.edit', [
            'user' => $user,
            'hashedId' => $hashedId,
            'userStatus' => $userStatus,
            'selectedStatus' => $selectedStatus,
            'roles' => $roles,
            'selectedRole' => $selectedRole
        ]);
    }
    public function update(Request $request, $hashedId)
{
    Log::info('Masuk ke method update', ['hashedId' => $hashedId]);

    $user = User::with('Terms', 'roles.permissions', 'Employee')->get()->first(function ($u) use ($hashedId) {
        $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
        return $expectedHash === $hashedId;
    });

    if (!$user) {
        Log::warning('User tidak ditemukan dengan hashedId', ['hashedId' => $hashedId]);
        return redirect()->route('pages.dashboardAdmin')->with('error', 'ID tidak valid.');
    }

    Log::info('User ditemukan', ['user_id' => $user->id]);

    $validatedData = $request->validate([
        'device_lan_mac' => ['nullable', 'string', 'max:255', new NoXSSInput()],
        'device_wifi_mac' => ['nullable', 'string', 'max:255', new NoXSSInput()],
        'password' => ['nullable', 'string', 'min:7', 'max:12', new NoXSSInput()],
        'username' => [
            'required', 'string', 'max:12', 'min:7',
            'regex:/^[a-zA-Z0-9_-]+$/',
            Rule::unique('users')->ignore($user->id),
            new NoXSSInput()
        ],
        'status' => ['nullable', 'string', 'in:Active,Inactive,Pending,Mutation', new NoXSSInput()],
        'role' => ['required', 'string', 'exists:roles,name'],
        'permissions' => ['nullable'],
    ]);

    Log::info('Data berhasil divalidasi', ['validatedData' => $validatedData]);

    $userData = ['username' => $validatedData['username']];
    if (!empty($validatedData['password'])) {
        $userData['password'] = bcrypt($validatedData['password']);
    }

    DB::beginTransaction();

    try {
        $user->update($userData);
        Log::info('User berhasil diupdate', ['user_id' => $user->id]);

        if ($user->Terms) {
            $user->Terms->update([
                'device_wifi_mac' => $validatedData['device_wifi_mac'] ?? null,
                'device_lan_mac' => $validatedData['device_lan_mac'] ?? null,
            ]);
            Log::info('Terms berhasil diupdate');
        }

        if ($user->Employee) {
            $user->Employee->update([
                'status' => $validatedData['status'] ?? 'Active',
            ]);
            Log::info('Employee status berhasil diupdate');
        }

        // Ambil role dan permission
         // Assign role saja, tanpa manual sync permission
    $role = Role::findByName($validatedData['role']);
    $user->syncRoles($role);

        DB::commit();
        Log::info('Transaksi berhasil dikommit');

        return redirect()->route('pages.dashboardAdmin')->with('success', 'User Berhasil Diupdate.');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Gagal update user', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return redirect()->route('pages.dashboardAdmin')->with('error', 'Terjadi kesalahan saat mengupdate user.');
    }
}

    public function show($hashedId)
    {
        $user = User::with('terms', 'roles')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$user) {
            abort(404, 'User not found.');
        }

        $userStatus = ['Active', 'Inactive'];
        $selectedStatus = old('status', $user->status ?? '');

        // Dapatkan role pertama user (untuk selected value)
        $selectedRole = old('role', optional($user->roles->first())->name ?? '');

        // Dapatkan semua roles sebagai array [name => name]
        $roles = Role::pluck('name', 'name')->all();

        return view('pages.dashboardAdmin.show', [
            'user' => $user,
            'hashedId' => $hashedId,
            'userStatus' => $userStatus,
            'selectedStatus' => $selectedStatus,
            'roles' => $roles,
            'selectedRole' => $selectedRole
        ]);
    }
    public function create()
    {
        $roles = Role::pluck('name', 'name')->all();

        $permissions = Permission::all();
        return view('pages.dashboardAdmin.create', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $validatedData = $request->validate([
            'password' => ['nullable', 'string', 'min:7', 'max:12', new NoXSSInput()],
            'username' => [
                'required',
                'string',
                'max:12',
                'min:7',
                'regex:/^[a-zA-Z0-9_-]+$/',
                'unique:users,username',
                new NoXSSInput()
            ],
            'device_lan_mac' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/',
                function ($attribute, $value, $fail) {
                    if ($value && Terms::where('device_lan_mac', $value)->whereNotNull('device_lan_mac')->exists()) {
                        $fail("Alamat LAN MAC sudah terdaftar.");
                    }
                },
            ],
            'device_wifi_mac' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/',
                function ($attribute, $value, $fail) {
                    if ($value && Terms::where('device_wifi_mac', $value)->whereNotNull('device_wifi_mac')->exists()) {
                        $fail("Alamat WiFi MAC sudah terdaftar.");
                    }
                },
            ],
            'status' => ['nullable', 'string', 'in:Active,Inactive', new NoXSSInput()],
            'role' => ['required', 'string', 'exists:roles,name'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.string' => 'Username hanya boleh berupa teks.',
            'username.max' => 'Username maksimal terdiri dari 12 karakter.',
            'username.min' => 'Username minimal terdiri dari 7 karakter.',
            'username.regex' => 'Username hanya boleh mengandung huruf, angka, tanda hubung, atau underscore.',
            'username.unique' => 'Username sudah terdaftar. Silakan pilih username lain.',
            'password.string' => 'Password harus berupa teks.',
            'password.min' => 'Password minimal terdiri dari 7 karakter.',
            'password.max' => 'Password maksimal terdiri dari 12 karakter.',
            'roles.required' => 'Paling sedikit satu role harus dipilih.',
            'roles.string' => 'Format roles tidak valid.',
        ]);
        try {
            DB::beginTransaction();
            // Simpan Terms dulu, baru User
            $terms = Terms::create([
                'device_wifi_mac' => $validatedData['device_wifi_mac'] ?? null,
                'device_lan_mac' => $validatedData['device_lan_mac'] ?? null,
            ]);
            $user = User::create([
                'username' => $validatedData['username'],
                'password' => $validatedData['password'] ? Hash::make($validatedData['password']) : null,
                'status' => $validatedData['status'] ?? 'Active',
                'terms_id' => $terms->id,
            ]);
            // $user->syncRoles($validatedData['role']);
            // DB::commit();
             // Step 1: Assign role
     $user->syncRoles($validatedData['role']);

    $role = Role::findByName($validatedData['role']);
    $user->syncRoles($role);
$user->syncPermissions($role->permissions); 
    DB::commit();
            return redirect()->route('pages.dashboardAdmin')->with('success', 'User berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
                ->withInput();
        }
    }
    //     public function update(Request $request, $hashedId)
//     {
//         $user = User::with('Terms', 'roles','Employee')->get()->first(function ($u) use ($hashedId) {
//             $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
//             return $expectedHash === $hashedId;
//         });
//         if (!$user) {
//             return redirect()->route('pages.dashboardAdmin')->with('error', 'ID tidak valid.');
//         }
//         $validatedData = $request->validate([
//             'device_lan_mac' => ['nullable', 'string', 'max:255', new NoXSSInput()],
//             'device_wifi_mac' => ['nullable', 'string', 'max:255', new NoXSSInput()],
//             'password' => ['nullable', 'string', 'min:7', 'max:12', new NoXSSInput()],
//             'username' => [
//                 'required',
//                 'string',
//                 'max:12',
//                 'min:7',
//                 'regex:/^[a-zA-Z0-9_-]+$/',
//                 Rule::unique('users')->ignore($user->id),
//                 new NoXSSInput()
//             ],
//             'status' => ['nullable', 'string', 'in:Active,Inactive,Pending,Mutation', new NoXSSInput()],
//             // 'role' => ['required', 'string', 'exists:roles,name'],
//           'role' => ['required', 'uuid', 'exists:roles,id'],



    //         ], [
//             'username.required' => 'Username is required.',
//             'username.string' => 'Username must be a text.',
//             'username.max' => 'Username can have a maximum of 12 characters.',
//             'username.min' => 'Username must have at least 7 characters.',
//             'username.regex' => 'Username can only contain letters, numbers, hyphens, or underscores.',
//             'username.unique' => 'Username is already registered. Please choose another one.',


    //             'password.string' => 'Password must be a text.',
//             'password.min' => 'Password must have at least 7 characters.',
//             'password.max' => 'Password can have a maximum of 12 characters.',
//             'phone.max' => 'Phone number can have a maximum of 13 characters.',
//             'device_lan_mac.regex' => 'Format LAN MAC tidak valid. Gunakan format: XX:XX:XX:XX:XX:XX atau XX-XX-XX-XX-XX-XX',
//             'device_wifi_mac.regex' => 'Format WiFi MAC tidak valid. Gunakan format: XX:XX:XX:XX:XX:XX atau XX-XX-XX-XX-XX-XX',
//             'roles.required' => 'Paling sedikit satu role harus dipilih.',
//             'roles.string' => 'Format roles tidak valid.',

    //         ]);

    //         // Tidak perlu implode untuk user_type karena sudah string

    //         $userData = [
//             'username' => $validatedData['username'],
//         ];

    //         if (!empty($validatedData['password'])) {
//             $userData['password'] = bcrypt($validatedData['password']);
//         }

    //         DB::beginTransaction();
//         $user->update($userData);

    //         if ($user->Terms) {
//             $user->Terms->update([
//                 'device_wifi_mac' => !empty($validatedData['device_wifi_mac']) ? $validatedData['device_wifi_mac'] : null,
//                 'device_lan_mac' => !empty($validatedData['device_lan_mac']) ? $validatedData['device_lan_mac'] : null,
//             ]);
//         }
//         if ($user->Employee) {
//             $user->Employee->update([
//                 'status' => $validatedData['status'] ?? 'Active',
//             ]);
//         }

//      
   // $user->syncRoles([$validatedData['role']]);
//         $role = Role::where('name', $validatedData['role'])->first();
// if ($role) {
//     $user->syncRoles([$role->id]);
// } else {
//     DB::rollBack();
//     return redirect()->route('pages.dashboardAdmin')->with('error', 'Role tidak ditemukan di database.');
// }

    //         DB::commit();

    //         return redirect()->route('pages.dashboardAdmin')->with('success', 'User Berhasil Diupdate.');
//     }


}