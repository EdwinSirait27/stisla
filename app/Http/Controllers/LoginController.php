<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Rules\NoXSSInput;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Log;
class LoginController extends Controller
{
    public function index()
    {
        
        return view('pages.login');
    }
    /**
     * Memproses permintaan login
     */
    public function store(Request $request)
    {
        // dd($request->all());
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
            'username.required' => 'Username harus diisi.',
            'username.max' => 'Username tidak boleh lebih dari 12 karakter.',
            'password.min' => 'Username tidak boleh lebih dari 7 karakter.',
            'password.required' => 'Password harus diisi.',
            'password.max' => 'Password tidak boleh lebih dari 12 karakter.',
        ]);
        
        try {
            // Remember me functionality is handled through the second parameter
            if (Auth::attempt($attributes, $request->boolean('remember'))) {
                $request->session()->regenerate();
                
                // Update remember_token if the user chose to be remembered
                if ($request->boolean('remember')) {
                    $user = Auth::user();
                    $user->remember_token = Str::random(60); // Generate a secure token
                    $user->save();
                }
                
                Log::info("Successful login for username: {$request->username}");
        
                $user = Auth::user();
                $dashboards = [
                    'isSU' => 'dashboard-general-dashboard',
                    'isKasir' => 'dashboardKasir',
                    'isSupervisor' => 'dashboardSupervisor',
                    'isManager' => 'dashboardManager',
                    
                ];
        
                foreach ($dashboards as $gate => $dashboard) {
                    if (Gate::allows($gate, $user)) {
                        Log::info("User {$user->username} logged in with role: $gate");
                        return redirect($dashboard)->with(['success' => "Anda berhasil login sebagai $gate"]);
                    }
                }
        
                Auth::logout();
                return redirect('login')->with(['error' => 'Akses tidak diizinkan.']);
            }
        
            Log::warning("Failed login attempt for username: {$request->username}");
            return back()->withErrors(['login' => 'Username atau Password salah.']);
        } catch (\Exception $e) {
            Log::error("Login error: " . $e->getMessage());
            return back()->withErrors(['login' => 'Terjadi kesalahan. Silakan coba lagi.']);
        }
    }
    
    /**
     * Proses logout
     */
    public function destroy(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('login')->with(['success' => 'Anda berhasil logout.']);
    }
}