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
use App\Models\Permission;
use App\Models\UserSession;
use App\Models\Activity;
use Illuminate\Support\Str;





class LoginController extends Controller
{
    public function index()
    {
        return view('pages.login');
    }


   
//     public function store(Request $request)
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

//         // =============================================
//         // PENAMBAHAN FITUR SINGLE LOGIN DENGAN UUID
//         // =============================================
        
//         // Cek apakah user sudah memiliki sesi aktif di perangkat lain
//         if ($user->session_id && $user->session_id !== $currentSessionId) {
//             // Jika user tidak memilih "force login", tampilkan konfirmasi
//             if (!$request->has('force_login')) {
//                 Auth::logout();
//                 return back()->with('confirm_force_login', [
//                     'message' => 'Anda sudah login di perangkat lain. Lanjutkan akan logout dari perangkat tersebut?',
//                     'username' => $request->username,
//                     'password' => $request->password,
//                     'remember' => $request->boolean('remember')
//                 ]);
//             }
            
//             // Jika memilih "Ya", hapus sesi lama
//             $this->logoutOtherDevices($user);
//         }

//         // Simpan session ID baru (UUID)
//         $user->update(['session_id' => $currentSessionId]);
        
//         // =============================================
//         // LANJUTAN LOGIKA SEBELUMNYA
//         // =============================================

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

//         // Logika pengecekan MAC
//         $shouldCheckMac = !in_array($user->user_type, $privilegedRoles) ||
//             in_array($user->user_type, $mustCheckMacRoles);

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

//                 if ($permission)
//                     break;
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

//         // Redirect berdasarkan role
//         $dashboardRoutes = [
//             'isAdmin' => 'pages.dashboardAdmin',
//             'isHead Warehouse' => 'pages.dashboarHeadWarehouse',
//             'isHead Buyer' => 'pages.dashboardHeadBuyer',
//             'isKasir' => 'pages.dashboardKasir',
//             'isSupervisor' => 'pages.dashboardSupervisor',
//             'isManager Store' => 'pages.dashboardManagerStore'
//         ];

//         foreach ($dashboardRoutes as $gate => $route) {
//             if (Gate::allows($gate, $user)) {
//                 Log::info("User {$normalizedUsername} logged in with role: {$user->user_type}");
//                 return redirect()->route($route)->with('success', 'Anda berhasil login, semangat bekerja!!!');
//             }
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

// /**
//  * Logout dari perangkat lain
//  */
// protected function logoutOtherDevices($user)
// {
//     $user->session_id = null;
//     $user->save();
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
        
        // Lanjutan logika sebelumnya (MAC Check, Role Routing dll)

        // Daftar role yang boleh bypass MAC check
        $privilegedRoles = [
            'Admin',
            'Head Warehouse',
            'Head Buyer',
            'Head Finance',
            'GM',
            'Finance',
            'Buyer',
            'Warehouse'
        ];

        // Role yang harus selalu cek MAC (termasuk Manager Store)
        $mustCheckMacRoles = [
            'Manager Store',
            'Supervisor Store',
            'Cashier Store'
        ];

        // Logika pengecekan MAC
        $shouldCheckMac = !in_array($user->user_type, $privilegedRoles) ||
            in_array($user->user_type, $mustCheckMacRoles);

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
                $permission = Permission::where('device_wifi_mac', $normalizedMac)
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

        // Redirect berdasarkan role
        $dashboardRoutes = [
            'isAdmin' => 'pages.dashboardAdmin',
            'isHead Warehouse' => 'pages.dashboarHeadWarehouse',
            'isHead Buyer' => 'pages.dashboardHeadBuyer',
            'isKasir' => 'pages.dashboardKasir',
            'isSupervisor' => 'pages.dashboardSupervisor',
            'isManager Store' => 'pages.dashboardManagerStore'
        ];

