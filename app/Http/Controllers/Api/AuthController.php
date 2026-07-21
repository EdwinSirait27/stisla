<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RegisteredDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;


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
    public function login(Request $request)
{
    Log::info('Login attempt - IP: ' . $request->ip() . ' | Username: ' . $request->username);
    $validator = Validator::make($request->all(), [
        'username' => 'required|string|max:255',
        'password' => 'required|string|max:255',
        'device_id' => 'required|string|max:255',
        'device_name' => 'nullable|string|max:100',
    ]);
    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Data tidak valid',
            'errors' => $validator->errors(),
        ], 422);
    }
    // Rate limiting — maksimal 5 percobaan per menit per kombinasi username+IP
    $throttleKey = Str::lower($request->username) . '|' . $request->ip();

    if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
        $seconds = RateLimiter::availableIn($throttleKey);

        return response()->json([
            'success' => false,
            'message' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            'code' => 'TOO_MANY_ATTEMPTS',
        ], 429);
    }
    $user = User::where('username', $request->username)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        RateLimiter::hit($throttleKey, 60); // catat percobaan gagal, kunci 60 detik

        return response()->json([
            'success' => false,
            'message' => 'Username atau password salah',
        ], 401);
    }

    RateLimiter::clear($throttleKey); // reset counter kalau berhasil

    $employee = $user->employee;

    if (!$employee) {
        return response()->json([
            'success' => false,
            'message' => 'Data karyawan tidak ditemukan, hubungi HR',
        ], 403);
    }

    // Filter status karyawan yang diizinkan login
    $allowedStatuses = ['Active', 'Pending', 'Mutation', 'On Leave'];

    if (!in_array($employee->status, $allowedStatuses)) {
        return response()->json([
            'success' => false,
            'message' => 'Akun tidak aktif atau tidak memiliki akses, hubungi HR',
            'code' => 'INACTIVE_STATUS',
        ], 403);
    }

    $registeredDevice = RegisteredDevice::where('user_id', $user->id)
        ->where('is_active', true)
        ->first();

    if ($registeredDevice && $registeredDevice->device_id !== $request->device_id) {
        return response()->json([
            'success' => false,
            'message' => 'Akun ini sudah terdaftar di device lain. Hubungi HR untuk reset device.',
            'code' => 'DEVICE_MISMATCH',
        ], 403);
    }

    if (!$registeredDevice) {
        RegisteredDevice::create([
            'user_id' => $user->id,
            'device_id' => $request->device_id,
            'device_name' => $request->device_name,
            'is_active' => true,
            'registered_at' => now(),
        ]);
    }

    $user->tokens()->delete();

    $token = $user->createToken('mobile-app')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Login berhasil',
        'data' => [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'employee_name' => $employee->employee_name,
                'email' => $employee->email,
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
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }
   public function photo(Request $request, $path)
{
    // Verifikasi signature URL — mencegah akses sembarangan tanpa link yang sah
    if (!$request->hasValidSignature()) {
        abort(403, 'Link tidak valid atau sudah kedaluwarsa');
    }

    $decodedPath = base64_decode($path);

    if (!Storage::disk('s3')->exists($decodedPath)) {
        abort(404);
    }

    $content = Storage::disk('s3')->get($decodedPath);
    $mimeType = Storage::disk('s3')->mimeType($decodedPath);

    return response($content)->header('Content-Type', $mimeType);
}
}
