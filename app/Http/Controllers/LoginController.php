<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\UserSession;
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
                if (strip_tags($value) !== $value) {
                    $fail("Input $attribute mengandung tag HTML yang tidak diperbolehkan.");
                }
            }
        ],
        'password' => [
            'required',
            'string',
            'min:8',
            'max:20',
        ],
    ], [
        'username.required' => 'Username is required.',
        'username.min' => 'Username must be at least 7 characters.',
        'username.max' => 'Username cannot be more than 12 characters.',
        'password.required' => 'Password is required.',
        'password.min' => 'Password must be at least 8 characters.',
        'password.max' => 'Password cannot be more than 20 characters.',
    ]);
    $normalizedUsername = strtolower($request->username);
    $rateLimiterKey = "login:{$normalizedUsername}";
    if (RateLimiter::tooManyAttempts($rateLimiterKey, 10)) {
        Log::warning("Rate limiter triggered", [
            'username' => $normalizedUsername,
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
        ]);
        return back()->withErrors(['/' => 'Too many attempts. Please try again in 1 minute.']);
    }
    try {
        $attributes['username'] = $normalizedUsername;
        if (!Auth::attempt($attributes, $request->boolean('remember'))) {
            sleep(1);
            Log::warning("Failed login attempt", [
                'username' => $normalizedUsername,
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);
            RateLimiter::hit($rateLimiterKey, 60);
            return back()->withErrors(['/' => 'Wrong usename or Password.']);
        }
        $request->session()->regenerate();
        RateLimiter::clear($rateLimiterKey);
        $user = Auth::user();
        /**
         * 🔍 Cek relasi employee dan status aktif
         */
        if (!$user->employee) {
            Auth::logout();
            RateLimiter::clear($rateLimiterKey);
            return back()->withErrors(['/' => 'Your account is not yet connected to employee data.']);
        }
        if ($user->employee->status !== 'Active') {
            Auth::logout();
            RateLimiter::clear($rateLimiterKey);
            return back()->withErrors(['/' => 'Your account is inactive. Please contact HR..']);
        }
        /**
         * 🔁 Cek apakah user sudah login di device lain
         */
        $currentSessionId = $request->session()->getId();
        $existingSession = UserSession::where('user_id', $user->id)
            ->where('session_id', '!=', $currentSessionId)
            ->first();
        if ($existingSession) {
            if (!$request->has('force_login')) {
                Log::info("User already logged in elsewhere", [
                    'username' => $normalizedUsername,
                    'current_session' => $currentSessionId,
                    'existing_session' => $existingSession->session_id,
                ]);
                Auth::logout();
                RateLimiter::clear($rateLimiterKey);
                return back()->with('confirm_force_login', [
                    'message' => 'You are already logged in on another device. Will you continue to log out from that device?',
                    'username' => $request->username,
                    'password' => $request->password,
                    'remember' => $request->boolean('remember')
                ]);
            }
            $this->logoutOtherDevices($user, $currentSessionId);
        }
        /**
         * 🗂️ Simpan sesi login
         */
        UserSession::updateOrCreate(
            ['user_id' => $user->id, 'session_id' => $currentSessionId],
            [
                'ip_address' => $request->ip(),
                'last_activity' => now(),
                'device_type' => $request->header('User-Agent')
            ]
        );
        /**
         * 🎯 Redirect berdasarkan role (Spatie)
         */
        $dashboardRoutes = [
            'Admin' => 'pages.dashboardAdmin',
            'Human' => 'pages.Dashboard',
            'Manager' => 'pages.dashboardTeam',
            'Director' => 'pages.dashboardDirector',
            'HeadHR' => 'pages.dashboardHR',
            'HR' => 'pages.dashboardHR',
            ];
        foreach ($dashboardRoutes as $role => $route) {
            if ($user->hasRole($role)) {
                Log::info("User logged in", [
                    'username' => $normalizedUsername,
                    'role' => $role,
                    'ip' => $request->ip(),
                    'permissions' => implode(', ', $user->getPermissionsViaRoles()->pluck('name')->toArray())
                ]);
                return redirect()->route($route)->with('success', 'Success login, Goodluck!!!');
            }
        }
        Log::warning("User has no valid role", [
            'username' => $normalizedUsername,
            'ip' => $request->ip()
        ]);
        Auth::logout();
        RateLimiter::clear($rateLimiterKey);
        return redirect('/')->with('warning', 'Your account does not have a valid role.');
    } catch (\Exception $e) {
        Log::error("Login error", [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'ip' => $request->ip(),
            'username' => $normalizedUsername ?? null,
        ]);
        return back()->withErrors(['/' => 'An error occurred. Please try again.']);
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
}