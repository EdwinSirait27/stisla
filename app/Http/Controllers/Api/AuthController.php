<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PushToken;
use App\Models\RegisteredDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use App\Services\LoginSessionService;
use App\Services\LoginThrottle;
use App\Services\TwoFactorVerifier;


class AuthController extends Controller
{
    public function appConfig()
    {
        $logoPath = 'logos/asianbay.png';

        $logoUrl = Storage::disk('s3')->exists($logoPath)
            ? Storage::disk('s3')->temporaryUrl($logoPath, now()->addHours(1))
            : null;

        return response()->json([
            'success' => true,
            'data' => [
                'logo_url' => $logoUrl,
            ],
        ]);
    }
    /** Catat aktivitas login/logout aplikasi mobile ke Spatie activity log (log_name "auth"). */
    private function logAuth(User $user, string $event, string $description, Request $request, array $extra = []): void
    {
        try {
            activity('auth')->causedBy($user)->performedOn($user)->event($event)
                ->withProperties(array_merge([
                    'channel'     => 'mobile',
                    'ip'          => $request->ip(),
                    'user_agent'  => substr((string) $request->userAgent(), 0, 255),
                    'device_id'   => $request->input('device_id'),
                    'device_name' => $request->input('device_name'),
                ], $extra))
                ->log($description);
        } catch (\Throwable $e) {
            Log::error('Failed to record mobile auth activity: ' . $e->getMessage());
        }
    }

    private const GENERIC_FAIL = 'Username atau password salah';

