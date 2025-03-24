<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class dashboardAdminController extends Controller
{
    public function index()
    {
        $totaluser = User::count();
        return view('pages.dashboardAdmin.dashboardAdmin');
    }
    public function create()
    {
        return view('pages.dashboardAdmin.create');
    }
   
    public function getUsers()
    {
        $users = User::with('Permissions')->select(['id', 'name', 'username', 'password', 'phone', 'user_type', 'role', 'permission_id', 'created_at'])->get()
            ->map(function ($user) {
                $user->id_hashed = substr(hash('sha256', $user->id . env('APP_KEY')), 0, 8);
                $user->action = '
                <a href="' . route('dashboardAdmin.edit', $user->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user">
                    <i class="fas fa-user-edit text-secondary"></i>
                </a>';
                return $user;
            });

        return DataTables::of($users)
            ->addColumn('created_at', function ($user) {
                return Carbon::parse($user->created_at)->format('d-m-Y H:i:s');
            })
            ->addColumn('device_wifi_mac', function ($user) {
                return $user->Permissions ? $user->Permissions->device_wifi_mac : 'No Permission'; // Tampilkan nama permission
            })
            ->addColumn('device_lan_mac', function ($user) {
                return $user->Permissions ? $user->Permissions->device_lan_mac : 'No Permission'; // Tampilkan nama permission
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    public function edit($hashedId)
    {
        $user = User::with('Permissions')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        $roles = explode(',', $user->getRawOriginal('role'));
        if (!$user) {
            abort(404, 'User not found.');
        }
        return view('pages.dashboardAdmin.edit', compact('user', 'hashedId', 'roles'));
    }

    public function update(Request $request, $hashedId)
    {
        $user = User::with('Permissions')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$user) {
            return redirect()->route('pages.dashboardAdmin')->with('error', 'ID tidak valid.');
        }

        $validatedData = $request->validate([
            'user_type' => ['required', 'string', 'in:Admin,Kasir,Manager', new NoXSSInput()],
            'name' => ['required', 'string', 'max:255', new NoXSSInput()],
            'device_lan_mac' => ['required', 'string', 'max:255', new NoXSSInput()],
            'device_wifi_mac' => ['required', 'string', 'max:255', new NoXSSInput()],
            'role' => ['required', 'array', 'min:1', new NoXSSInput()],
            'role.*' => ['string', Rule::in(['Admin', 'Kasir', 'Manager'])], // Validasi per elemen array

            'password' => ['nullable', 'string', 'min:7', 'max:12', new NoXSSInput()],
            'phone' => ['nullable', 'string', 'max:13', new NoXSSInput()],
            'username' => [
                'required',
                'string',
                'max:12',
                'min:7',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('users')->ignore($user->id),
                new NoXSSInput()
            ],
        ], [
            'username.required' => 'Username is required.',
            'username.string' => 'Username must be a text.',
            'username.max' => 'Username can have a maximum of 12 characters.',
            'username.min' => 'Username must have at least 7 characters.',
            'username.regex' => 'Username can only contain letters, numbers, hyphens, or underscores.',
            'username.unique' => 'Username is already registered. Please choose another one.',

            'user_type.required' => 'User type is required.',
            'user_type.string' => 'User type must be a text.',
            'user_type.in' => 'Please select a valid user type.',

            'role.required' => 'At least one role must be selected.',
            'role.array' => 'Role must be an array.',
            'role.min' => 'At least one role must be selected.',
            'role.*.in' => 'Please select a valid role.',

            'name.required' => 'Name is required.',
            'name.string' => 'Name must be a text.',
            'name.max' => 'Name can have a maximum of 255 characters.',

            'password.string' => 'Password must be a text.',
            'password.min' => 'Password must have at least 7 characters.',
            'password.max' => 'Password can have a maximum of 12 characters.',
            'phone.max' => 'Phone number can have a maximum of 13 characters.',

        ]);

        // Tidak perlu implode untuk user_type karena sudah string
        $user_type = $validatedData['user_type'];

        // Gunakan implode untuk role
        $roles = implode(',', $validatedData['role']);

        $userData = [
            'username' => $validatedData['username'],
            'hakakses' => $validatedData['user_type'], // Perbaiki agar sesuai dengan field database
            'user_type' => $user_type,
            'role' => $roles,
        ];

        if (!empty($validatedData['password'])) {
            $userData['password'] = bcrypt($validatedData['password']);
        }

        DB::beginTransaction();
        $user->update($userData);
        if ($user->Permissions) {
            $user->Permissions->update([
                'device_lan_mac' => $validatedData['device_lan_mac'],
                'device_wifi_mac' => $validatedData['device_wifi_mac'],
            ]);
        }
        DB::commit();

        return redirect()->route('pages.dashboardAdmin')->with('success', 'User Berhasil Diupdate.');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_type' => ['required', 'string', 'in:Admin,Kasir,Manager', new NoXSSInput()],
            'name' => ['required', 'string', 'max:255', new NoXSSInput()],
            'role' => ['required', 'array', 'min:1', new NoXSSInput()],
            'role.*' => ['string', Rule::in(['Admin', 'Kasir', 'Manager'])],
            'password' => ['nullable', 'string', 'min:7', 'max:12', new NoXSSInput()],
            'phone' => ['nullable', 'string', 'max:13', new NoXSSInput()],
            'username' => [
                'required',
                'string',
                'max:12',
                'min:7',
                'regex:/^[a-zA-Z0-9_-]+$/',
                'unique:users,username',
                new NoXSSInput()
            ],
            'permission_id' => [
                'nullable',
                'string',
                'max:255',
            ],
            'device_lan_mac' => ['required', 'string', 'max:255', 'unique:permissions,device_lan_mac', new NoXSSInput()],
            'device_wifi_mac' => ['required', 'string', 'max:255', 'unique:permissions,device_wifi_mac', new NoXSSInput()],
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.string' => 'Username hanya boleh berupa teks.',
            'username.max' => 'Username maksimal terdiri dari 12 karakter.',
            'username.min' => 'Username minimal terdiri dari 7 karakter.',
            'username.regex' => 'Username hanya boleh mengandung huruf, angka, tanda hubung, atau underscore.',
            'username.unique' => 'Username sudah terdaftar. Silakan pilih username lain.',
            'user_type.required' => 'Hak akses wajib dipilih.',
            'user_type.string' => 'Hak akses harus berupa teks.',
            'user_type.in' => 'Pilih hak akses yang valid.',
            'role.required' => 'Setidaknya satu role harus dipilih.',
            'role.array' => 'Role harus dalam format array.',
            'role.min' => 'Setidaknya satu role harus dipilih.',
            'role.*.in' => 'Pilih role yang valid. Hanya Admin, Kasir, atau Manager yang diperbolehkan.',
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama hanya boleh berupa teks.',
            'name.max' => 'Nama maksimal terdiri dari 255 karakter.',
            'password.string' => 'Password harus berupa teks.',
            'password.min' => 'Password minimal terdiri dari 7 karakter.',
            'password.max' => 'Password maksimal terdiri dari 12 karakter.',
        ]);

        try {
            DB::beginTransaction();
            $Permissions = Permission::create([

                'device_lan_mac' => $validatedData['device_lan_mac'],
                'device_wifi_mac' => $validatedData['device_wifi_mac'],
            ]);

            User::create([
                'name' => $validatedData['name'],
                'username' => $validatedData['username'],
                'password' => Hash::make($validatedData['password']),
                'user_type' => $validatedData['user_type'],
                'role' => implode(',', (array) $validatedData['role']),
                'phone' => $validatedData['phone'] ?? null, // Menambahkan field phone jika ada
                'permission_id' => $Permissions->id,

            ]);



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