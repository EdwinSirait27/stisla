<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    public function index()
    {   
        return view('pages.login');
    }
    public function store(Request $request)
    {
        $attributes = $request->validate([
            'username' => [
                'required',
                'string',
                'min:7',
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
                'min:7',
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
    
        $rateLimiterKey = "login:{$request->ip()}:{$request->username}";
    
        if (RateLimiter::tooManyAttempts($rateLimiterKey, 10)) {
            Log::warning("Rate limiter triggered for username: {$request->username}");
            return back()->withErrors(['login' => 'Terlalu banyak percobaan login. Silakan coba lagi nanti.']);
        }
        
        try {
            if (Auth::attempt($attributes, $request->boolean('remember'))) {
                $request->session()->regenerate();
                Log::info("Successful login for username: {$request->username}");
        
                // Clear rate limiter on success
                RateLimiter::clear($rateLimiterKey);
        
                $user = Auth::user();
                
                // Update remember_me column if "remember me" was checked
                if ($request->boolean('remember')) {
                    $user->remember_me = $user->getRememberToken();
                    $user->save();
                }
                
                $dashboards = [
                    'isManager' => 'pages.dashboardManager',
                    'isKasir' => 'pages.dashboardKasir',
                    'isAdmin' => 'pages.dashboardAdmin',
                ];
        
                foreach ($dashboards as $gate => $dashboard) {
                    if (Gate::allows($gate, $user)) {
                        Log::info("User {$user->username} logged in with role: $gate");
                        return redirect()->route($dashboard)->with('success', 'Anda berhasil login');
                    }
                }
        
                // Jika tidak memiliki role yang sesuai
                Log::warning("User {$user->username} logged in but has no valid role");
                
                // Tidak logout, tetapi redirect ke halaman default
                return redirect('/')->with('warning', 'Akun Anda tidak memiliki peran yang valid.');
            }
        
            Log::warning("Failed login attempt for username: {$request->username}");
            RateLimiter::hit($rateLimiterKey, 60); // Expire in 60 seconds
            return back()->withErrors(['login' => 'Username atau Password salah.']);
        } catch (\Exception $e) {
            Log::error("Login error: " . $e->getMessage());
            return back()->withErrors(['login' => 'Terjadi kesalahan. Silakan coba lagi.']);
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