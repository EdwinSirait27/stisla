<?php

namespace App\Http\Controllers;

use App\Rules\NoXSSInput;
use App\Services\PasswordResetLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * Halaman publik yang dibuka dari link email reset password (sekali pakai).
 */
class PasswordResetLinkController extends Controller
{
    public function __construct(protected PasswordResetLinkService $links) {}

    public function show(string $token)
    {
        $link = $this->links->findUsable($token);

        return view('pages.password-reset-link', [
            'token'   => $token,
            'link'    => $link,
            'invalid' => $link === null,
        ]);
    }

    public function update(Request $request, string $token)
    {
        $link = $this->links->findUsable($token);
        if (!$link) {
            return redirect()->route('password.link.show', $token);
        }

        $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'max:20',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])\S+$/',
                new NoXSSInput(),
            ],
        ], [
            'password.required'  => 'Password baru wajib diisi.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.max'       => 'Password maksimal 20 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
            'password.regex'     => 'Password harus berisi huruf besar, huruf kecil, angka, dan simbol, tanpa spasi.',
        ]);

        // Password baru tidak boleh sama dengan username (akan dipaksa ganti lagi oleh ForcePasswordChange)
        if (strtolower($request->password) === strtolower($link->user->username)) {
            return back()->withErrors(['password' => 'Password tidak boleh sama dengan username.']);
        }

        try {
            $this->links->reset($link, $request->password);
        } catch (RuntimeException $e) {
            return redirect()->route('password.link.show', $token);
        }

        // Kalau browser ini sedang login sebagai user lain, keluarkan supaya bisa login ulang dengan akun yang benar
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('login')
            ->with('success', 'Password berhasil diganti. Silakan login dengan password baru.');
    }
}
