<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\Terms;
use App\Models\UserSession;
use App\Models\Activity;
use Illuminate\Support\Str;





class LoginController extends Controller
{
    public function index()
    {
        return view('pages.login');
    }
    // public function store(Request $request)
    // {
    //     // Validasi tetap sama
    //     $attributes = $request->validate([
    //         'username' => [
    //             'required',
    //             'string',
    //             'min:7',
    //             'max:12',
    //             'regex:/^[a-zA-Z0-9_-]+$/',
    //             new NoXSSInput(),
    //             function ($attribute, $value, $fail) {
    //                 if (strip_tags($value) !== $value) {
    //                     $fail("Input $attribute mengandung tag HTML yang tidak diperbolehkan.");
    //                 }
    //             }
    //         ],
    //         'password' => [
    //             'required',
    //             'string',
    //             'min:4',
    //             'max:12',
    //         ],
    //     ], [
    //         'username.required' => 'Username is required.',
    //         'username.min' => 'Username must be at least 7 characters.',
    //         'username.max' => 'Username cannot be more than 12 characters.',
    //         'password.required' => 'Password is required.',
    //         'password.min' => 'Password must be at least 7 characters.',
    //         'password.max' => 'Password cannot be more than 12 characters.',
    //     ]);
    
    //     // Rate limiting tetap sama
    //     $normalizedUsername = strtolower($request->username);
    //     $rateLimiterKey = "/:{$request->ip()}:{$normalizedUsername}";
    
    //     if (RateLimiter::tooManyAttempts($rateLimiterKey, 5)) {
    //         Log::warning("Rate limiter triggered for username: {$normalizedUsername}");
    //         return back()->withErrors(['/' => 'Terlalu banyak percobaan login. Silakan coba lagi nanti.']);
    //     }
    
    //     try {
    //         $attributes['username'] = $normalizedUsername;
    
    //         if (!Auth::attempt($attributes, $request->boolean('remember'))) {
    //             Log::warning("Failed login attempt for username: {$normalizedUsername}");
    //             RateLimiter::hit($rateLimiterKey, 60);
    //             return back()->withErrors(['/' => 'Username atau Password salah.']);
    //         }
    
    //         $request->session()->regenerate();
    //         RateLimiter::clear($rateLimiterKey);
    
    //         $user = Auth::user();
    //         $currentSessionId = $request->session()->getId();
    
    //         // Cek apakah user sudah memiliki sesi aktif di perangkat lain
    //         $existingSession = UserSession::where('user_id', $user->id)
    //             ->where('session_id', '!=', $currentSessionId)
    //             ->first();
    
    //         if ($existingSession) {
    //             // Jika user tidak memilih "force login", tampilkan konfirmasi
    //             if (!$request->has('force_login')) {
    //                 Auth::logout();
    //                 return back()->with('confirm_force_login', [
    //                     'message' => 'You are already logged in on another device. Will you continue to log out from that device?',
    //                     'username' => $request->username,
    //                     'password' => $request->password,
    //                     'remember' => $request->boolean('remember')
    //                 ]);
    //             }
    
    //             // Jika memilih "Ya", hapus sesi lama
    //             $this->logoutOtherDevices($user);
    //         }
    
    //         // Buat atau update session record
    //         UserSession::updateOrCreate(
    //             [
    //                 'user_id' => $user->id,
    //                 'session_id' => $currentSessionId,
    //             ],
    //             [
    //                 'ip_address' => $request->ip(),
    //                 'last_activity' => now(),
    //                 'device_type' => $request->header('User-Agent')
    //             ]
    //         );
    
    //         // Daftar role yang boleh bypass MAC check
    //         $privilegedRoles = [
    //             'Admin',
    //             'Head Warehouse',
    //             'Head Buyer',
    //             'Head Finance',
    //             'GM',
    //             'Finance',
    //             'Buyer',
    //             'Warehouse'
    //         ];
    
