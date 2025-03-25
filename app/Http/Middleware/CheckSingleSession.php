<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
class CheckSingleSession
{
    // public function handle($request, Closure $next)
    // {
    //     $user = $request->user();
        
    //     if ($user) {
    //         // Generate UUID session ID jika belum ada
    //         if (empty($user->session_id)) {
    //             $user->session_id = Str::uuid()->toString();
    //             $user->save();
    //             $request->session()->put('uuid_session_id', $user->session_id);
    //         }
            
    //         // Jika UUID session ID tidak match
    //         if ($user->session_id !== $request->session()->get('uuid_session_id')) {
    //             Auth::logout();
                
    //             // Hapus session ID dari database
    //             $user->update(['session_id' => null]);
                
    //             return redirect('/')->with('success', 'Anda telah login di perangkat lain, Bye.');   
    //         }
    //     }
        
    //     return $next($request);
    // }
}
