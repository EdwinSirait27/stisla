<?php

namespace App\Http\Controllers;
use App\Models\TwoFactorLog;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use App\Services\LoginSessionService;
use App\Services\LoginThrottle;
use App\Services\DashboardRedirectService;
use Ramsey\Uuid\Uuid;
class TwoFactorController extends Controller
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Login lewat email (tanpa password) hanya punya satu faktor, jadi kodenya dijaga ketat:
     * 5 kode salah per akun per 24 jam, setelah itu mode email terkunci dan harus pakai
     * username + password. Recovery code juga tidak berlaku di mode ini.
     */
    private const EMAIL_MODE_MAX_FAILS = 5;
    private const EMAIL_MODE_DECAY     = 86400;

    // ── Halaman verifikasi TOTP setelah login ─────────────────────
    public function showVerify()
    {
        $pending = $this->pending2fa();
        if (!$pending) {
            return redirect()->route('login')->withErrors(['/' => 'Sesi verifikasi 2FA habis. Silakan login ulang.']);
        }
        return view('auth.two-factor-verify', ['viaEmail' => $pending['via'] === 'email']);
    }

    public function verify(Request $request, LoginSessionService $loginSession)
    {
        $pending = $this->pending2fa();
        if (!$pending) {
            return redirect()->route('login')->withErrors(['/' => 'Sesi verifikasi 2FA habis. Silakan login ulang.']);
        }

        $viaEmail = $pending['via'] === 'email';

        $request->validate([
            'code' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($viaEmail) {
                    $len = strlen(trim($value));
                    if ($viaEmail && $len !== 6) {
                        $fail('Masukkan kode 6 digit dari aplikasi Authenticator.');
                    } elseif ($len !== 6 && $len !== 10) {
                        $fail('Kode tidak valid. Masukkan kode 6-digit dari Authenticator atau recovery code 10 karakter.');
                    }
                },
            ],
        ]);

        if ($viaEmail) {
            return $this->verifyEmailMode($request, $loginSession, $pending);
        }

        $user           = User::with('employee')->findOrFail($pending['user_id']);
        $rateLimiterKey = "2fa:{$user->id}";

        if (RateLimiter::tooManyAttempts($rateLimiterKey, 10)) {
            $this->log2FAEvent($user, 'failed', $request);
            $minutes = max(1, (int) ceil(RateLimiter::availableIn($rateLimiterKey) / 60));
            return back()->withErrors(['code' => "Terlalu banyak percobaan. Coba lagi dalam {$minutes} menit."]);
        }

        $inputCode  = strtoupper(trim($request->code));
        $isRecovery = strlen($inputCode) === 10;
        $valid      = false;

        if ($isRecovery) {
            $valid = $user->useRecoveryCode($inputCode);
            if ($valid) {
                $this->log2FAEvent($user, 'recovery_used', $request);
                Log::warning('2FA recovery code used', ['user_id' => $user->id, 'ip' => $request->ip()]);
            }
        } else {
            $valid = $this->checkTotp($user, $inputCode);
        }

        if (!$valid) {
            RateLimiter::hit($rateLimiterKey, 300);
            $this->log2FAEvent($user, 'failed', $request);
            return back()->withErrors(['code' => 'Kode tidak valid atau sudah dipakai. Tunggu kode berikutnya di aplikasi Authenticator.']);
        }

        RateLimiter::clear($rateLimiterKey);

        return $this->completeLogin($request, $loginSession, $user, 'verified');
    }

    /** Langkah kode untuk login email + 2FA. Sesi umpan (email tidak memenuhi syarat) diperlakukan sama persis. */
    private function verifyEmailMode(Request $request, LoginSessionService $loginSession, array $pending)
    {
        $user = $pending['user_id'] ? User::with('employee')->find($pending['user_id']) : null;
        $limitKey = $user ? "2fa-email:{$user->id}" : '2fa-email:decoy:' . $pending['decoy'];
        $throttle = LoginThrottle::keys($user ? $user->username : 'mail:' . $pending['decoy'], $request->ip());

        if (RateLimiter::tooManyAttempts($limitKey, self::EMAIL_MODE_MAX_FAILS)
            || LoginThrottle::lockedFor($throttle)) {
            $request->session()->forget('2fa');
            if ($user) {
                $this->log2FAEvent($user, 'failed', $request);
            }
            return redirect()->route('login')->withErrors([
                'throttle' => 'Login dengan 2FA dikunci sementara karena terlalu banyak kode salah. Silakan login dengan username dan password.',
            ]);
        }

        // Syarat dicek ulang: izin bisa dicabut admin di antara dua langkah.
        $eligible = $user && $user->requiresTwoFactor() && $user->hasTwoFactorEnabled();
        $valid    = $eligible && $this->checkTotp($user, strtoupper(trim($request->code)));

        if (!$valid) {
            RateLimiter::hit($limitKey, self::EMAIL_MODE_DECAY);
            LoginThrottle::hit($throttle); // ikut dihitung di batas login username (dan IP) yang sama
            if ($user) {
                $this->log2FAEvent($user, 'failed', $request);
                activity('auth')->causedBy($user)->performedOn($user)->event('login_failed')
                    ->withProperties(['channel' => 'web-2fa-email', 'ip' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 255)])
                    ->log('Percobaan login email + 2FA gagal: kode salah');
            }
            return back()->withErrors(['code' => 'Kode tidak valid atau sudah dipakai. Tunggu kode berikutnya di aplikasi Authenticator.']);
        }

        RateLimiter::clear($limitKey);
        LoginThrottle::clear($throttle);
        activity('auth')->causedBy($user)->performedOn($user)->event('login_2fa_email')
            ->withProperties(['channel' => 'web-2fa-email', 'ip' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 255)])
            ->log('Login dengan email + 2FA (tanpa password)');

        return $this->completeLogin($request, $loginSession, $user, 'verified');
    }

    /** TOTP 6 digit dengan anti replay: kode yang sudah pernah dipakai (atau lebih lama) ditolak. */
    private function checkTotp(User $user, string $code): bool
    {
        if (!preg_match('/^\d{6}$/', $code) || !$user->two_factor_secret) {
            return false;
        }
        $tsKey = "2fa:last-ts:{$user->id}";
        $ts    = $this->google2fa->verifyKeyNewer(decrypt($user->two_factor_secret), $code, (int) Cache::get($tsKey, 0), 1);
        if ($ts === false) {
            return false;
        }
        Cache::put($tsKey, $ts, now()->addMinutes(10));
        return true;
    }

    private function completeLogin(Request $request, LoginSessionService $loginSession, User $user, string $event)
    {
        $request->session()->forget('2fa');

        if (!LoginSessionService::canLogin($user)) {
            return redirect()->route('login')->withErrors(['/' => 'Akun ini tidak dapat digunakan untuk login. Hubungi HR.']);
        }

        Auth::login($user); // tanpa "ingat saya": cookie remember akan melewati 2FA di kunjungan berikutnya
        $request->session()->regenerate();
        $this->log2FAEvent($user, $event, $request);

        return $loginSession->finish($request, $user, false, false);
    }

    /**
     * Login yang menunggu kode 2FA, null kalau tidak ada atau sudah lewat waktunya.
     * via = 'password' (setelah username + password) atau 'email' (login email + 2FA).
     */
    private function pending2fa(): ?array
    {
        $pending = session('2fa');
        $valid = is_array($pending)
            && ($pending['expires_at'] ?? 0) >= now()->timestamp
            && (!empty($pending['user_id']) || (($pending['via'] ?? null) === 'email' && !empty($pending['decoy'])));

        if (!$valid) {
            session()->forget('2fa');
            return null;
        }

        return [
            'user_id' => !empty($pending['user_id']) ? (string) $pending['user_id'] : null,
            'via'     => ($pending['via'] ?? 'password') === 'email' ? 'email' : 'password',
            'decoy'   => $pending['decoy'] ?? null,
        ];
    }

    // ── Setup 2FA (user scan QR) ──────────────────────────────────

    public function showSetup()
    {
        /** @var User $user */
        $user = Auth::user();
        if ($user->hasTwoFactorEnabled()) {
            // Sudah aktif — tidak perlu setup lagi
            return redirect()->route('pages.feature-profile')
                ->with('info', '2FA sudah aktif di akun Anda.');
        }
        // Generate secret baru HANYA kalau belum ada
        // Kalau sudah ada tapi belum confirmed, pakai yang existing
        // supaya QR tidak berubah kalau user refresh halaman
        if (!$user->two_factor_secret) {
            $secret = $this->google2fa->generateSecretKey();
            $user->forceFill([
                'two_factor_secret' => encrypt($secret),
            ])->save();
        } else {
            $secret = decrypt($user->two_factor_secret);
        }
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email ?? $user->username,
            $secret
        );
        $qrCodeSvg = $this->generateQrSvg($qrCodeUrl);
        return view('auth.two-factor-setup', compact('secret', 'qrCodeSvg'));
    }
    public function confirmSetup(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // $request->validate([
        //     'code' => ['required', 'string', 'size:6', 'numeric'],
        // ]);
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        if (!$user->two_factor_secret) {
            return back()->withErrors(['code' => 'Setup belum dimulai. Refresh halaman.']);
        }

        $secret = decrypt($user->two_factor_secret);
        $valid  = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'Kode tidak cocok. Pastikan Anda scan QR yang benar.']);
        }

        // Konfirmasi 2FA aktif
        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        // Generate & tampilkan recovery codes SEKALI
        $recoveryCodes = $user->generateRecoveryCodes();
        $user->storeRecoveryCodes($recoveryCodes);

        $this->log2FAEvent($user, 'enabled', $request);

        Log::info('2FA enabled', ['user_id' => $user->id]);

        // Simpan sementara di session untuk ditampilkan sekali
        session(['2fa.recovery_codes' => $recoveryCodes]);

        return redirect()->route('2fa.recovery-codes');
    }
    // ── Tampilkan recovery codes (HANYA sekali setelah setup) ─────
    public function showRecoveryCodes()
    {
        $codes = session('2fa.recovery_codes');
        if (!$codes) {
            return redirect()->route('pages.feature-profile'); // sudah pernah lihat
        }
        session()->forget('2fa.recovery_codes');

        // Tujuan tombol "Lanjut": sama dengan aturan setelah login (password default dulu, lalu dashboard per permission).
        /** @var User $user */
        $user = Auth::user();
        if (LoginSessionService::hasDefaultPassword($user)) {
            $nextUrl   = route('pages.change-password');
            $nextLabel = 'Lanjut ganti password';
        } elseif ($route = DashboardRedirectService::routeNameFor($user)) {
            $nextUrl   = route($route);
            $nextLabel = 'Lanjut ke Dashboard';
        } else {
            $nextUrl   = route('pages.feature-profile');
            $nextLabel = 'Lanjut ke Profil';
        }

        return view('auth.two-factor-recovery-codes', compact('codes', 'nextUrl', 'nextLabel'));
    }

    // ── Disable 2FA — dipanggil oleh admin ───────────────────────
    public function adminDisable(Request $request, string $userId)
    {
        /** @var User $admin */
        $admin = Auth::user();

        if (!$admin->hasRole('Admin')) {
            abort(403);
        }

        $target = User::findOrFail($userId);
        $target->forceFill([
            'two_factor_secret'         => null,
            'two_factor_confirmed_at'   => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        $this->log2FAEvent($target, 'disabled', $request);

        Log::info('2FA disabled by admin', [
            'target_user_id' => $target->id,
            'admin_id'       => $admin->id,
        ]);

        return back()->with('success', "2FA untuk user {$target->username} berhasil dinonaktifkan.");
    }

    // ── Admin toggle required ─────────────────────────────────────
    public function adminToggleRequired(Request $request, string $userId)
    {
        /** @var User $admin */
        $admin = Auth::user();

        if (!$admin->hasRole('Admin')) {
            abort(403);
        }

        $target = User::findOrFail($userId);
        $target->forceFill([
            'two_factor_required' => !$target->two_factor_required,
        ])->save();

        $status = $target->two_factor_required ? 'diwajibkan' : 'tidak diwajibkan';

        return back()->with('success', "2FA untuk user {$target->username} sekarang {$status}.");
    }

    // ── Helpers ───────────────────────────────────────────────────
    private function generateQrSvg(string $url): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        return $writer->writeString($url);
    }

    private function log2FAEvent(User $user, string $event, Request $request): void
    {
        try {
            TwoFactorLog::create([
                'id'         => Uuid::uuid7()->toString(),
                'user_id'    => $user->id,
                'event'      => $event,
                'ip_address' => $request->ip(),
                'device_type' => $request->header('User-Agent'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to log 2FA event', ['message' => $e->getMessage()]);
        }
    }
}