    //         // Role yang harus selalu cek MAC (termasuk Manager Store)
    //         $mustCheckMacRoles = [
    //             'Manager Store',
    //             'Supervisor Store',
    //             'Cashier Store'
    //         ];
    
    //         // Logika pengecekan MAC dengan Spatie
    //         $shouldCheckMac = true;
            
    //         foreach ($privilegedRoles as $role) {
    //             if ($user->hasRole($role)) {
    //                 $shouldCheckMac = false;
    //                 break;
    //             }
    //         }
            
    //         foreach ($mustCheckMacRoles as $role) {
    //             if ($user->hasRole($role)) {
    //                 $shouldCheckMac = true;
    //                 break;
    //             }
    //         }
    
    //         if ($shouldCheckMac) {
    //             $macAddresses = $this->getAllMacAddresses();
    
    //             if (empty($macAddresses)) {
    //                 Log::warning("No MAC addresses detected for IP: {$request->ip()}");
    //                 Auth::logout();
    //                 return back()->withErrors(['/' => 'Gagal mendapatkan identitas perangkat. Silakan hubungi administrator.'])->withInput();
    //             }
    
    //             Log::info("Detected MAC addresses: " . implode(', ', $macAddresses));
    
    //             $permission = null;
    //             foreach ($macAddresses as $mac) {
    //                 $normalizedMac = str_replace(':', '-', strtoupper($mac));
    //                 $permission = Permission::where('device_wifi_mac', $normalizedMac)
    //                     ->orWhere('device_lan_mac', $normalizedMac)
    //                     ->first();
    
    //                 if ($permission) {
    //                     break;
    //                 }
    //             }
    //             if (!$permission) {
    //                 Log::warning("Unauthorized device attempt from IP: {$request->ip()}");
    //                 Auth::logout();
    //                 return back()->withErrors(['/' => 'Perangkat ini tidak diizinkan untuk login.'])->withInput();
    //             }
    
    //             Log::info("User {$normalizedUsername} passed MAC address verification");
    //         } else {
    //             Log::info("Bypassed MAC check for privileged user: {$normalizedUsername}");
    //         }
    
    //         // Set remember token
    //         if ($request->boolean('remember')) {
    //             $user->setRememberToken(Str::random(60));
    //             $user->save();
    //         }
    
    //         // Redirect berdasarkan role menggunakan Spatie
    //         if ($user->hasRole('Admin')) {
    //             return redirect()->route('pages.dashboardAdmin')->with('success', 'You have successfully logged in, keep up the good work!!!');
    //         } elseif ($user->hasRole('Head Warehouse')) {
    //             return redirect()->route('pages.dashboarHeadWarehouse')->with('success', 'You have successfully logged in, keep up the good work!!!');
    //         } elseif ($user->hasRole('Head Buyer')) {
    //             return redirect()->route('pages.dashboardHeadBuyer')->with('success', 'You have successfully logged in, keep up the good work!!!');
    //         } elseif ($user->hasRole('Cashier Store')) {
    //             return redirect()->route('pages.dashboardKasir')->with('success', 'You have successfully logged in, keep up the good work!!!');
    //         } elseif ($user->hasRole('Supervisor Store')) {
    //             return redirect()->route('pages.dashboardSupervisor')->with('success', 'You have successfully logged in, keep up the good work!!!');
    //         } elseif ($user->hasRole('Manager Store')) {
    //             return redirect()->route('pages.dashboardManager')->with('success', 'You have successfully logged in, keep up the good work!!!');
    //         }
               
    
    //         // Fallback untuk role tidak dikenal
    //         Log::warning("User {$normalizedUsername} has no valid role assigned");
    //         Auth::logout();
    //         return redirect('/')->with('warning', 'Akun Anda tidak memiliki peran yang valid.');
    
