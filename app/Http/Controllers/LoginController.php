<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSession;
use App\Rules\NoXSSInput;
use App\Services\LoginSessionService;
use App\Services\LoginThrottle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    private const GENERIC_FAIL = 'Username atau password salah.';
    private const BLOCKED_ACCOUNT = 'Akun ini tidak dapat digunakan untuk login. Hubungi HR.';

    public function __construct(private LoginSessionService $loginSession)
    {
    }

    public function index()
    {
        return view('pages.login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => [
                'required',
                'string',
                'min:7',
                'max:12',
                'regex:/^[a-zA-Z0-9_-]+$/',
                new NoXSSInput(),
            ],
            'password' => ['required', 'string', 'min:8', 'max:128'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.min'      => 'Username minimal 7 karakter.',
            'username.max'      => 'Username maksimal 12 karakter.',
            'username.regex'    => 'Username hanya boleh huruf, angka, - dan _.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
            'password.max'      => 'Password maksimal 128 karakter.',
        ]);

        $username = strtolower($request->username);
        $keys = LoginThrottle::keys($username, $request->ip());

        if ($seconds = LoginThrottle::lockedFor($keys)) {
            Log::warning('Login rate limit hit', ['username' => $username, 'ip' => $request->ip()]);

            return back()->withInput($request->only('username'))->withErrors([
                'throttle' => 'Terlalu banyak percobaan login. Coba lagi dalam ' . max(1, (int) ceil($seconds / 60)) . ' menit.',
            ]);
        }

        try {
            $user = User::with('employee')->where('username', $username)->first();

            // Hash tetap dihitung walau user tidak ada, supaya waktu respons tidak membocorkan username.
            $passwordOk = $user
                ? Hash::check($request->password, $user->password)
                : (Hash::check($request->password, Hash::make('timing-equaliser')) && false);

            if (!$passwordOk) {
                LoginThrottle::hit($keys);
                Log::warning('Failed login attempt', ['username' => $username, 'ip' => $request->ip()]);
                if ($user) {
                    // Muncul di Activity Log milik user itu (percobaan login dengan password salah)
                    activity('auth')->causedBy($user)->performedOn($user)->event('login_failed')
                        ->withProperties(['channel' => 'web', 'ip' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 255)])
                        ->log('Percobaan login web gagal: password salah');
                }

                return back()->withInput($request->only('username'))->withErrors(['/' => self::GENERIC_FAIL]);
            }

            if (!LoginSessionService::canLogin($user)) {
                // Dihitung sebagai gagal juga, dan pesannya satu untuk semua alasan.
                LoginThrottle::hit($keys);
                activity('auth')->causedBy($user)->performedOn($user)->event('login_blocked')
                    ->withProperties(['channel' => 'web', 'ip' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 255)])
                    ->log('Login web ditolak: ' . ($user->employee ? 'status karyawan ' . $user->employee->status : 'akun belum terhubung ke data karyawan'));
                Log::warning('Login blocked: employee missing or not active', [
                    'user_id' => $user->id,
                    'status'  => $user->employee?->status,
                    'ip'      => $request->ip(),
                ]);

                return back()->withInput($request->only('username'))->withErrors(['/' => self::BLOCKED_ACCOUNT]);
            }

            LoginThrottle::clear($keys);

            // 2FA aktif: belum login sama sekali sampai kode valid.
            if ($user->hasTwoFactorEnabled()) {
                $request->session()->regenerate();
                $request->session()->put('2fa', [
                    'user_id'    => $user->id,
                    'expires_at' => now()->addMinutes(10)->timestamp,
                ]);

                return redirect()->route('2fa.verify');
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return $this->loginSession->finish($request, $user, false, $request->boolean('remember'));
        } catch (\Throwable $e) {
            Log::error('Login error', ['message' => $e->getMessage(), 'ip' => $request->ip(), 'username' => $username]);

            return back()->withInput($request->only('username'))->withErrors(['/' => 'Terjadi kesalahan. Silakan coba lagi.']);
        }
    }

    /** Konfirmasi "lanjutkan login & keluarkan perangkat lain" (token sekali pakai dari LoginSessionService). */
    public function confirmForceLogin(Request $request)
    {
        $pending = $this->loginSession->consumePending($request, $request->input('token'));
        if (!$pending) {
            return redirect()->route('login')->withErrors(['/' => 'Konfirmasi sudah kedaluwarsa. Silakan login ulang.']);
        }

        $user = User::with('employee')->find($pending['user_id']);
        if (!$user || !LoginSessionService::canLogin($user)) {
            return redirect()->route('login')->withErrors(['/' => self::BLOCKED_ACCOUNT]);
        }

        Auth::login($user, (bool) $pending['remember']);
        $request->session()->regenerate();

        return $this->loginSession->finish($request, $user, true, (bool) $pending['remember']);
    }

    public function destroy(Request $request)
    {
        if ($user = Auth::user()) {
            UserSession::where('user_id', $user->id)
                ->where('session_id', $request->session()->getId())
                ->delete();
        }

        Auth::logout(); // juga mengganti remember token, jadi cookie "ingat saya" lama tidak berlaku
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda berhasil logout');
    }
}