    /**
     * Login aplikasi mobile. Urutan pengecekan sama dengan login web:
     * rate limit bertingkat → password → status karyawan Active → permission mobile.attendance
     * → password bukan default (username/NIP) → 2FA (kalau aktif/wajib) → device terdaftar → token.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username'    => 'required|string|max:50',
            'password'    => 'required|string|max:128',
            'device_id'   => 'required|string|max:255',
            'device_name' => 'nullable|string|max:100',
            'otp'         => 'nullable|string|max:20',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Data tidak valid', 'errors' => $validator->errors()], 422);
        }

        $username = strtolower(trim($request->username));
        $keys     = LoginThrottle::keys($username, $request->ip());

        if ($seconds = LoginThrottle::lockedFor($keys)) {
            Log::warning('Mobile login rate limit hit', ['username' => $username, 'ip' => $request->ip()]);
            return response()->json([
                'success'     => false,
                'message'     => 'Terlalu banyak percobaan login. Coba lagi dalam ' . max(1, (int) ceil($seconds / 60)) . ' menit.',
                'code'        => 'TOO_MANY_ATTEMPTS',
                'retry_after' => $seconds,
            ], 429);
        }

        $user = User::with('employee')->where('username', $username)->first();

        // Hash tetap dihitung walau user tidak ada, supaya waktu respons tidak membocorkan username
        $passwordOk = $user
            ? Hash::check($request->password, $user->password)
            : (Hash::check($request->password, Hash::make('timing-equaliser')) && false);

        if (!$passwordOk) {
            LoginThrottle::hit($keys);
            if ($user) $this->logAuth($user, 'login_failed', 'Percobaan login aplikasi mobile gagal: password salah', $request);
            return response()->json(['success' => false, 'message' => self::GENERIC_FAIL], 401);
        }

        $employee = $user->employee;
        $deny = function (string $reason, string $message, string $code) use ($user, $request, $keys) {
            // Ditolak setelah password benar tetap dihitung, supaya tidak jadi alat uji password
            LoginThrottle::hit($keys);
            $this->logAuth($user, 'login_blocked', 'Login aplikasi mobile ditolak: ' . $reason, $request);
            return response()->json(['success' => false, 'message' => $message, 'code' => $code], 403);
        };

        if (!$employee) {
            return $deny('akun belum terhubung ke data karyawan', 'Data karyawan tidak ditemukan, hubungi HR', 'NO_EMPLOYEE');
        }
        if ($employee->status !== 'Active') {
            return $deny("status karyawan {$employee->status}", 'Akun tidak aktif atau tidak memiliki akses, hubungi HR', 'INACTIVE_STATUS');
        }
        if (!$user->hasPermissionTo('mobile.attendance', 'web')) {
            return $deny('tidak punya permission mobile.attendance', 'Akun Anda tidak punya akses aplikasi absensi, hubungi HR', 'NO_MOBILE_ACCESS');
        }
        if (LoginSessionService::hasDefaultPassword($user, $request->password)) {
            return $deny('password masih default (username/NIP)', 'Password Anda masih default. Ganti password dulu lewat web HRX, lalu login lagi di aplikasi.', 'PASSWORD_CHANGE_REQUIRED');
        }

        // 2FA: wajib kalau sudah diaktifkan; akun yang diwajibkan tapi belum setup harus setup di web dulu
        if ($user->requiresTwoFactor() && !$user->hasTwoFactorEnabled()) {
            return $deny('wajib 2FA tapi belum setup', 'Akun Anda wajib mengaktifkan 2FA. Aktifkan dulu lewat web HRX, lalu login lagi.', 'TWO_FACTOR_SETUP_REQUIRED');
        }
        if ($user->hasTwoFactorEnabled()) {
            if (!$request->filled('otp')) {
                // Belum dihitung gagal: aplikasi baru akan meminta kode
                return response()->json(['success' => false, 'message' => 'Masukkan kode 2FA dari aplikasi Authenticator', 'code' => 'OTP_REQUIRED'], 401);
            }
            $otpKey = 'mobile-2fa:' . $user->id;
            if (RateLimiter::tooManyAttempts($otpKey, 10)) {
                return response()->json(['success' => false, 'message' => 'Terlalu banyak kode 2FA salah. Coba lagi dalam ' . max(1, (int) ceil(RateLimiter::availableIn($otpKey) / 60)) . ' menit.', 'code' => 'TOO_MANY_ATTEMPTS'], 429);
            }
            $method = TwoFactorVerifier::verify($user, $request->otp);
            if (!$method) {
                RateLimiter::hit($otpKey, 300);
                $this->logAuth($user, 'login_failed', 'Login aplikasi mobile gagal: kode 2FA salah', $request);
                return response()->json(['success' => false, 'message' => 'Kode 2FA tidak valid atau sudah dipakai', 'code' => 'OTP_INVALID'], 401);
            }
            RateLimiter::clear($otpKey);
        }

        $registeredDevice = RegisteredDevice::where('user_id', $user->id)->where('is_active', true)->first();
        if ($registeredDevice && !hash_equals((string) $registeredDevice->device_id, (string) $request->device_id)) {
            $this->logAuth($user, 'login_blocked', 'Login aplikasi mobile ditolak: device berbeda dari yang terdaftar', $request, [
                'registered_device' => $registeredDevice->device_name,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Akun ini sudah terdaftar di device lain. Hubungi HR untuk reset device.',
                'code'    => 'DEVICE_MISMATCH',
            ], 403);
        }

        LoginThrottle::clear($keys);

        if (!$registeredDevice) {
            RegisteredDevice::create([
                'user_id'       => $user->id,
                'device_id'     => $request->device_id,
                'device_name'   => $request->device_name,
                'is_active'     => true,
                'registered_at' => now(),
            ]);
        }

        // Satu device, satu token: token lama (dan push token-nya) diputus
        $user->tokens()->delete();
        PushToken::where('user_id', $user->id)->update(['is_active' => false]);

        $token = $user->createToken('mobile-app', ['mobile'])->plainTextToken;
        $this->logAuth($user, 'login', 'Login aplikasi mobile', $request, ['new_device' => !$registeredDevice, '2fa' => $user->hasTwoFactorEnabled()]);

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data'    => [
                'token' => $token,
                'user'  => [
                    'id'            => $user->id,
                    'username'      => $user->username,
                    'employee_name' => $employee->employee_name,
                    'email'         => $employee->email,
                ],
            ],
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan',
            ], 404);
        }
        $employee->load([
            'company:id,name',
            'position:id,name',
            'department:id,department_name',
            'store:id,name',
        ]);

       
      $photoUrl = $employee->photos
    ? URL::temporarySignedRoute('mobile.photo', now()->addHours(1), ['path' => base64_encode($employee->photos)])
    : null;

        return response()->json([
            'success' => true,
            'data' => [
                'employee_name' => $employee->employee_name,
                'employee_pengenal' => $employee->employee_pengenal,
                'status_employee' => $employee->status_employee,
                'status' => $employee->status,
                'join_date' => $employee->join_date,
                'telp_number' => $employee->telp_number,
                'email' => $employee->email,
                'photo_url' => $photoUrl,
                'grading' => $employee->grading->grading_name ?? null,
                'company' => $employee->company->name ?? null,
                'position' => $employee->position->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'is_primary' => (bool) $p->pivot->is_primary,
                ]),
                'department' => $employee->department->map(fn($d) => [
                    'id' => $d->id,
                    'name' => $d->department_name,
                    'is_primary' => (bool) $d->pivot->is_primary,
                ]),
                'stores' => $employee->store->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'is_primary' => (bool) $s->pivot->is_primary,
                ]),
            ],
        ]);
    }
    public function logout(Request $request)
    {
        $device = RegisteredDevice::where('user_id', $request->user()->id)->where('is_active', true)->first();
        $this->logAuth($request->user(), 'logout', 'Logout aplikasi mobile', $request, [
            'device_id'   => $device?->device_id ?? $request->input('device_id'),
            'device_name' => $device?->device_name ?? $request->input('device_name'),
        ]);
        $request->user()->currentAccessToken()?->delete();
        // Notifikasi tidak dikirim lagi ke device yang sudah logout
        PushToken::where('user_id', $request->user()->id)->update(['is_active' => false]);
        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }
    public function me(Request $request)
    {
        $user = $request->user();
        // Hanya data yang dibutuhkan aplikasi (dulu seluruh model User, termasuk daftar role)
        return response()->json([
            'success' => true,
            'data'    => [
                'id'            => $user->id,
                'username'      => $user->username,
                'employee_id'   => $user->employee_id,
                'employee_name' => $user->employee?->employee_name,
                'email'         => $user->employee?->email,
                'two_factor'    => $user->hasTwoFactorEnabled(),
                // Menu yang boleh ditampilkan aplikasi; server tetap memeriksa ulang di setiap endpoint
                'features'      => [
                    'employees'        => \App\Http\Controllers\Api\EmployeeDirectoryController::accessLevel($user) !== null,
                    'employees_access' => \App\Http\Controllers\Api\EmployeeDirectoryController::accessLevel($user),
                ],
            ],
        ]);
    }
   public function photo(Request $request, $path)
{
    // Verifikasi signature URL — mencegah akses sembarangan tanpa link yang sah
    if (!$request->hasValidSignature()) {
        abort(403, 'Link tidak valid atau sudah kedaluwarsa');
    }

    $decodedPath = base64_decode($path, true);
    // Hanya foto karyawan, bukan file lain di bucket
    if ($decodedPath === false || str_contains($decodedPath, '..') || !preg_match('/\.(jpe?g|png|webp)$/i', $decodedPath)) {
        abort(404);
    }

    if (!Storage::disk('s3')->exists($decodedPath)) {
        abort(404);
    }

    $content = Storage::disk('s3')->get($decodedPath);
    $mimeType = Storage::disk('s3')->mimeType($decodedPath);

    return response($content)
        ->header('Content-Type', $mimeType)
        ->header('X-Content-Type-Options', 'nosniff')
        ->header('Cache-Control', 'private, max-age=3600');
}
public function registerPushToken(Request $request)
{
    $validator = Validator::make($request->all(), [
        'token' => 'required|string|max:512',
        'platform' => 'nullable|in:android,ios',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Data tidak valid',
            'errors' => $validator->errors(),
        ], 422);
    }

    $user = $request->user();

    // Token push milik device, bukan akun: kalau dulu dipakai akun lain di device yang sama, putus dari akun itu
    PushToken::where('token', $request->token)->where('user_id', '!=', $user->id)->update(['is_active' => false]);

    // Upsert — update kalau sudah ada, insert kalau belum
    PushToken::updateOrCreate(
        ['user_id' => $user->id, 'token' => $request->token],
        [
            'platform' => $request->platform ?? 'android',
            'is_active' => true,
        ]
    );

    return response()->json([
        'success' => true,
        'message' => 'Push token berhasil didaftarkan',
    ]);
}
}