    //     } catch (\Exception $e) {
    //         Log::error("Login error: " . $e->getMessage());
    //         return back()->withErrors(['/' => 'Terjadi kesalahan. Silakan coba lagi.']);
    //     }
    // }
    public function store(Request $request)
{
    // Validasi tetap sama
    $attributes = $request->validate([
        'username' => [
            'required',
            'string',
            'min:7',
            'max:12',
            'regex:/^[a-zA-Z0-9_-]+$/',
            new NoXSSInput(),
            function ($attribute, $value, $fail) {
                if (strip_tags($value) !== $value) {
                    $fail("Input $attribute mengandung tag HTML yang tidak diperbolehkan.");
                }
            }
        ],
        'password' => [
            'required',
            'string',
            'min:4',
            'max:12',
        ],
    ], [
        'username.required' => 'Username is required.',
        'username.min' => 'Username must be at least 7 characters.',
        'username.max' => 'Username cannot be more than 12 characters.',
        'password.required' => 'Password is required.',
        'password.min' => 'Password must be at least 7 characters.',
        'password.max' => 'Password cannot be more than 12 characters.',
    ]);
    // Rate limiting tetap sama
    $normalizedUsername = strtolower($request->username);
    $rateLimiterKey = "/:{$request->ip()}:{$normalizedUsername}";
    if (RateLimiter::tooManyAttempts($rateLimiterKey, 5)) {
        Log::warning("Rate limiter triggered for username: {$normalizedUsername}");
        return back()->withErrors(['/' => 'Terlalu banyak percobaan login. Silakan coba lagi nanti.']);
    }
    try {
        $attributes['username'] = $normalizedUsername;
        if (!Auth::attempt($attributes, $request->boolean('remember'))) {
            Log::warning("Failed login attempt for username: {$normalizedUsername}");
            RateLimiter::hit($rateLimiterKey, 60);
            return back()->withErrors(['/' => 'Username atau Password salah.']);
        }

        $request->session()->regenerate();
        RateLimiter::clear($rateLimiterKey);

        $user = Auth::user();
        $currentSessionId = $request->session()->getId();

        // Cek apakah user sudah memiliki sesi aktif di perangkat lain
        $existingSession = UserSession::where('user_id', $user->id)
            ->where('session_id', '!=', $currentSessionId)
            ->first();

        if ($existingSession) {
            // Jika user tidak memilih "force login", tampilkan konfirmasi
            if (!$request->has('force_login')) {
                Auth::logout();
                return back()->with('confirm_force_login', [
                    'message' => 'You are already logged in on another device. Will you continue to log out from that device?',
                    'username' => $request->username,
                    'password' => $request->password,
                    'remember' => $request->boolean('remember')
                ]);
            }

            // Jika memilih "Ya", hapus sesi lama
            $this->logoutOtherDevices($user);
        }

        // Buat atau update session record
        UserSession::updateOrCreate(
            [
                'user_id' => $user->id,
                'session_id' => $currentSessionId,
            ],
            [
                'ip_address' => $request->ip(),
                'last_activity' => now(),
                'device_type' => $request->header('User-Agent')
            ]
        );

        // Daftar role yang boleh bypass MAC check menggunakan Spatie
        $privilegedRoles = [
            'Admin',
            'head-warehouse',
            'head-buyer',
            'head-finance',
            'gm',
            'finance',
            'buyer',
            'warehouse'
        ];

        // Role yang harus selalu cek MAC (termasuk Manager Store)
        $mustCheckMacRoles = [
            'Manager Store',
            'supervisor-store',
            'cashier-store'
        ];

        // Logika pengecekan MAC
        $shouldCheckMac = !$user->hasAnyRole($privilegedRoles) || 
                        $user->hasAnyRole($mustCheckMacRoles);

        if ($shouldCheckMac) {
            $macAddresses = $this->getAllMacAddresses();

            if (empty($macAddresses)) {
                Log::warning("No MAC addresses detected for IP: {$request->ip()}");
                Auth::logout();
                return back()->withErrors(['/' => 'Gagal mendapatkan identitas perangkat. Silakan hubungi administrator.'])->withInput();
            }

            Log::info("Detected MAC addresses: " . implode(', ', $macAddresses));

            $permission = null;
            foreach ($macAddresses as $mac) {
                $normalizedMac = str_replace(':', '-', strtoupper($mac));
                $permission = Terms::where('device_wifi_mac', $normalizedMac)
                    ->orWhere('device_lan_mac', $normalizedMac)
                    ->first();

                if ($permission)
                    break;
            }

            if (!$permission) {
                Log::warning("Unauthorized device attempt from IP: {$request->ip()}");
                Auth::logout();
                return back()->withErrors(['/' => 'Perangkat ini tidak diizinkan untuk login.'])->withInput();
            }

            Log::info("User {$normalizedUsername} passed MAC address verification");
        } else {
            Log::info("Bypassed MAC check for privileged user: {$normalizedUsername}");
        }

        // Set remember token
        if ($request->boolean('remember')) {
            $user->setRememberToken(Str::random(60));
            $user->save();
        }

        // Redirect berdasarkan role menggunakan Spatie
        $dashboardRoutes = [
            'Admin' => 'pages.dashboardAdmin',
            'head-warehouse' => 'pages.dashboarHeadWarehouse',
            'head-buyer' => 'pages.dashboardHeadBuyer',
            'cashier-store' => 'pages.dashboardKasir',
            'supervisor-store' => 'pages.dashboardSupervisor',
            'Manager Store' => 'pages.dashboardManager'
        ];

        foreach ($dashboardRoutes as $role => $route) {
            if ($user->hasRole($role)) {
                Log::info("User {$normalizedUsername} logged in with role: {$role}");
                return redirect()->route($route)->with('success', 'Anda berhasil login, semangat bekerja!!!');
            }
        }
        

        // Fallback untuk role tidak dikenal
        Log::warning("User {$normalizedUsername} has no valid role assigned");
        Auth::logout();
        return redirect('/')->with('warning', 'Akun Anda tidak memiliki peran yang valid.');

    } catch (\Exception $e) {
        Log::error("Login error: " . $e->getMessage());
        return back()->withErrors(['/' => 'Terjadi kesalahan. Silakan coba lagi.']);
    }
}


