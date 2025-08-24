<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\Terms;
use App\Models\UserSession;
use Illuminate\Support\Str;
class LoginController extends Controller
{
    public function index()
    {
        return view('pages.login');
    }
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
                'min:7', // disamakan dengan pesan error
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

        // Rate limiting
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

                // Jika memilih "Ya", hapus sesi lama (kecuali session sekarang)
                $this->logoutOtherDevices($user, $currentSessionId);
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

            // Redirect berdasarkan role menggunakan Spatie
            $dashboardRoutes = [
                'Admin' => 'pages.dashboardAdmin',
                'HeadHR' => 'pages.dashboardHR',
                'HR' => 'pages.dashboardHR',
                'head-warehouse' => 'pages.dashboardHeadWarehouse',
                'head-buyer' => 'pages.dashboardHeadBuyer',
                'cashier-store' => 'pages.dashboardKasir',
                'supervisor-store' => 'pages.dashboardSupervisor',
                'ManagerStore' => 'pages.dashboardManager'
            ];

            foreach ($dashboardRoutes as $role => $route) {
                if ($user->hasRole($role)) {
                    Log::info("User {$normalizedUsername} logged in with role: {$role}");
                    \Log::debug('User permissions: ' . auth()->user()->getPermissionsViaRoles()->pluck('name'));
                    return redirect()->route($route)->with('success', 'Success login, Goodluck!!!');
                }
            }

            // Fallback untuk role tidak dikenal
            Log::warning("User {$normalizedUsername} has no valid role assigned");
            Auth::logout();
            return redirect('/')->with('warning', 'Akun Anda tidak memiliki peran yang valid.');

        } catch (\Exception $e) {
            Log::error("Login error", ['exception' => $e]);
            return back()->withErrors(['/' => 'Terjadi kesalahan. Silakan coba lagi.']);
        }
    }

    protected function logoutOtherDevices($user, $currentSessionId)
    {
        UserSession::where('user_id', $user->id)
            ->where('session_id', '!=', $currentSessionId)
            ->delete();
    }
    public function destroy(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $user->setRememberToken(null);

            UserSession::where('user_id', $user->id)->delete();

            $user->save();
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('success', 'Anda berhasil logout');
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
    //         // Daftar role yang boleh bypass MAC check menggunakan Spatie
    //         $privilegedRoles = [
    //             'Admin',
    //             'head-warehouse',
    //             'HeadHR',
    //             'HR',
    //             'head-buyer',
    //             'head-finance',
    //             'gm',
    //             'finance',
    //             'buyer',
    //             'warehouse'
    //         ];

    //         // Role yang harus selalu cek MAC (termasuk Manager Store)
    //         $mustCheckMacRoles = [
    //             'ManagerStore',
    //             'supervisor-store',
    //             'cashier-store'
    //         ];

    //         // Logika pengecekan MAC
    //         $shouldCheckMac = !$user->hasAnyRole($privilegedRoles) ||
    //             $user->hasAnyRole($mustCheckMacRoles);

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
    //                 $permission = Terms::where('device_wifi_mac', $normalizedMac)
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

    //         // Redirect berdasarkan role menggunakan Spatie
    //         $dashboardRoutes = [
    //             'Admin' => 'pages.dashboardAdmin',
    //             'HeadHR' => 'pages.dashboardHR',
    //             'HR' => 'pages.dashboardHR',
    //             'head-warehouse' => 'pages.dashboarHeadWarehouse',
    //             'head-buyer' => 'pages.dashboardHeadBuyer',
    //             'cashier-store' => 'pages.dashboardKasir',
    //             'supervisor-store' => 'pages.dashboardSupervisor',
    //             'ManagerStore' => 'pages.dashboardManager'
    //         ];

    //         foreach ($dashboardRoutes as $role => $route) {
    //             if ($user->hasRole($role)) {
    //                 Log::info("User {$normalizedUsername} logged in with role: {$role}");
    //                 \Log::debug('User permissions: ' . auth()->user()->getPermissionsViaRoles()->pluck('name'));
    //                 return redirect()->route($route)->with('success', 'Success login, Goodluck!!!');
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
    //     // Hapus semua session lain milik user ini, termasuk session saat ini
    //     UserSession::where('user_id', $user->id)->delete();
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

    // public function destroy(Request $request)
    // {
    //     // Get current user
    //     $user = Auth::user();

    //     if ($user) {
    //         // Set remember_token menjadi null di database
    //         $user->setRememberToken(null);

    //         // Hapus session records
    //         UserSession::where('user_id', $user->id)->delete();

    //         // Simpan perubahan
    //         $user->save();
    //     }

    //     // Logout user (ini juga menghapus cookie remember_me)
    //     Auth::logout();

    //     // Invalidate session
    //     $request->session()->invalidate();

    //     // Regenerate CSRF token
    //     $request->session()->regenerateToken();

    //     return redirect('/')->with('success', 'Anda berhasil logout');
    // }
}