        foreach ($dashboardRoutes as $gate => $route) {
            if (Gate::allows($gate, $user)) {
                Log::info("User {$normalizedUsername} logged in with role: {$user->user_type}");
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
// protected function logoutOtherDevices($user)
// {
//     // Hapus semua session lain milik user ini
//     UserSession::where('user_id', $user->id)
//         ->where('session_id', '!=', session()->getId())
//         ->delete();
// }
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
    // public function destroy(Request $request)
    // {

    //     // Set remember_token menjadi null di database
    //     if (Auth::user()) {
    //         Auth::user()->setRememberToken(null);
    //         UserSession::where('user_id', $user->id)->delete();
    //         Auth::user()->save();
    //     }

    //     // Logout user (ini juga menghapus cookie remember_me)
    //     Auth::logout();

    //     // Invalidate session
    //     $request->session()->invalidate();

    //     // Regenerate CSRF token
    //     $request->session()->regenerateToken();

    //     return redirect('/')->with('success', 'Anda berhasil logout');
    // }
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
 // public function store(Request $request)
    // {
    //     // Validasi tetap sama
    //     $attributes = $request->validate([
    //         'username' => [
    //             'required',
    //             'string',
    //             'min:7', // Diubah untuk konsisten dengan pesan error
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
    //             'min:4', // Diubah untuk konsisten dengan pesan error
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

    //         // Logika pengecekan MAC
    //         $shouldCheckMac = !in_array($user->user_type, $privilegedRoles) ||
    //             in_array($user->user_type, $mustCheckMacRoles);

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

    //                 if ($permission)
    //                     break;
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

    //         // Redirect berdasarkan role
    //         $dashboardRoutes = [
    //             'isAdmin' => 'pages.dashboardAdmin',
    //             'isHead Warehouse' => 'pages.dashboarHeadWarehouse',
    //             'isHead Buyer' => 'pages.dashboardHeadBuyer',
    //             'isKasir' => 'pages.dashboardKasir',
    //             'isSupervisor' => 'pages.dashboardSupervisor',
    //             'isManager Store' => 'pages.dashboardManagerStore' // Pastikan ada route ini
    //         ];

    //         foreach ($dashboardRoutes as $gate => $route) {
    //             if (Gate::allows($gate, $user)) {
    //                 Log::info("User {$normalizedUsername} logged in with role: {$user->user_type}");
    //                 return redirect()->route($route)->with('success', 'Anda berhasil login, semangat bekerja!!!');
    //             }
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
    // /**
    //  * Mendapatkan semua MAC addresses dari perangkat
    //  * 
    //  * @return array
    //  */
    // private function getAllMacAddresses()
    // {
    //     $macAddresses = [];

    //     // Deteksi berdasarkan sistem operasi
    //     if (PHP_OS_FAMILY === 'Windows') {
    //         // Windows - gunakan ipconfig
    //         $output = [];
    //         exec('ipconfig /all', $output);

    //         foreach ($output as $line) {
    //             // Format dengan dash: xx-xx-xx-xx-xx-xx
    //             if (preg_match('/Physical Address.*: ([0-9A-F]{2}-[0-9A-F]{2}-[0-9A-F]{2}-[0-9A-F]{2}-[0-9A-F]{2}-[0-9A-F]{2})/i', $line, $matches)) {
    //                 // Konversi format MAC dari xx-xx-xx-xx-xx-xx menjadi xx:xx:xx:xx:xx:xx untuk konsistensi
    //                 $mac = str_replace('-', ':', $matches[1]);
    //                 $macAddresses[] = ($mac);
    //             }

    //             // Format dengan colon: xx:xx:xx:xx:xx:xx
    //             if (preg_match('/Physical Address.*: ([0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2})/i', $line, $matches)) {
    //                 $macAddresses[] = ($matches[1]);
    //             }
    //         }
    //     } else {
    //         // Linux/Unix/macOS - gunakan ifconfig atau ip
    //         if ($this->command_exists('ifconfig')) {
    //             $output = [];
    //             exec('ifconfig', $output);
    //             $outputStr = implode(' ', $output);

    //             if (preg_match_all('/([0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2})/i', $outputStr, $matches)) {
    //                 foreach ($matches[1] as $mac) {
    //                     $macAddresses[] = ($mac);
    //                 }
    //             }
    //         } elseif ($this->command_exists('ip')) {
    //             $output = [];
    //             exec('ip link', $output);
    //             $outputStr = implode(' ', $output);

    //             if (preg_match_all('/([0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2})/i', $outputStr, $matches)) {
    //                 foreach ($matches[1] as $mac) {
    //                     $macAddresses[] = ($mac);
    //                 }
    //             }
    //         }
    //     }

    //     // Hapus duplikat dan kembalikan hasilnya
    //     return array_unique($macAddresses);
    // }

    // /**
    //  * Memeriksa apakah perintah tertentu tersedia
    //  * 
    //  * @param string $cmd
    //  * @return bool
    //  */
    // private function command_exists($cmd)
    // {
    //     $return = shell_exec(sprintf("which %s 2>/dev/null", escapeshellarg($cmd)));
    //     return !empty($return);
    // }





















































// public function store(Request $request)
// {
//     // Bagian validasi - tidak ada perubahan
//     $attributes = $request->validate([
//         'username' => [
//             'required',
//             'string',
//             'min:4',
//             'max:12',
//             'regex:/^[a-zA-Z0-9_-]+$/',
//             new NoXSSInput(),
//             function ($attribute, $value, $fail) {
//                 $sanitizedValue = strip_tags($value);
//                 if ($sanitizedValue !== $value) {
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

//     // Rate limiter - tidak ada perubahan
//     $rateLimiterKey = "/:{$request->ip()}:{$request->username}";
//     if (RateLimiter::tooManyAttempts($rateLimiterKey, 10)) {
//         Log::warning("Rate limiter triggered for username: {$request->username}");
//         return back()->withErrors(['/' => 'Terlalu banyak percobaan login. Silakan coba lagi nanti.']);
//     }

//     try {
//         // Dapatkan semua MAC addresses
//         $macAddresses = $this->getAllMacAddresses();

//         if (empty($macAddresses)) {
//             Log::warning("Gagal mendapatkan MAC Address untuk login dari IP: {$request->ip()}");
//             return back()->withErrors(['/' => 'Gagal mendapatkan identitas perangkat. Silakan hubungi administrator.'])->withInput();
//         }

//         Log::info("Daftar MAC addresses yang terdeteksi: " . implode(', ', $macAddresses));

//         $devicePermission = null;
//         $foundMac = null;
//         $macType = null;

//         foreach ($macAddresses as $mac) {
//             // Normalisasi MAC address ke format yang sama dengan database (gunakan tanda hubung)
//             $normalizedMac = str_replace(':', '-', strtoupper($mac));

//             // Cari permission berdasarkan MAC address yang dinormalisasi
//             $tempPermission = Permission::where('device_wifi_mac', $normalizedMac)
//                 ->orWhere('device_lan_mac', $normalizedMac)
//                 ->first();

//             if ($tempPermission) {
//                 $devicePermission = $tempPermission;
//                 $foundMac = $normalizedMac;

//                 // Tentukan jenis MAC address yang digunakan
//                 if ($devicePermission->device_wifi_mac === $normalizedMac) {
//                     $macType = "device_wifi_mac";
//                 } elseif ($devicePermission->device_lan_mac === $normalizedMac) {
//                     $macType = "device_lan_mac";
//                 }

//                 Log::info("MAC Address yang cocok dengan database: {$normalizedMac} (Tipe: {$macType})");
//                 break;
//             }
//         }

//         // Jika tidak ditemukan permission
//         if (!$devicePermission) {
//             Log::warning("Perangkat tidak terdaftar mencoba login dari IP: {$request->ip()}");
//             return back()->withErrors(['/' => 'Perangkat ini tidak diizinkan untuk login.'])->withInput();
//         }

//         // Pastikan hanya menggunakan MAC yang valid
//         if ($macType === "device_wifi_mac" && $devicePermission->device_wifi_mac !== $foundMac) {
//             Log::warning("MAC Address tidak valid: {$foundMac}");
//             return back()->withErrors(['/' => 'Perangkat ini tidak diizinkan untuk login.'])->withInput();
//         }

//         if ($macType === "device_lan_mac" && $devicePermission->device_lan_mac !== $foundMac) {
//             Log::warning("MAC Address tidak valid: {$foundMac}");
//             return back()->withErrors(['/' => 'Perangkat ini tidak diizinkan untuk login.'])->withInput();
//         }
//         // Proses login
//         if (Auth::attempt($attributes, $request->boolean('remember'))) {
//             $request->session()->regenerate();
//             Log::info("Successful login for username: {$request->username} with {$macType} MAC: {$foundMac}");

//             RateLimiter::clear($rateLimiterKey);
//             $user = Auth::user();
//             if ($request->boolean('remember')) {
//                 $user->remember_token = $user->getRememberToken();
//                 $user->save();
//             }

//             $dashboards = [
//                 'isAdmin' => 'pages.dashboardAdmin',
//                 'isHead Warehouse' => 'pages.dashboarHeadWarehouse',
//                 'isHead Buyer' => 'pages.dashboardHeadBuyer',
//                 'isKasir' => 'pages.dashboardKasir',
//                 'isSupervisor' => 'pages.dashboardSupervisor',
//             ];

//             foreach ($dashboards as $gate => $dashboard) {
//                 if (Gate::allows($gate, $user)) {
//                     Log::info("User {$user->username} logged in with role: $gate");
//                     return redirect()->route($dashboard)->with('success', 'Anda berhasil login, semangat bekerja!!!');
//                 }
//             }

//             Log::warning("User {$user->username} logged in but has no valid role");
//             return redirect('/')->with('warning', 'Akun Anda tidak memiliki peran yang valid.');
//         }

//         // Login gagal
//         Log::warning("Failed login attempt for username: {$request->username}");
//         RateLimiter::hit($rateLimiterKey, 60);
//         return back()->withErrors(['/' => 'Username atau Password salah.']);
//     } catch (\Exception $e) {
//         Log::error("Login error: " . $e->getMessage());
//         return back()->withErrors(['/' => 'Terjadi kesalahan. Silakan coba lagi.']);
//     }
// }

// /**
//  * Mendapatkan semua MAC addresses dari perangkat
//  * 
//  * @return array
//  */
// private function getAllMacAddresses()
// {
//     $macAddresses = [];

//     // Deteksi berdasarkan sistem operasi
//     if (PHP_OS_FAMILY === 'Windows') {
//         // Windows - gunakan ipconfig
//         $output = [];
//         exec('ipconfig /all', $output);

//         foreach ($output as $line) {
//             // Format dengan dash: xx-xx-xx-xx-xx-xx
//             if (preg_match('/Physical Address.*: ([0-9A-F]{2}-[0-9A-F]{2}-[0-9A-F]{2}-[0-9A-F]{2}-[0-9A-F]{2}-[0-9A-F]{2})/i', $line, $matches)) {
//                 // Konversi format MAC dari xx-xx-xx-xx-xx-xx menjadi xx:xx:xx:xx:xx:xx untuk konsistensi
//                 $mac = str_replace('-', ':', $matches[1]);
//                 $macAddresses[] = ($mac);
//             }

//             // Format dengan colon: xx:xx:xx:xx:xx:xx
//             if (preg_match('/Physical Address.*: ([0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2})/i', $line, $matches)) {
//                 $macAddresses[] = ($matches[1]);
//             }
//         }
//     } else {
//         // Linux/Unix/macOS - gunakan ifconfig atau ip
//         if ($this->command_exists('ifconfig')) {
//             $output = [];
//             exec('ifconfig', $output);
//             $outputStr = implode(' ', $output);

//             if (preg_match_all('/([0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2})/i', $outputStr, $matches)) {
//                 foreach ($matches[1] as $mac) {
//                     $macAddresses[] = ($mac);
//                 }
//             }
//         } elseif ($this->command_exists('ip')) {
//             $output = [];
//             exec('ip link', $output);
//             $outputStr = implode(' ', $output);

//             if (preg_match_all('/([0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2})/i', $outputStr, $matches)) {
//                 foreach ($matches[1] as $mac) {
//                     $macAddresses[] = ($mac);
//                 }
//             }
//         }
//     }

//     // Hapus duplikat dan kembalikan hasilnya
//     return array_unique($macAddresses);
// }

// /**
//  * Memeriksa apakah perintah tertentu tersedia
//  * 
//  * @param string $cmd
//  * @return bool
//  */
// private function command_exists($cmd) {
//     $return = shell_exec(sprintf("which %s 2>/dev/null", escapeshellarg($cmd)));
//     return !empty($return);
// }

// public function store(Request $request)
// {
//     // Bagian validasi - tidak ada perubahan
//     $attributes = $request->validate([
//         'username' => [
//             'required',
//             'string',
//             'min:4',
//             'max:12',
//             'regex:/^[a-zA-Z0-9_-]+$/',
//             new NoXSSInput(),
//             function ($attribute, $value, $fail) {
//                 $sanitizedValue = strip_tags($value);
//                 if ($sanitizedValue !== $value) {
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

//     // Rate limiter - tidak ada perubahan
//     $rateLimiterKey = "/:{$request->ip()}:{$request->username}";
//     if (RateLimiter::tooManyAttempts($rateLimiterKey, 5)) {
//         Log::warning("Rate limiter triggered for username: {$request->username}");
//         return back()->withErrors(['/' => 'Terlalu banyak percobaan login. Silakan coba lagi nanti.']);
//     }

//     try {
//         // Proses login terlebih dahulu
//         $attributes['username'] = strtolower($attributes['username']);
//         if (Auth::attempt($attributes, $request->boolean('remember'))) {
//             $request->session()->regenerate();
//             RateLimiter::clear($rateLimiterKey);

//             $user = Auth::user();

//             // Daftar role yang boleh bypass MAC address check
//             $bypassRoles = [
//                 'Admin', 
//                 'Head Warehouse', 
//                 'Head Buyer', 
//                 'Head Finance', 
//                 'GM', 
//                 'Finance', 
//                 'Buyer', 
//                 'Warehouse'
//             ];

//             // Cek apakah user memiliki role yang boleh bypass
//             $shouldBypassMacCheck = in_array($user->user_type, $bypassRoles);

//             if (!$shouldBypassMacCheck) {
//                 // Lanjutkan pengecekan MAC address untuk role lainnya
//                 $macAddresses = $this->getAllMacAddresses();

//                 if (empty($macAddresses)) {
//                     Log::warning("Gagal mendapatkan MAC Address untuk login dari IP: {$request->ip()}");
//                     Auth::logout();
//                     return back()->withErrors(['/' => 'Gagal mendapatkan identitas perangkat. Silakan hubungi administrator.'])->withInput();
//                 }

//                 Log::info("Daftar MAC addresses yang terdeteksi: " . implode(', ', $macAddresses));

//                 $devicePermission = null;
//                 $foundMac = null;
//                 $macType = null;

//                 foreach ($macAddresses as $mac) {
//                     $normalizedMac = str_replace(':', '-', strtoupper($mac));

//                     $tempPermission = Permission::where('device_wifi_mac', $normalizedMac)
//                         ->orWhere('device_lan_mac', $normalizedMac)
//                         ->first();

//                     if ($tempPermission) {
//                         $devicePermission = $tempPermission;
//                         $foundMac = $normalizedMac;

//                         if ($devicePermission->device_wifi_mac === $normalizedMac) {
//                             $macType = "device_wifi_mac";
//                         } elseif ($devicePermission->device_lan_mac === $normalizedMac) {
//                             $macType = "device_lan_mac";
//                         }

//                         Log::info("MAC Address yang cocok dengan database: {$normalizedMac} (Tipe: {$macType})");
//                         break;
//                     }
//                 }

//                 if (!$devicePermission) {
//                     Log::warning("Perangkat tidak terdaftar mencoba login dari IP: {$request->ip()}");
//                     Auth::logout();
//                     return back()->withErrors(['/' => 'Perangkat ini tidak diizinkan untuk login.'])->withInput();
//                 }

//                 if ($macType === "device_wifi_mac" && $devicePermission->device_wifi_mac !== $foundMac) {
//                     Log::warning("MAC Address tidak valid: {$foundMac}");
//                     Auth::logout();
//                     return back()->withErrors(['/' => 'Perangkat ini tidak diizinkan untuk login.'])->withInput();
//                 }

//                 if ($macType === "device_lan_mac" && $devicePermission->device_lan_mac !== $foundMac) {
//                     Log::warning("MAC Address tidak valid: {$foundMac}");
//                     Auth::logout();
//                     return back()->withErrors(['/' => 'Perangkat ini tidak diizinkan untuk login.'])->withInput();
//                 }

//                 Log::info("Successful login for username: {$request->username} with {$macType} MAC: {$foundMac}");
//             } else {
//                 Log::info("Successful login for privileged user: {$request->username} (Role: {$user->user_type}) - MAC check bypassed");
//             }

//             // Set remember token jika diperlukan
//             if ($request->boolean('remember')) {
//                 $user->remember_token = $user->getRememberToken();
//                 $user->save();
//             }

//             $dashboards = [
//                 'isAdmin' => 'pages.dashboardAdmin',
//                 'isHead Warehouse' => 'pages.dashboarHeadWarehouse',
//                 'isHead Buyer' => 'pages.dashboardHeadBuyer',
//                 'isKasir' => 'pages.dashboardKasir',
//                 'isSupervisor' => 'pages.dashboardSupervisor',
//             ];

//             foreach ($dashboards as $gate => $dashboard) {
//                 if (Gate::allows($gate, $user)) {
//                     Log::info("User {$user->username} logged in with role: $gate");
//                     return redirect()->route($dashboard)->with('success', 'Anda berhasil login, semangat bekerja!!!');
//                 }
//             }

//             Log::warning("User {$user->username} logged in but has no valid role");
//             Auth::logout();
//             return redirect('/')->with('warning', 'Akun Anda tidak memiliki peran yang valid.');
//         }

//         // Login gagal
//         Log::warning("Failed login attempt for username: {$request->username}");
//         RateLimiter::hit($rateLimiterKey, 60);
//         return back()->withErrors(['/' => 'Username atau Password salah.']);
//     } catch (\Exception $e) {
//         Log::error("Login error: " . $e->getMessage());
//         return back()->withErrors(['/' => 'Terjadi kesalahan. Silakan coba lagi.']);
//     }
// }
// public function store(Request $request)
// {
//     // Validasi yang konsisten antara aturan dan pesan error
//     $attributes = $request->validate([
//         'username' => [
//             'required',
//             'string',
//             'min:7', // Diubah untuk konsisten dengan pesan error
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
//             'min:4', // Diubah untuk konsisten dengan pesan error
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

//     // Normalisasi username sebelum rate limiting
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

//         // Bypass MAC check untuk role tertentu
//         $bypassRoles = ['Admin', 'Head Warehouse', 'Head Buyer', 'Head Finance', 'GM', 'Finance', 'Buyer', 'Warehouse'];

//         if (!in_array($user->user_type, $bypassRoles)) {
//             $macAddresses = $this->getAllMacAddresses();

//             if (empty($macAddresses)) {
//                 Log::warning("No MAC addresses detected for IP: {$request->ip()}");
//                 Auth::logout();
//                 return back()->withErrors(['/' => 'Gagal mendapatkan identitas perangkat. Silakan hubungi administrator.'])->withInput();
//             }

//             Log::info("Detected MAC addresses: " . implode(', ', $macAddresses));

//             foreach ($macAddresses as $mac) {
//                 $normalizedMac = str_replace(':', '-', strtoupper($mac));
//                 $permission = Permission::where('device_wifi_mac', $normalizedMac)
//                     ->orWhere('device_lan_mac', $normalizedMac)
//                     ->first();

//                 if ($permission) {
//                     Log::info("Valid MAC address found: {$normalizedMac}");
//                     break;
//                 }
//             }

//             if (!$permission) {
//                 Log::warning("Unauthorized device attempt from IP: {$request->ip()}");
//                 Auth::logout();
//                 return back()->withErrors(['/' => 'Perangkat ini tidak diizinkan untuk login.'])->withInput();
//             }
//         } else {
//             Log::info("Bypassed MAC check for privileged user: {$normalizedUsername}");
//         }

//         // Set remember token jika diperlukan
//         if ($request->boolean('remember')) {
//             $user->setRememberToken(Str::random(60));
//             $user->save();
//         }

//         // Redirect berdasarkan role
//         $dashboardRoutes = [
//             'isAdmin' => 'pages.dashboardAdmin',
//             'isHead Warehouse' => 'pages.dashboarHeadWarehouse',
//             'isHead Buyer' => 'pages.dashboardHeadBuyer',
//             'isKasir' => 'pages.dashboardKasir',
//             'isSupervisor' => 'pages.dashboardSupervisor',
//         ];

//         foreach ($dashboardRoutes as $gate => $route) {
//             if (Gate::allows($gate, $user)) {
//                 Log::info("User {$normalizedUsername} logged in with role: {$user->user_type}");
//                 return redirect()->route($route)->with('success', 'Anda berhasil login, semangat bekerja!!!');
//             }
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