    /**
     * Logout dari perangkat lain
     */
 
    protected function logoutOtherDevices($user)
    {
        // Hapus semua session lain milik user ini, termasuk session saat ini
        UserSession::where('user_id', $user->id)->delete();
    }
    /**
     * Mendapatkan semua MAC addresses dari perangkat
     * 
     * @return array
     */
    private function getAllMacAddresses()
    {
        $macAddresses = [];

        // Deteksi berdasarkan sistem operasi
        if (PHP_OS_FAMILY === 'Windows') {
            // Windows - gunakan ipconfig
            $output = [];
            exec('ipconfig /all', $output);

            foreach ($output as $line) {
                // Format dengan dash: xx-xx-xx-xx-xx-xx
                if (preg_match('/Physical Address.*: ([0-9A-F]{2}-[0-9A-F]{2}-[0-9A-F]{2}-[0-9A-F]{2}-[0-9A-F]{2}-[0-9A-F]{2})/i', $line, $matches)) {
                    // Konversi format MAC dari xx-xx-xx-xx-xx-xx menjadi xx:xx:xx:xx:xx:xx untuk konsistensi
                    $mac = str_replace('-', ':', $matches[1]);
                    $macAddresses[] = ($mac);
                }

                // Format dengan colon: xx:xx:xx:xx:xx:xx
                if (preg_match('/Physical Address.*: ([0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2})/i', $line, $matches)) {
                    $macAddresses[] = ($matches[1]);
                }
            }
        } else {
            // Linux/Unix/macOS - gunakan ifconfig atau ip
            if ($this->command_exists('ifconfig')) {
                $output = [];
                exec('ifconfig', $output);
                $outputStr = implode(' ', $output);

                if (preg_match_all('/([0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2})/i', $outputStr, $matches)) {
                    foreach ($matches[1] as $mac) {
                        $macAddresses[] = ($mac);
                    }
                }
            } elseif ($this->command_exists('ip')) {
                $output = [];
                exec('ip link', $output);
                $outputStr = implode(' ', $output);

                if (preg_match_all('/([0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2})/i', $outputStr, $matches)) {
                    foreach ($matches[1] as $mac) {
                        $macAddresses[] = ($mac);
                    }
                }
            }
        }

        // Hapus duplikat dan kembalikan hasilnya
        return array_unique($macAddresses);
    }

