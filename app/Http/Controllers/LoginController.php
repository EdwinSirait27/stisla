<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\Permission;
use App\Models\Activity;


class LoginController extends Controller
{
    public function index()
    {   
        return view('pages.login');
    }

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

public function store(Request $request)
{
    // Bagian validasi - tidak ada perubahan
    $attributes = $request->validate([
        'username' => [
            'required',
            'string',
            'min:4',
            'max:12',
            'regex:/^[a-zA-Z0-9_-]+$/',
            new NoXSSInput(),
            function ($attribute, $value, $fail) {
                $sanitizedValue = strip_tags($value);
                if ($sanitizedValue !== $value) {
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

    // Rate limiter - tidak ada perubahan
    $rateLimiterKey = "/:{$request->ip()}:{$request->username}";
    if (RateLimiter::tooManyAttempts($rateLimiterKey, 10)) {
        Log::warning("Rate limiter triggered for username: {$request->username}");
        return back()->withErrors(['/' => 'Terlalu banyak percobaan login. Silakan coba lagi nanti.']);
    }

    try {
        // Proses login terlebih dahulu
        if (Auth::attempt($attributes, $request->boolean('remember'))) {
            $request->session()->regenerate();
            RateLimiter::clear($rateLimiterKey);
            
            $user = Auth::user();
            
            // Daftar role yang boleh bypass MAC address check
            $bypassRoles = [
                'Admin', 
                'Head Warehouse', 
                'Head Buyer', 
                'Head Finance', 
                'GM', 
                'Finance', 
                'Buyer', 
                'Warehouse'
            ];
            
            // Cek apakah user memiliki role yang boleh bypass
            $shouldBypassMacCheck = in_array($user->user_type, $bypassRoles);
            
            if (!$shouldBypassMacCheck) {
                // Lanjutkan pengecekan MAC address untuk role lainnya
                $macAddresses = $this->getAllMacAddresses();
                
                if (empty($macAddresses)) {
                    Log::warning("Gagal mendapatkan MAC Address untuk login dari IP: {$request->ip()}");
                    Auth::logout();
                    return back()->withErrors(['/' => 'Gagal mendapatkan identitas perangkat. Silakan hubungi administrator.'])->withInput();
                }
                
                Log::info("Daftar MAC addresses yang terdeteksi: " . implode(', ', $macAddresses));
                
                $devicePermission = null;
                $foundMac = null;
                $macType = null;
                
                foreach ($macAddresses as $mac) {
                    $normalizedMac = str_replace(':', '-', strtoupper($mac));
                    
                    $tempPermission = Permission::where('device_wifi_mac', $normalizedMac)
                        ->orWhere('device_lan_mac', $normalizedMac)
                        ->first();
                        
                    if ($tempPermission) {
                        $devicePermission = $tempPermission;
                        $foundMac = $normalizedMac;
                        
                        if ($devicePermission->device_wifi_mac === $normalizedMac) {
                            $macType = "device_wifi_mac";
                        } elseif ($devicePermission->device_lan_mac === $normalizedMac) {
                            $macType = "device_lan_mac";
                        }
                        
                        Log::info("MAC Address yang cocok dengan database: {$normalizedMac} (Tipe: {$macType})");
                        break;
                    }
                }
                    
                if (!$devicePermission) {
                    Log::warning("Perangkat tidak terdaftar mencoba login dari IP: {$request->ip()}");
                    Auth::logout();
                    return back()->withErrors(['/' => 'Perangkat ini tidak diizinkan untuk login.'])->withInput();
                }

                if ($macType === "device_wifi_mac" && $devicePermission->device_wifi_mac !== $foundMac) {
                    Log::warning("MAC Address tidak valid: {$foundMac}");
                    Auth::logout();
                    return back()->withErrors(['/' => 'Perangkat ini tidak diizinkan untuk login.'])->withInput();
                }
                
                if ($macType === "device_lan_mac" && $devicePermission->device_lan_mac !== $foundMac) {
                    Log::warning("MAC Address tidak valid: {$foundMac}");
                    Auth::logout();
                    return back()->withErrors(['/' => 'Perangkat ini tidak diizinkan untuk login.'])->withInput();
                }
                
                Log::info("Successful login for username: {$request->username} with {$macType} MAC: {$foundMac}");
            } else {
                Log::info("Successful login for privileged user: {$request->username} (Role: {$user->user_type}) - MAC check bypassed");
            }
            
            // Set remember token jika diperlukan
            if ($request->boolean('remember')) {
                $user->remember_token = $user->getRememberToken();
                $user->save();
            }

            $dashboards = [
                'isAdmin' => 'pages.dashboardAdmin',
                'isHead Warehouse' => 'pages.dashboarHeadWarehouse',
                'isHead Buyer' => 'pages.dashboardHeadBuyer',
                'isKasir' => 'pages.dashboardKasir',
                'isSupervisor' => 'pages.dashboardSupervisor',
            ];

            foreach ($dashboards as $gate => $dashboard) {
                if (Gate::allows($gate, $user)) {
                    Log::info("User {$user->username} logged in with role: $gate");
                    return redirect()->route($dashboard)->with('success', 'Anda berhasil login, semangat bekerja!!!');
                }
            }

            Log::warning("User {$user->username} logged in but has no valid role");
            Auth::logout();
            return redirect('/')->with('warning', 'Akun Anda tidak memiliki peran yang valid.');
        }
        
        // Login gagal
        Log::warning("Failed login attempt for username: {$request->username}");
        RateLimiter::hit($rateLimiterKey, 60);
        return back()->withErrors(['/' => 'Username atau Password salah.']);
    } catch (\Exception $e) {
        Log::error("Login error: " . $e->getMessage());
        return back()->withErrors(['/' => 'Terjadi kesalahan. Silakan coba lagi.']);
    }
}
public function destroy(Request $request)
{
  
    // Set remember_token menjadi null di database
    if (Auth::user()) {
        Auth::user()->setRememberToken(null);
        Auth::user()->save();
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