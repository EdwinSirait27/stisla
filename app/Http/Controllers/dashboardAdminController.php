<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Yajra\DataTables\DataTables;
use App\Models\Terms;
use App\Models\Stores;
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
        $storeList = Stores::select('name')->distinct()->get();
        return view('pages.dashboardAdmin.dashboardAdmin', compact('storeList'));
    }
//         public function getUsers(Request $request)
// {
//     $users = User::with(['Terms', 'roles',
//      'Employee.store' => fn($q) => $q->wherePivot('is_primary', true),
//     'Employee.position' => fn($q) => $q->wherePivot('is_primary', true),'Employee.grading'])
//         ->select(['id', 'username', 'employee_id', 'password', 'terms_id', 'created_at'])
//         ->get()
//         ->map(function ($user) {
//             $user->id_hashed = substr(hash('sha256', $user->id . config('app.key')), 0, 8);
//             $user->action = '
//                 <a href="' . route('dashboardAdmin.edit', $user->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" title="Edit User: ' . e($user->username) . '">
//                     <i class="fas fa-user-edit text-secondary"></i>
//                 </a>';
//             return $user;
//         });

//     return DataTables::of($users)
//         ->addColumn('checkbox', function ($user) {
//             return '<input type="checkbox" class="user-checkbox" value="' . e($user->id) . '">';
//         })
//         ->addColumn('roles', function ($user) {
//             if (is_array($user->roles)) {
//                 return implode(', ', $user->roles);
//             } elseif ($user->roles instanceof \Illuminate\Support\Collection) {
//                 return $user->roles->pluck('name')->implode(', ');
//             }
//             return 'Empty';
//         })
//         ->addColumn('device_lan_mac', fn($user) => optional($user->Terms)->device_lan_mac ?? 'Empty')
//         ->addColumn('employee_name', fn($user) => optional($user->Employee)->employee_name ?? 'Empty')
//         ->addColumn('store_name', fn($user) => optional($user->Employee)->store->first()?->name ?? 'Empty')
// ->addColumn('position_name', fn($user) => optional($user->Employee)->position->first()?->name ?? 'Empty')
//         ->addColumn('grading_name', fn($user) => optional(optional($user->Employee)->grading)->grading_name ?? 'Empty')
//         ->addColumn('pin', fn($user) => optional($user->Employee)->pin ?? 'Empty')
//         ->addColumn('device_wifi_mac', fn($user) => optional($user->Terms)->device_wifi_mac ?? 'Empty')
//         ->addColumn('status', fn($user) => optional($user->Employee)->status ?? 'Empty')
//         ->rawColumns(['checkbox', 'device_lan_mac', 'device_wifi_mac', 'action'])
//         ->make(true);
// }
public function getUsers(Request $request)
{
    $users = User::with([
        'Terms',
        'roles',
        'Employee.store'    => fn($q) => $q->wherePivot('is_primary', true),
        'Employee.position' => fn($q) => $q->wherePivot('is_primary', true),
        'Employee.grading',
    ])
        ->select(['id', 'username', 'employee_id', 'password', 'terms_id', 'created_at',
            // ← tambah 3 kolom ini supaya hasTwoFactorEnabled() & requiresTwoFactor() bisa jalan
            'two_factor_secret',
            'two_factor_confirmed_at',
            'two_factor_required',
        ])
        ->get()
        ->map(function ($user) {
            $user->id_hashed = substr(hash('sha256', $user->id . env('APP_KEY')), 0, 8);
            $user->action = '
                <a href="' . route('dashboardAdmin.edit', $user->id_hashed) . '" class="mx-3"
                   data-bs-toggle="tooltip" title="Edit User: ' . e($user->username) . '">
                    <i class="fas fa-user-edit text-secondary"></i>
                </a>';
            return $user;
        });

    return DataTables::of($users)
        ->addColumn('checkbox', fn($user) =>
            '<input type="checkbox" class="user-checkbox" value="' . e($user->id) . '">'
        )
        ->addColumn('roles', function ($user) {
            if ($user->roles instanceof \Illuminate\Support\Collection) {
                return $user->roles->pluck('name')->implode(', ');
            }
            return 'Empty';
        })
        ->addColumn('device_lan_mac',   fn($u) => optional($u->Terms)->device_lan_mac    ?? 'Empty')
        ->addColumn('employee_name',    fn($u) => optional($u->Employee)->employee_name   ?? 'Empty')
        ->addColumn('store_name',       fn($u) => optional($u->Employee)->store->first()?->name     ?? 'Empty')
        ->addColumn('position_name',    fn($u) => optional($u->Employee)->position->first()?->name  ?? 'Empty')
        ->addColumn('grading_name',     fn($u) => optional(optional($u->Employee)->grading)->grading_name ?? 'Empty')
        ->addColumn('pin',              fn($u) => optional($u->Employee)->pin             ?? 'Empty')
        ->addColumn('device_wifi_mac',  fn($u) => optional($u->Terms)->device_wifi_mac    ?? 'Empty')
        ->addColumn('status',           fn($u) => optional($u->Employee)->status          ?? 'Empty')

        // ── Kolom baru: status 2FA ────────────────────────────────────
        ->addColumn('two_factor_status', function ($user) {
            if ($user->hasTwoFactorEnabled()) {
                return '<span class="badge bg-success">Aktif</span>';
            }
            if ($user->two_factor_required) {
                return '<span class="badge bg-warning text-dark">Belum Setup</span>';
            }
            return '<span class="badge bg-secondary">Tidak Aktif</span>';
        })

        // ── Kolom baru: action 2FA ────────────────────────────────────
        // ->addColumn('two_factor_action', function ($user) {
        //     $toggleLabel = $user->two_factor_required ? 'Batalkan Wajib' : 'Wajibkan';
        //     $toggleClass = $user->two_factor_required ? 'btn-outline-warning' : 'btn-outline-secondary';

        //     $toggleBtn = '
        //         <form method="POST" action="' . route('admin.2fa.toggle-required', $user->id) . '" class="d-inline">
        //             ' . csrf_field() . '
        //             <button type="submit" class="btn btn-sm ' . $toggleClass . '" title="' . $toggleLabel . ' 2FA">
        //                 ' . $toggleLabel . '
        //             </button>
        //         </form>';

        //     $resetBtn = '';
        //     if ($user->hasTwoFactorEnabled()) {
        //         $resetBtn = '
        //         <form method="POST" action="' . route('admin.2fa.disable', $user->id) . '" class="d-inline"
        //             onsubmit="return confirm(\'Reset 2FA untuk ' . e($user->username) . '?\')">
        //             ' . csrf_field() . '
        //             <button type="submit" class="btn btn-sm btn-outline-danger ms-1" title="Reset 2FA">
        //                 Reset
        //             </button>
        //         </form>';
        //     }

        //     return $toggleBtn . $resetBtn;
        // })
        ->addColumn('two_factor_action', function ($user) {
    $toggleLabel = $user->two_factor_required ? 'Batalkan Wajib' : 'Wajibkan';
    $toggleClass = $user->two_factor_required ? 'btn-outline-warning' : 'btn-outline-secondary';

    $toggleBtn = '
        <form method="POST" action="' . route('admin.2fa.toggle-required', $user->id) . '" class="d-inline">
            ' . csrf_field() . '
            <button type="submit" class="btn btn-sm ' . $toggleClass . '">
                ' . $toggleLabel . '
            </button>
        </form>';

    $resetBtn = '';
    if ($user->hasTwoFactorEnabled()) {
        // Sudah setup — tampilkan tombol Reset
        $resetBtn = '
            <form method="POST" action="' . route('admin.2fa.disable', $user->id) . '" class="d-inline"
                onsubmit="return confirm(\'Reset 2FA untuk ' . e($user->username) . '?\')">
                ' . csrf_field() . '
                <button type="submit" class="btn btn-sm btn-outline-danger ms-1">
                    Reset
                </button>
            </form>';
    } elseif ($user->two_factor_required) {
        // Diwajibkan tapi belum setup — tampilkan info cara setup
        $resetBtn = '
            <span class="ms-1 text-muted small"
                data-bs-toggle="tooltip"
                title="User harus login dan akses /two-factor/setup untuk setup 2FA">
                <i class="fas fa-info-circle text-warning"></i> Belum setup
            </span>';
    }

    return $toggleBtn . $resetBtn;
})

        ->rawColumns([
            'checkbox', 'device_lan_mac', 'device_wifi_mac', 'action',
            'two_factor_status', 'two_factor_action', // ← tambah ke rawColumns
        ])
        ->make(true);
}
public function bulkUpdateRole(Request $request)
{
    $request->validate([
        'user_ids' => 'required|array',
        'user_ids.*' => 'uuid'
    ]);

    try {
        $users = User::whereIn('id', $request->user_ids)->get();

        foreach ($users as $user) {
            // Hapus semua role lama, lalu assign 'Human'
            $user->syncRoles(['Human']);
        }

        return response()->json(['success' => true, 'message' => 'Selected users successfully updated to Human role.']);
    } catch (\Exception $e) {
        Log::error('Bulk update error', ['message' => $e->getMessage()]);
        return response()->json(['success' => false, 'message' => 'Something went wrong.']);
    }
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

    $userStatus     = ['Active', 'Inactive'];
    $selectedStatus = old('status', $user->Employee->status ?? '');
    $roles          = Role::pluck('name', 'name')->all();
    $selectedRoles  = old('roles', $user->roles->pluck('name')->toArray());

    return view('pages.dashboardAdmin.edit', [
        'user'           => $user,
        'hashedId'       => $hashedId,
        'userStatus'     => $userStatus,
        'selectedStatus' => $selectedStatus,
        'roles'          => $roles,
        'selectedRoles'  => $selectedRoles,
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
    'password' => [
        'nullable',
        'string',
        'min:8',
        'max:20',
        'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])\S+$/',
        new NoXSSInput(),
    ],
    'username' => [
        'required',
        'string',
        'max:20',
        'min:8',
        'regex:/^[a-zA-Z0-9_-]+$/',
        Rule::unique('users')->ignore($user->id),
        new NoXSSInput()
    ],
    'status' => ['nullable', 'string', 'in:Active,Inactive,Pending,Mutation', new NoXSSInput()],
    'pin'    => ['required', 'max:4', new NoXSSInput()],
    'roles'  => ['required', 'array', 'min:1'],
    'roles.*' => ['string', 'exists:roles,name'],
    'permissions' => ['nullable'],
], [
    'password.regex' => 'Password must contain at least 1 uppercase letter, 1 lowercase letter, 1 number, and 1 symbol, and must not contain spaces.',
    'password.min'   => 'Password must be at least 8 characters.',
    'password.max'   => 'Password maximum 20 characters.',
    'roles.required' => 'Minimal satu role harus dipilih.',
    'roles.min'      => 'Minimal satu role harus dipilih.',
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
                    'pin' => $validatedData['pin'] ?? 'Active',
                ]);
                Log::info('Employee status berhasil diupdate');
            }

            // Ambil role dan permission
             $roles = Role::whereIn('name', $validatedData['roles'])->get();
    $user->syncRoles($roles);
     $user->update([
        'active_role_hrx' => $validatedData['roles'][0],
        'all_roles_hrx'   => $validatedData['roles'],
    ]);

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
}