    /**
     * Memeriksa apakah perintah tertentu tersedia
     * 
     * @param string $cmd
     * @return bool
     */
    private function command_exists($cmd)
    {
        $return = shell_exec(sprintf("which %s 2>/dev/null", escapeshellarg($cmd)));
        return !empty($return);
    }

    public function destroy(Request $request)
    {
        // Get current user
        $user = Auth::user();

        if ($user) {
            // Set remember_token menjadi null di database
            $user->setRememberToken(null);

            // Hapus session records
            UserSession::where('user_id', $user->id)->delete();

            // Simpan perubahan
            $user->save();
        }
      
        // Logout user (ini juga menghapus cookie remember_me)
        Auth::logout();
     
        // Invalidate session
        $request->session()->invalidate();

        // Regenerate CSRF token
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Anda berhasil logout');
    }
}



// $user = App\Models\User::create([
//     'terms_id' => '1',   
//      'username' => 'testuser',
//         'password' => bcrypt('password123'),
//     'remember_token' => null,
//         'status' => 'active'
//     ]);
    
    
//     $user = User::create([
//         'terms_id' => 1,
//         'username' => 'testuser',
//         'password' => bcrypt('password123'),
//         'remember_token' => null,
//         'status' => 'active'
//     ]);
    
    
//     $user->assignRole('Admin');
    
//     $user->givePermissionTo(['edit articles', 'delete articles']);
    
//     public function store(Request $request)
//     {
//         // Validasi tetap sama
//         $attributes = $request->validate([
//             'username' => [
//                 'required',
//                 'string',
//                 'min:7',
//                 'max:12',
//                 'regex:/^[a-zA-Z0-9_-]+$/',
//                 new NoXSSInput(),
//                 function ($attribute, $value, $fail) {
//                     if (strip_tags($value) !== $value) {
//                         $fail("Input $attribute mengandung tag HTML yang tidak diperbolehkan.");
//                     }
//                 }
//             ],
//             'password' => [
//                 'required',
//                 'string',
//                 'min:4',
//                 'max:12',
//             ],
//         ], [
//             'username.required' => 'Username is required.',
//             'username.min' => 'Username must be at least 7 characters.',
//             'username.max' => 'Username cannot be more than 12 characters.',
//             'password.required' => 'Password is required.',
//             'password.min' => 'Password must be at least 7 characters.',
//             'password.max' => 'Password cannot be more than 12 characters.',
//         ]);
    
//         // Rate limiting tetap sama
//         $normalizedUsername = strtolower($request->username);
//         $rateLimiterKey = "/:{$request->ip()}:{$normalizedUsername}";
    
//         if (RateLimiter::tooManyAttempts($rateLimiterKey, 5)) {
//             Log::warning("Rate limiter triggered for username: {$normalizedUsername}");
//             return back()->withErrors(['/' => 'Terlalu banyak percobaan login. Silakan coba lagi nanti>    }
//      try {
//             $attributes['username'] = $normalizedUsername;
    
//             if (!Auth::attempt($attributes, $request->boolean('remember'))) {
//                 Log::warning("Failed login attempt for username: {$normalizedUsername}");
//                 RateLimiter::hit($rateLimiterKey, 60);
//                 return back()->withErrors(['/' => 'Username atau Password salah.']);
//             }
    
