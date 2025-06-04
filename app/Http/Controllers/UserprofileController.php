<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserprofileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('pages.feature-profile', compact('user'));
    }
    public function updatePassword(Request $request)
{
    $request->validate([
        'password' => ['nullable', 'string', 'min:8'],
    ]);

    $user = Auth::user();

    // Update password hanya jika new_password ada isinya
    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    // Update name jika diisi (harus ada karena required)
    
    if ($user->save()) {
        Log::info("Data berhasil diperbarui untuk user ID {$user->id}");
        return back()->with('status', 'Success');
    } else {
        Log::error("Gagal menyimpan data untuk user ID {$user->id}");
        return back()->withErrors(['update' => 'Gagal menyimpan perubahan']);
    }
}
}

