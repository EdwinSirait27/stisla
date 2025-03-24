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

//     $rateLimiterKey = "/:{$request->ip()}:{$request->username}";

//     if (RateLimiter::tooManyAttempts($rateLimiterKey, 10)) {
//         Log::warning("Rate limiter triggered for username: {$request->username}");
//         return back()->withErrors(['login' => 'Terlalu banyak percobaan login. Silakan coba lagi nanti.']);
//     }

//     try {
//         $macAddress = null;
//         if (PHP_OS_FAMILY === 'Linux') {
//             exec("cat /sys/class/net/eth0/address", $mac);
//         } elseif (PHP_OS_FAMILY === 'Windows') {
//             exec("getmac", $mac);
//         }
        
//         if (!isset($mac[0])) {
//             return back()->withErrors(['/' => 'Gagal mendapatkan MAC Address perangkat.'])->withInput();
//         }

//         $macAddress = trim($mac[0]);

//         // **CEK APAKAH MAC ADDRESS TERDAFTAR DI DATABASE**
//         $deviceRegistered = Permission::where('device_wifi_mac', $macAddress)->exists();

//         if (!$deviceRegistered) {
//             Log::warning("Unauthorized device attempted login: {$macAddress}");
//             return back()->withErrors(['/' => 'Perangkat ini tidak diizinkan untuk login.'])->withInput();
//         }

//         // **PROSES LOGIN JIKA DEVICE SUDAH TERDAFTAR**
//         if (Auth::attempt($attributes, $request->boolean('remember'))) {
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

//         Log::warning("Failed login attempt for username: {$request->username}");
//         RateLimiter::hit($rateLimiterKey, 60);
//         return back()->withErrors(['login' => 'Username atau Password salah.']);
//     } catch (\Exception $e) {
//         Log::error("Login error: " . $e->getMessage());
//         return back()->withErrors(['login' => 'Terjadi kesalahan. Silakan coba lagi.']);
//     }
// }
public function store(Request $request)
{
    // Validasi input (tetap seperti kode asli Anda)
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

    // Rate limiter (tetap seperti kode asli Anda)
    $rateLimiterKey = "/:{$request->ip()}:{$request->username}";
    if (RateLimiter::tooManyAttempts($rateLimiterKey, 10)) {
        Log::warning("Rate limiter triggered for username: {$request->username}");
        return back()->withErrors(['/' => 'Terlalu banyak percobaan login. Silakan coba lagi nanti.']);
    }

    try {
        // TAHAP 1: Mendapatkan MAC Address perangkat
        $macAddress = $this->getMacAddress();
        
        // TAHAP 2: Validasi apakah MAC berhasil didapatkan
        if (!$macAddress) {
            Log::warning("Gagal mendapatkan MAC Address untuk login dari IP: {$request->ip()}");
            return back()->withErrors(['/' => 'Gagal mendapatkan identitas perangkat. Silakan hubungi administrator.'])->withInput();
        }

        // TAHAP 3: Memeriksa apakah MAC Address terdaftar
        $deviceRegistered = Permission::where('device_wifi_mac','device_lan_wifi', $macAddress)->exists();

        // TAHAP 4: Menolak akses jika tidak terdaftar
        if (!$deviceRegistered) {
            Log::warning("Perangkat tidak terdaftar mencoba login: {$macAddress} dari IP: {$request->ip()}");
            return back()->withErrors(['/' => 'Perangkat ini tidak diizinkan untuk login.'])->withInput();
        }

        // Lanjutkan proses login jika device sudah terdaftar
               if (Auth::attempt($attributes, $request->boolean('remember'))) {
            $request->session()->regenerate();
            Log::info("Successful login for username: {$request->username}");

            // **Bersihkan rate limiter jika login berhasil**
            RateLimiter::clear($rateLimiterKey);

            $user = Auth::user();

            if ($request->boolean('remember')) {
                $user->remember_me = $user->getRememberToken();
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
        
        // Login gagal (tetap seperti kode asli Anda)
        Log::warning("Failed login attempt for username: {$request->username}");
        RateLimiter::hit($rateLimiterKey, 60);
        return back()->withErrors(['/' => 'Username atau Password salah.']);
    } catch (\Exception $e) {
        Log::error("Login error: " . $e->getMessage());
        return back()->withErrors(['/' => 'Terjadi kesalahan. Silakan coba lagi.']);
    }
}

// Tambahkan method helper ini di dalam controller yang sama
private function getMacAddress()
{
    try {
        $macAddress = null;
        
        // Coba untuk Linux terlebih dahulu
        if (PHP_OS_FAMILY === 'Linux') {
            // Cara 1: Cek semua interface jaringan
            $interfaces = glob('/sys/class/net/*/address');
            foreach ($interfaces as $interface) {
                $mac = trim(file_get_contents($interface));
                if ($mac && $mac !== '00:00:00:00:00:00') {
                    return $mac;
                }
            }
            
            // Cara 2: Fallback dengan exec
            exec("ip link | grep 'link/ether' | awk '{print $2}'", $macOutput);
            if (!empty($macOutput[0])) {
                $macAddress = trim($macOutput[0]);
                return $macAddress;
            }
        } 
        // Untuk Windows
        elseif (PHP_OS_FAMILY === 'Windows') {
            exec("getmac /fo csv /nh", $macOutput);
            if (!empty($macOutput[0])) {
                // Format output dari getmac adalah CSV, ambil kolom pertama
                $parts = explode(',', $macOutput[0]);
                if (isset($parts[0])) {
                    return trim($parts[0], '"');
                }
            }
        }
        
        return null;
    } catch (\Exception $e) {
        Log::error("Error mendapatkan MAC address: " . $e->getMessage());
        return null;
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