//             $request->session()->regenerate();
//             RateLimiter::clear($rateLimiterKey);
    
//             $user = Auth::user();
//             $currentSessionId = $request->session()->getId();
    
//             // Cek apakah user sudah memiliki sesi aktif di perangkat lain
//             $existingSession = UserSession::where('user_id', $user->id)
//                 ->where('session_id', '!=', $currentSessionId)
//                 ->first();
    
//             if ($existingSession) {
//                 // Jika user tidak memilih "force login", tampilkan konfirmasi
//                 if (!$request->has('force_login')) {
//                     Auth::logout();
//                     return back()->with('confirm_force_login', [
//                         'message' => 'You are already logged in on another device. Will you continue t>                    'username' => $request->username,
//                         'password' => $request->password,
//                         'remember' => $request->boolean('remember')
//                     ]);
//                 }
    
//                 // Jika memilih "Ya", hapus sesi lama
//                 $this->logoutOtherDevices($user);
//             }
    
//             // Buat atau update session record
//             UserSession::updateOrCreate(
//                 [
//                     'user_id' => $user->id,
//                     'session_id' => $currentSessionId,
//                 ],
//                 [
//                     'ip_address' => $request->ip(),
//                     'last_activity' => now(),
//                     'device_type' => $request->header('User-Agent')
//                 ]
//             );
    
//             // Lanjutan logika sebelumnya (MAC Check, Role Routing dll)
    
//             // Daftar role yang boleh bypass MAC check
//             $privilegedRoles = [
//      'Admin',
//                 'Head Warehouse',
//                 'Head Buyer',
//                 'Head Finance',
//                 'GM',
//                 'Finance',
//                 'Buyer',
//                 'Warehouse'
//             ];
    
//             // Role yang harus selalu cek MAC (termasuk Manager Store)
//             $mustCheckMacRoles = [
//                 'Manager Store',
//                 'Supervisor Store',
//                 'Cashier Store'
//             ];
    
//             // Logika pengecekan MAC
//             $shouldCheckMac = !in_array($user->user_type, $privilegedRoles) ||
//                 in_array($user->user_type, $mustCheckMacRoles);
    
//             if ($shouldCheckMac) {
//                 $macAddresses = $this->getAllMacAddresses();
    
//                 if (empty($macAddresses)) {
//                     Log::warning("No MAC addresses detected for IP: {$request->ip()}");
//                     Auth::logout();
//                     return back()->withErrors(['/' => 'Gagal mendapatkan identitas perangkat. Silakan >            }
    
//                 Log::info("Detected MAC addresses: " . implode(', ', $macAddresses));
    
//                 $permission = null;
//                 foreach ($macAddresses as $mac) {
//                     $normalizedMac = str_replace(':', '-', strtoupper($mac));
//                     $permission = Permission::where('device_wifi_mac', $normalizedMac)
//                         ->orWhere('device_lan_mac', $normalizedMac)
//                         ->first();
    
//                     if ($permission)
//                         break;
//                 }
    
//                 if (!$permission) {
//                     Log::warning("Unauthorized device attempt from IP: {$request->ip()}");
//                     Auth::logout();
//                     return back()->withErrors(['/' => 'Perangkat ini tidak diizinkan untuk login.'])->>            }
    
//                 Log::info("User {$normalizedUsername} passed MAC address verification");
//       } else {
//                 Log::info("Bypassed MAC check for privileged user: {$normalizedUsername}");
//             }
    
//             // Set remember token
//             if ($request->boolean('remember')) {
//                 $user->setRememberToken(Str::random(60));
//                 $user->save();
//             }
    
//             // Redirect berdasarkan role
//             $dashboardRoutes = [
//                 'isAdmin' => 'pages.dashboardAdmin',
//                 'isHead Warehouse' => 'pages.dashboarHeadWarehouse',
//                 'isHead Buyer' => 'pages.dashboardHeadBuyer',
//                 'isKasir' => 'pages.dashboardKasir',
//                 'isSupervisor' => 'pages.dashboardSupervisor',
//                 'isManager Store' => 'pages.dashboardManagerStore'
//             ];
    
