<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\Permission;


class LoginController extends Controller
{
    public function index()
    {   
        return view('pages.login');
    }
//     public function store(Request $request)
//     {
//         $attributes = $request->validate([
//             'username' => [
//                 'required',
//                 'string',
//                 'min:7',
//                 'max:12',
//                 'regex:/^[a-zA-Z0-9_-]+$/',
//                 new NoXSSInput(),
//                 function ($attribute, $value, $fail) {
//                     $sanitizedValue = strip_tags($value);
//                     if ($sanitizedValue !== $value) {
//                         $fail("Input $attribute mengandung tag HTML yang tidak diperbolehkan.");
//                     }
//                 }
//             ],
//             'password' => [
//                 'required',
//                 'string',
//                 'min:7',
//                 'max:12',
//             ],
//         ], [
//           'username.required' => 'Username is required.',
// 'username.min' => 'Username must be at least 7 characters.',
// 'username.max' => 'Username cannot be more than 12 characters.',
// 'password.required' => 'Password is required.',
// 'password.min' => 'Password must be at least 7 characters.',
// 'password.max' => 'Password cannot be more than 12 characters.',

//         ]);
    
//         $rateLimiterKey = "login:{$request->ip()}:{$request->username}";
    
//         if (RateLimiter::tooManyAttempts($rateLimiterKey, 10)) {
//             Log::warning("Rate limiter triggered for username: {$request->username}");
//             return back()->withErrors(['login' => 'Terlalu banyak percobaan login. Silakan coba lagi nanti.']);
//         }
        
//         try {
//             if (Auth::attempt($attributes, $request->boolean('remember'))) {
//                 $request->session()->regenerate();
//                 Log::info("Successful login for username: {$request->username}");
        
//                 // Clear rate limiter on success
//                 RateLimiter::clear($rateLimiterKey);
        
//                 $user = Auth::user();
                
//                 // Update remember_me column if "remember me" was checked
//                 if ($request->boolean('remember')) {
//                     $user->remember_me = $user->getRememberToken();
//                     $user->save();
//                 }
            
//                 $dashboards = [
//                     'isManager' => 'pages.dashboardManager',
//                     'isKasir' => 'pages.dashboardKasir',
//                     'isAdmin' => 'pages.dashboardAdmin',
//                     'isSupervisor' => 'pages.dashboardSupervisor',
//                 ];
        
//                 foreach ($dashboards as $gate => $dashboard) {
//                     if (Gate::allows($gate, $user)) {
//                         Log::info("User {$user->username} logged in with role: $gate");
//                         return redirect()->route($dashboard)->with('success', 'Anda berhasil login');
//                     }
//                 }
        
//                 // Jika tidak memiliki role yang sesuai
//                 Log::warning("User {$user->username} logged in but has no valid role");
                
//                 // Tidak logout, tetapi redirect ke halaman default
//                 return redirect('/')->with('warning', 'Akun Anda tidak memiliki peran yang valid.');
//             }
        
//             Log::warning("Failed login attempt for username: {$request->username}");
//             RateLimiter::hit($rateLimiterKey, 60); // Expire in 60 seconds
//             return back()->withErrors(['login' => 'Username atau Password salah.']);
//         } catch (\Exception $e) {
//             Log::error("Login error: " . $e->getMessage());
//             return back()->withErrors(['login' => 'Terjadi kesalahan. Silakan coba lagi.']);
//         }
//     }
// dd($request->all());


// public function store(Request $request)
// {
//     // Validasi input (tetap seperti kode asli Anda)
//     $attributes = $request->validate([
//                'username' => [
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
//        ], [
//         'username.required' => 'Username is required.',
//         'username.min' => 'Username must be at least 7 characters.',
//         'username.max' => 'Username cannot be more than 12 characters.',
//         'password.required' => 'Password is required.',
//         'password.min' => 'Password must be at least 7 characters.',
//         'password.max' => 'Password cannot be more than 12 characters.',
//     ]);

//     // Rate limiter (tetap seperti kode asli Anda)
//     $rateLimiterKey = "/:{$request->ip()}:{$request->username}";
//     if (RateLimiter::tooManyAttempts($rateLimiterKey, 10)) {
//         Log::warning("Rate limiter triggered for username: {$request->username}");
//         return back()->withErrors(['/' => 'Terlalu banyak percobaan login. Silakan coba lagi nanti.']);
//     }

//     try {
//         // TAHAP 1: Mendapatkan MAC Address perangkat
//         $macAddress = $this->getMacAddress();
        
//         // TAHAP 2: Validasi apakah MAC berhasil didapatkan
//         if (!$macAddress) {
//             Log::warning("Gagal mendapatkan MAC Address untuk login dari IP: {$request->ip()}");
//             return back()->withErrors(['/' => 'Gagal mendapatkan identitas perangkat. Silakan hubungi administrator.'])->withInput();
//         }

//         // TAHAP 3: Memeriksa apakah MAC Address terdaftar
//         // $deviceRegistered = Permission::where('device_wifi_mac','device_lan_wifi', $macAddress)->exists();
//         $deviceRegistered = Permission::where('device_wifi_mac', $macAddress)
//     ->orWhere('device_lan_mac', $macAddress)
//     ->exists();


//         // TAHAP 4: Menolak akses jika tidak terdaftar
//         if (!$deviceRegistered) {
//             Log::warning("Perangkat tidak terdaftar mencoba login: {$macAddress} dari IP: {$request->ip()}");
//             return back()->withErrors(['/' => 'Perangkat ini tidak diizinkan untuk login.'])->withInput();
//         }

//         // Lanjutkan proses login jika device sudah terdaftar
//                if (Auth::attempt($attributes, $request->boolean('remember'))) {
//             $request->session()->regenerate();
//             Log::info("Successful login for username: {$request->username}");

//             // **Bersihkan rate limiter jika login berhasil**
//             RateLimiter::clear($rateLimiterKey);

//             $user = Auth::user();

//             if ($request->boolean('remember')) {
//                 $user->remember_me = $user->getRememberToken();
//                 $user->save();
//             }

//             $dashboards = [
//                 'isManager' => 'pages.dashboardManager',
//                 'isKasir' => 'pages.dashboardKasir',
//                 'isAdmin' => 'pages.dashboardAdmin',
//                 'isSupervisor' => 'pages.dashboardSupervisor',
//             ];

//             foreach ($dashboards as $gate => $dashboard) {
//                 if (Gate::allows($gate, $user)) {
//                     Log::info("User {$user->username} logged in with role: $gate");
//                     return redirect()->route($dashboard)->with('success', 'Anda berhasil login');
//                 }
//             }

//             Log::warning("User {$user->username} logged in but has no valid role");
//             return redirect('/')->with('warning', 'Akun Anda tidak memiliki peran yang valid.');
//         }
        
//         // Login gagal (tetap seperti kode asli Anda)
//         Log::warning("Failed login attempt for username: {$request->username}");
//         RateLimiter::hit($rateLimiterKey, 60);
//         return back()->withErrors(['/' => 'Username atau Password salah.']);
//     } catch (\Exception $e) {
//         Log::error("Login error: " . $e->getMessage());
//         return back()->withErrors(['/' => 'Terjadi kesalahan. Silakan coba lagi.']);
//     }
// }

// Tambahkan method helper ini di dalam controller yang sama
// public function store(Request $request)
// {
//     // Validasi input (tetap seperti kode asli Anda)
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

//     // Rate limiter (tetap seperti kode asli Anda)
//     $rateLimiterKey = "/:{$request->ip()}:{$request->username}";
//     if (RateLimiter::tooManyAttempts($rateLimiterKey, 10)) {
//         Log::warning("Rate limiter triggered for username: {$request->username}");
//         return back()->withErrors(['/' => 'Terlalu banyak percobaan login. Silakan coba lagi nanti.']);
//     }

//     try {
//         // TAHAP 1: Mendapatkan MAC Address perangkat
//         $macAddress = $this->getMacAddress();
        
//         // TAHAP 2: Validasi apakah MAC berhasil didapatkan
//         if (!$macAddress) {
//             Log::warning("Gagal mendapatkan MAC Address untuk login dari IP: {$request->ip()}");
//             return back()->withErrors(['/' => 'Gagal mendapatkan identitas perangkat. Silakan hubungi administrator.'])->withInput();
//         }

//         // TAHAP 3: Memeriksa apakah MAC Address terdaftar di salah satu kolom device_wifi_mac atau device_lan_mac
//         $devicePermission = Permission::where('device_wifi_mac', $macAddress)
//             ->orWhere('device_lan_mac', $macAddress)
//             ->first();
            
//         // TAHAP 4: Menolak akses jika tidak terdaftar
//         if (!$devicePermission) {
//             Log::warning("Perangkat tidak terdaftar mencoba login: {$macAddress} dari IP: {$request->ip()}");
//             return back()->withErrors(['/' => 'Perangkat ini tidak diizinkan untuk login.'])->withInput();
//         }
        
//         // TAHAP 5: Periksa validitas dari kedua kolom MAC dan gunakan yang valid
//         $validMac = $macAddress;
//         if ($devicePermission->device_wifi_mac === $macAddress) {
//             // MAC address cocok dengan device_wifi_mac, gunakan ini
//             $validMac = $devicePermission->device_wifi_mac;
//             Log::info("Menggunakan device_wifi_mac untuk autentikasi: {$validMac}");
//         } elseif ($devicePermission->device_lan_mac === $macAddress) {
//             // MAC address cocok dengan device_lan_mac, gunakan ini
//             $validMac = $devicePermission->device_lan_mac;
//             Log::info("Menggunakan device_lan_mac untuk autentikasi: {$validMac}");
//         }

//         // Lanjutkan proses login dengan MAC yang valid
//         if (Auth::attempt($attributes, $request->boolean('remember'))) {
//             $request->session()->regenerate();
//             Log::info("Successful login for username: {$request->username} with MAC: {$validMac}");

//             // **Bersihkan rate limiter jika login berhasil**
//             RateLimiter::clear($rateLimiterKey);

//             $user = Auth::user();

//             if ($request->boolean('remember')) {
//                 $user->remember_me = $user->getRememberToken();
//                 $user->save();
//             }

//             $dashboards = [
//                 'isManager' => 'pages.dashboardManager',
//                 'isKasir' => 'pages.dashboardKasir',
//                 'isAdmin' => 'pages.dashboardAdmin',
//                 'isSupervisor' => 'pages.dashboardSupervisor',
//             ];

//             foreach ($dashboards as $gate => $dashboard) {
//                 if (Gate::allows($gate, $user)) {
//                     Log::info("User {$user->username} logged in with role: $gate");
//                     return redirect()->route($dashboard)->with('success', 'Anda berhasil login');
//                 }
//             }

//             Log::warning("User {$user->username} logged in but has no valid role");
//             return redirect('/')->with('warning', 'Akun Anda tidak memiliki peran yang valid.');
//         }
        
//         // Login gagal (tetap seperti kode asli Anda)
//         Log::warning("Failed login attempt for username: {$request->username}");
//         RateLimiter::hit($rateLimiterKey, 60);
//         return back()->withErrors(['/' => 'Username atau Password salah.']);
//     } catch (\Exception $e) {
//         Log::error("Login error: " . $e->getMessage());
//         return back()->withErrors(['/' => 'Terjadi kesalahan. Silakan coba lagi.']);
//     }
// }
// private function getMacAddress()
// {
//     try {
//         $macAddress = null;
        
//         // Coba untuk Linux terlebih dahulu
//         if (PHP_OS_FAMILY === 'Linux') {
//             // Cara 1: Cek semua interface jaringan
//             $interfaces = glob('/sys/class/net/*/address');
//             foreach ($interfaces as $interface) {
//                 $mac = trim(file_get_contents($interface));
//                 if ($mac && $mac !== '00:00:00:00:00:00') {
//                     return $mac;
//                 }
//             }
            
//             // Cara 2: Fallback dengan exec
//             exec("ip link | grep 'link/ether' | awk '{print $2}'", $macOutput);
//             if (!empty($macOutput[0])) {
//                 $macAddress = trim($macOutput[0]);
//                 return $macAddress;
//             }
//         } 
//         // Untuk Windows
//         elseif (PHP_OS_FAMILY === 'Windows') {
//             exec("getmac /fo csv /nh", $macOutput);
//             if (!empty($macOutput[0])) {
//                 // Format output dari getmac adalah CSV, ambil kolom pertama
//                 $parts = explode(',', $macOutput[0]);
//                 if (isset($parts[0])) {
//                     return trim($parts[0], '"');
//                 }
//             }
//         }
        
//         return null;
//     } catch (\Exception $e) {
//         Log::error("Error mendapatkan MAC address: " . $e->getMessage());
//         return null;
//     }
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
//         // PERUBAHAN 1: Ganti getMacAddress() dengan getAllMacAddresses()
//         $macAddresses = $this->getAllMacAddresses();
        
//         // PERUBAHAN 2: Validasi array MAC addresses, bukan string tunggal
//         if (empty($macAddresses)) {
//             Log::warning("Gagal mendapatkan MAC Address untuk login dari IP: {$request->ip()}");
//             return back()->withErrors(['/' => 'Gagal mendapatkan identitas perangkat. Silakan hubungi administrator.'])->withInput();
//         }
        
//         // PERUBAHAN 3: Tambahkan log untuk debugging
//         Log::info("Daftar MAC addresses yang terdeteksi: " . implode(', ', $macAddresses));
        
//         // PERUBAHAN 4: Loop melalui setiap MAC address untuk verifikasi
//         $devicePermission = null;
//         $foundMac = null;
        
//         foreach ($macAddresses as $mac) {
//             $tempPermission = Permission::where('device_wifi_mac', $mac)
//                 ->orWhere('device_lan_mac', $mac)
//                 ->first();
                
//             if ($tempPermission) {
//                 $devicePermission = $tempPermission;
//                 $foundMac = $mac;
//                 Log::info("MAC Address yang cocok dengan database: {$mac}");
//                 break;
//             }
//         }
            
//         // Cek keberadaan permission - sama seperti kode asli
//         if (!$devicePermission) {
//             Log::warning("Perangkat tidak terdaftar mencoba login dari IP: {$request->ip()}");
//             return back()->withErrors(['/' => 'Perangkat ini tidak diizinkan untuk login.'])->withInput();
//         }
        
//         // PERUBAHAN 5: Tentukan jenis MAC address yang digunakan
//         $macType = "unknown";
//         if ($devicePermission->device_wifi_mac === $foundMac) {
//             $macType = "device_wifi_mac";
//             Log::info("MAC terdeteksi sebagai device_wifi_mac: {$foundMac}");
//         } elseif ($devicePermission->device_lan_mac === $foundMac) {
//             $macType = "device_lan_mac";
//             Log::info("MAC terdeteksi sebagai device_lan_mac: {$foundMac}");
//         }

//         // Proses login - tidak ada perubahan
//         if (Auth::attempt($attributes, $request->boolean('remember'))) {
//             $request->session()->regenerate();
//             // PERUBAHAN 6: Log lebih detail
//             Log::info("Successful login for username: {$request->username} with {$macType} MAC: {$foundMac}");

//             // Sisanya tetap sama...
//             RateLimiter::clear($rateLimiterKey);
//             $user = Auth::user();
//             if ($request->boolean('remember')) {
//                 $user->remember_me = $user->getRememberToken();
//                 $user->save();
//             }

//             $dashboards = [
//                 'isManager' => 'pages.dashboardManager',
//                 'isKasir' => 'pages.dashboardKasir',
//                 'isAdmin' => 'pages.dashboardAdmin',
//                 'isSupervisor' => 'pages.dashboardSupervisor',
//             ];

//             foreach ($dashboards as $gate => $dashboard) {
//                 if (Gate::allows($gate, $user)) {
//                     Log::info("User {$user->username} logged in with role: $gate");
//                     return redirect()->route($dashboard)->with('success', 'Anda berhasil login');
//                 }
//             }

//             Log::warning("User {$user->username} logged in but has no valid role");
//             return redirect('/')->with('warning', 'Akun Anda tidak memiliki peran yang valid.');
//         }
        
//         // Login gagal - tidak ada perubahan
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
//         // PERUBAHAN 1: Ganti getMacAddress() dengan getAllMacAddresses()
//         $macAddresses = $this->getAllMacAddresses();
        
//         // PERUBAHAN 2: Validasi array MAC addresses, bukan string tunggal
//         if (empty($macAddresses)) {
//             Log::warning("Gagal mendapatkan MAC Address untuk login dari IP: {$request->ip()}");
//             return back()->withErrors(['/' => 'Gagal mendapatkan identitas perangkat. Silakan hubungi administrator.'])->withInput();
//         }
        
//         // PERUBAHAN 3: Tambahkan log untuk debugging
//         Log::info("Daftar MAC addresses yang terdeteksi: " . implode(', ', $macAddresses));
        
//         // PERUBAHAN 4: Loop melalui setiap MAC address untuk verifikasi
//         $devicePermission = null;
//         $foundMac = null;
//         $macType = null;
        
//         foreach ($macAddresses as $mac) {
//             $tempPermission = Permission::where('device_wifi_mac', $mac)
//                 ->orWhere('device_lan_mac', $mac)
//                 ->first();
                
//             if ($tempPermission) {
//                 $devicePermission = $tempPermission;
//                 $foundMac = $mac;
                
//                 // Tentukan jenis MAC address yang digunakan
//                 if ($devicePermission->device_wifi_mac === $mac) {
//                     $macType = "device_wifi_mac";
//                 } elseif ($devicePermission->device_lan_mac === $mac) {
//                     $macType = "device_lan_mac";
//                 }
                
//                 Log::info("MAC Address yang cocok dengan database: {$mac} (Tipe: {$macType})");
//                 break;
//             }
//         }
            
//         // Cek keberadaan permission - sama seperti kode asli
//         if (!$devicePermission) {
//             Log::warning("Perangkat tidak terdaftar mencoba login dari IP: {$request->ip()}");
//             return back()->withErrors(['/' => 'Perangkat ini tidak diizinkan untuk login.'])->withInput();
//         }
        
//         // PERUBAHAN 5: Pastikan hanya menggunakan MAC yang valid
//         if ($macType === "device_wifi_mac" && $devicePermission->device_wifi_mac !== $foundMac) {
//             Log::warning("MAC Address tidak valid: {$foundMac}");
//             return back()->withErrors(['/' => 'Perangkat ini tidak diizinkan untuk login.'])->withInput();
//         }
        
//         if ($macType === "device_lan_mac" && $devicePermission->device_lan_mac !== $foundMac) {
//             Log::warning("MAC Address tidak valid: {$foundMac}");
//             return back()->withErrors(['/' => 'Perangkat ini tidak diizinkan untuk login.'])->withInput();
//         }

//         // Proses login - tidak ada perubahan
//         if (Auth::attempt($attributes, $request->boolean('remember'))) {
//             $request->session()->regenerate();
//             // PERUBAHAN 6: Log lebih detail
//             Log::info("Successful login for username: {$request->username} with {$macType} MAC: {$foundMac}");

//             // Sisanya tetap sama...
//             RateLimiter::clear($rateLimiterKey);
//             $user = Auth::user();
//             if ($request->boolean('remember')) {
//                 $user->remember_me = $user->getRememberToken();
//                 $user->save();
//             }

//             $dashboards = [
//                 'isManager' => 'pages.dashboardManager',
//                 'isKasir' => 'pages.dashboardKasir',
//                 'isAdmin' => 'pages.dashboardAdmin',
//                 'isSupervisor' => 'pages.dashboardSupervisor',
//             ];

//             foreach ($dashboards as $gate => $dashboard) {
//                 if (Gate::allows($gate, $user)) {
//                     Log::info("User {$user->username} logged in with role: $gate");
//                     return redirect()->route($dashboard)->with('success', 'Anda berhasil login');
//                 }
//             }

//             Log::warning("User {$user->username} logged in but has no valid role");
//             return redirect('/')->with('warning', 'Akun Anda tidak memiliki peran yang valid.');
//         }
        
//         // Login gagal - tidak ada perubahan
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
        // Dapatkan semua MAC addresses
        $macAddresses = $this->getAllMacAddresses();
        
