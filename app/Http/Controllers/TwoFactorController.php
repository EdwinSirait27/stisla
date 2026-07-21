<?php

namespace App\Http\Controllers;
use App\Models\TwoFactorLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\RateLimiter;
use Ramsey\Uuid\Uuid;
class TwoFactorController extends Controller
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    // ── Halaman verifikasi TOTP setelah login ─────────────────────
    public function showVerify()
    {
        if (!session('2fa.user_id')) {
            return redirect()->route('login');
        }
        return view('auth.two-factor-verify');
    }

    public function verify(Request $request)
    {
        $userId = session('2fa.user_id');
        if (!$userId) {
            return redirect()->route('login');
        }
        $user       = User::findOrFail($userId);
        $rateLimiterKey = "2fa:{$user->id}";
        // Max 5 percobaan per 5 menit
        if (RateLimiter::tooManyAttempts($rateLimiterKey, 5)) {
            $this->log2FAEvent($user, 'failed', $request);
            return back()->withErrors(['code' => 'Terlalu banyak percobaan. Coba lagi dalam 5 menit.']);
        }
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $inputCode = trim($request->code);

        // Coba validasi TOTP dulu
        $secret = decrypt($user->two_factor_secret);
        $valid  = $this->google2fa->verifyKey($secret, $inputCode);

        if (!$valid) {
            // Coba recovery code
            $valid = $user->useRecoveryCode($inputCode);

            if ($valid) {
                $this->log2FAEvent($user, 'recovery_used', $request);
                Log::warning('2FA recovery code used', [
                    'user_id' => $user->id,
                    'ip'      => $request->ip(),
                ]);
            }
        }

        if (!$valid) {
            RateLimiter::hit($rateLimiterKey, 300); // 5 menit
            $this->log2FAEvent($user, 'failed', $request);
            return back()->withErrors(['code' => 'Kode tidak valid. Pastikan waktu HP Anda sudah sinkron.']);
        }

        // Berhasil — login user & bersihkan session flag
        RateLimiter::clear($rateLimiterKey);
        Auth::loginUsingId($user->id);
        session()->forget('2fa.user_id');
        $request->session()->regenerate();

        $this->log2FAEvent($user, 'verified', $request);

        // Lanjutkan ke redirect yang sama seperti login normal
        $redirect = \App\Services\DashboardRedirectService::redirectForUser($user);
        return $redirect
            ? $redirect->with('success', 'Success login, Goodluck!!!')
            : redirect('/')->with('warning', 'Your account does not have a valid role.');
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
        return view('auth.two-factor-recovery-codes', compact('codes'));
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