//             foreach ($dashboardRoutes as $gate => $route) {
//                 if (Gate::allows($gate, $user)) {
//                     Log::info("User {$normalizedUsername} logged in with role: {$user->user_type}");
//                     return redirect()->route($route)->with('success', 'Anda berhasil login, semangat b>            }
//             }
    
//             // Fallback untuk role tidak dikenal
//             Log::warning("User {$normalizedUsername} has no valid role assigned");
//             Auth::logout();
//             return redirect('/')->with('warning', 'Akun Anda tidak memiliki peran yang valid.');
    
//         } catch (\Exception $e) {
//             Log::error("Login error: " . $e->getMessage());
//             return back()->withErrors(['/' => 'Terjadi kesalahan. Silakan coba lagi.']);
//         }
//     }
//     /**
//      * Logout dari perangkat lain
//      */
//     // protected function logoutOtherDevices($user)
//     // {
//     //     // Hapus semua session lain milik user ini
//     //     UserSession::where('user_id', $user->id)
//     //         ->where('session_id', '!=', session()->getId())
//     //         ->delete();
//     // }
//     protected function logoutOtherDevices($user)
//     {
//         // Hapus semua session lain milik user ini, termasuk session saat ini
//         UserSession::where('user_id', $user->id)->delete();
//     }
//     /**
//          * Mendapatkan semua MAC addresses dari perangkat
//          *
//          * @return array
//          */
//         private function getAllMacAddresses()
//         {
//             $macAddresses = [];
    
//             // Deteksi berdasarkan sistem operasi
//             if (PHP_OS_FAMILY === 'Windows') {
//                 // Windows - gunakan ipconfig
//                 $output = [];
//                 exec('ipconfig /all', $output);
    
//                 foreach ($output as $line) {
//                     // Format dengan dash: xx-xx-xx-xx-xx-xx
//                     if (preg_match('/Physical Address.*: ([0-9A-F]{2}-[0-9A-F]{2}-[0-9A-F]{2}-[0-9A-F]>                    // Konversi format MAC dari xx-xx-xx-xx-xx-xx menjadi xx:xx:xx:xx:xx:xx untuk >                    $mac = str_replace('-', ':', $matches[1]);
//                         $macAddresses[] = ($mac);
//                     }
    
//                     // Format dengan colon: xx:xx:xx:xx:xx:xx
//                     if (preg_match('/Physical Address.*: ([0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]>                    $macAddresses[] = ($matches[1]);
//                     }
//                 }
//             } else {
//                 // Linux/Unix/macOS - gunakan ifconfig atau ip
//                 if ($this->command_exists('ifconfig')) {
//                     $output = [];
//                     exec('ifconfig', $output);
//                     $outputStr = implode(' ', $output);
    
//                     if (preg_match_all('/([0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:>                    foreach ($matches[1] as $mac) {
//                             $macAddresses[] = ($mac);
    
//      }
//                     }
//                 } elseif ($this->command_exists('ip')) {
//                     $output = [];
//                     exec('ip link', $output);
//                     $outputStr = implode(' ', $output);
    
//                     if (preg_match_all('/([0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:>                    foreach ($matches[1] as $mac) {
//                             $macAddresses[] = ($mac);
//                         }
//                     }
//                 }
//             }
    
//             // Hapus duplikat dan kembalikan hasilnya
//             return array_unique($macAddresses);
//         }
    
//         /**
//          * Memeriksa apakah perintah tertentu tersedia
//          *
//          * @param string $cmd
//          * @return bool
//          */
//         private function command_exists($cmd)
//         {
//             $return = shell_exec(sprintf("which %s 2>/dev/null", escapeshellarg($cmd)));
//             return !empty($return);
//         }
    