        if (empty($macAddresses)) {
            Log::warning("Gagal mendapatkan MAC Address untuk login dari IP: {$request->ip()}");
            return back()->withErrors(['/' => 'Gagal mendapatkan identitas perangkat. Silakan hubungi administrator.'])->withInput();
        }
        
        Log::info("Daftar MAC addresses yang terdeteksi: " . implode(', ', $macAddresses));
        
        $devicePermission = null;
        $foundMac = null;
        $macType = null;
        
        foreach ($macAddresses as $mac) {
            // Normalisasi MAC address ke format yang sama dengan database (gunakan tanda hubung)
            $normalizedMac = str_replace(':', '-', strtoupper($mac));
            
            // Cari permission berdasarkan MAC address yang dinormalisasi
            $tempPermission = Permission::where('device_wifi_mac', $normalizedMac)
                ->orWhere('device_lan_mac', $normalizedMac)
                ->first();
                
            if ($tempPermission) {
                $devicePermission = $tempPermission;
                $foundMac = $normalizedMac;
                
                // Tentukan jenis MAC address yang digunakan
                if ($devicePermission->device_wifi_mac === $normalizedMac) {
                    $macType = "device_wifi_mac";
                } elseif ($devicePermission->device_lan_mac === $normalizedMac) {
                    $macType = "device_lan_mac";
                }
                
                Log::info("MAC Address yang cocok dengan database: {$normalizedMac} (Tipe: {$macType})");
                break;
            }
        }
            
