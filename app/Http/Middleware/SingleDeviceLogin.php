<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\UserSession;

class SingleDeviceLogin
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $currentSessionId = session()->getId();

            // Find active sessions for this user
            $activeSessions = UserSession::where('user_id', $user->id)
                ->where('session_id', '!=', $currentSessionId)
                ->get();

            // Check if there are active sessions from other devices
            if ($activeSessions->isNotEmpty()) {
                // Log the attempt
                Log::warning("User {$user->username} attempting to access with multiple active sessions");

                // Logout and redirect to login with a message
                Auth::logout();
                return redirect('/')->with('error', 'Anda telah logout dari perangkat lain. Silakan login kembali.');
            }
        }

        return $next($request);
    }
}