        // Jika tidak ditemukan permission
        if (!$devicePermission) {
            Log::warning("Perangkat tidak terdaftar mencoba login dari IP: {$request->ip()}");
            return back()->withErrors(['/' => 'Perangkat ini tidak diizinkan untuk login.'])->withInput();
        }

        // Pastikan hanya menggunakan MAC yang valid
        if ($macType === "device_wifi_mac" && $devicePermission->device_wifi_mac !== $foundMac) {
            Log::warning("MAC Address tidak valid: {$foundMac}");
            return back()->withErrors(['/' => 'Perangkat ini tidak diizinkan untuk login.'])->withInput();
        }
        
        if ($macType === "device_lan_mac" && $devicePermission->device_lan_mac !== $foundMac) {
            Log::warning("MAC Address tidak valid: {$foundMac}");
            return back()->withErrors(['/' => 'Perangkat ini tidak diizinkan untuk login.'])->withInput();
        }

        // Proses login
        if (Auth::attempt($attributes, $request->boolean('remember'))) {
            $request->session()->regenerate();
            Log::info("Successful login for username: {$request->username} with {$macType} MAC: {$foundMac}");

            RateLimiter::clear($rateLimiterKey);
            $user = Auth::user();
            if ($request->boolean('remember')) {
                $user->remember_token = $user->getRememberToken();
                $user->save();
            }

            $dashboards = [
                'isManager' => 'pages.dashboardManager',
                'isKasir' => 'pages.dashboardKasir',
                'isAdmin' => 'pages.dashboardAdmin',
                'isSupervisor' => 'pages.dashboardSupervisor',
            ];

            foreach ($dashboards as $gate => $dashboard) {
                if (Gate::allows($gate, $user)) {
                    Log::info("User {$user->username} logged in with role: $gate");
                    return redirect()->route($dashboard)->with('success', 'Anda berhasil login');
                }
            }

            Log::warning("User {$user->username} logged in but has no valid role");
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
private function command_exists($cmd) {
    $return = shell_exec(sprintf("which %s 2>/dev/null", escapeshellarg($cmd)));
    return !empty($return);
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