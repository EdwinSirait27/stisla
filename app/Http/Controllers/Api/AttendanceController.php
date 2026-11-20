<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AttendanceController extends Controller
{

    /** Status absen sah dari aplikasi. Enum kolom: approved | pending | flagged (bukan "valid"). */
    private const STATUS_OK = 'approved';

    /** Berapa jam setelah jam pulang shift malam, check-out masih dihitung untuk shift kemarin. */
    private const NIGHT_SHIFT_GRACE_HOURS = 6;

    const EARTH_RADIUS = 6371000;

    /**
     * Tanggal kerja untuk absen sekarang.
     * Shift malam kemarin yang sudah check-in tapi belum check-out tetap memakai tanggal kemarin,
     * tapi hanya sampai jam pulang shift + jeda. Lewat dari itu (lupa check-out), absen baru
     * masuk ke tanggal hari ini supaya karyawan tetap bisa check-in.
     */
    private function resolveWorkDate(Employee $employee): string
    {
        $now       = now();
        $today     = $now->toDateString();
        $yesterday = $now->copy()->subDay()->toDateString();

        $yesterdayRoster = $employee->rosters()->with('shift')->whereDate('date', $yesterday)->first();
        $shift = $yesterdayRoster?->shift;

        if ($shift && $shift->end_time < $shift->start_time) {
            $shiftEnd = Carbon::parse($today . ' ' . $shift->end_time);
            if ($now->lte($shiftEnd->copy()->addHours(self::NIGHT_SHIFT_GRACE_HOURS))) {
                $logs = AttendanceLog::where('employee_id', $employee->id)->where('work_date', $yesterday)->pluck('type');
                if ($logs->contains('checkin') && !$logs->contains('checkout')) {
                    return $yesterday;
                }
            }
        }

        return $today;
    }

    public function checkin(Request $request)
    {
        return $this->record($request, 'checkin');
    }

    public function checkout(Request $request)
    {
        return $this->record($request, 'checkout');
    }

    /** Alur bersama check-in / check-out: validasi → cek urutan → mock → wajah → geofence → simpan. */
    private function record(Request $request, string $type)
    {
        set_time_limit(120);

        $validator = Validator::make($request->all(), [
            'latitude'         => 'required|numeric|between:-90,90',
            'longitude'        => 'required|numeric|between:-180,180',
            'photo'            => 'required|image|max:5120',
            'device_id'        => 'required|string|max:255',
            'is_mock_location' => 'required|in:0,1,true,false',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Data tidak valid', 'errors' => $validator->errors()], 422);
        }

        $employee = $request->user()->employee;
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Data karyawan tidak ditemukan'], 404);
        }
        if (!$employee->photos) {
            return response()->json(['success' => false, 'message' => 'Foto referensi karyawan belum tersedia, hubungi HR'], 422);
        }

        // Satu proses absen per karyawan pada satu waktu (tombol ditekan dua kali, jaringan retry)
        $lock = Cache::lock('attendance:' . $employee->id, 120);
        if (!$lock->get()) {
            return response()->json(['success' => false, 'message' => 'Absen sebelumnya masih diproses, tunggu sebentar', 'code' => 'IN_PROGRESS'], 429);
        }

        try {
            $workDate = $this->resolveWorkDate($employee);
            $existing = AttendanceLog::where('employee_id', $employee->id)->where('work_date', $workDate)->get(['type', 'logged_at']);
            $checkinLog = $existing->firstWhere('type', 'checkin');

            if ($type === 'checkin' && $checkinLog) {
                return response()->json(['success' => false, 'message' => 'Anda sudah melakukan check-in untuk shift ini', 'code' => 'ALREADY_CHECKED_IN'], 422);
            }
            if ($type === 'checkout' && !$checkinLog) {
                return response()->json(['success' => false, 'message' => 'Anda belum melakukan check-in untuk shift ini', 'code' => 'NO_CHECKIN_FOUND'], 422);
            }
            if ($type === 'checkout' && $existing->contains('type', 'checkout')) {
                return response()->json(['success' => false, 'message' => 'Anda sudah melakukan check-out untuk shift ini', 'code' => 'ALREADY_CHECKED_OUT'], 422);
            }

            if (filter_var($request->is_mock_location, FILTER_VALIDATE_BOOLEAN)) {
                Log::warning('Attendance rejected: mock location', ['employee_id' => $employee->id, 'type' => $type, 'device_id' => $request->device_id]);
                return response()->json(['success' => false, 'message' => 'Absen ditolak: terdeteksi fake GPS/mock location', 'code' => 'ATTENDANCE_REJECTED'], 422);
            }

            // Geofence dicek sebelum wajah: lebih murah, dan tidak membebani DeepFace untuk absen yang pasti ditolak
            $distance = null;
            $storeId  = null;
            if ($employee->attendance_type === 'store_bound') {
                $store = $employee->store()->wherePivot('is_primary', true)->first();
                if (!$store || $store->latitude === null || $store->longitude === null || !$store->geofence_radius) {
                    return response()->json(['success' => false, 'message' => 'Lokasi store Anda belum diset, hubungi admin', 'code' => 'STORE_LOCATION_MISSING'], 422);
                }
                $storeId  = $store->id;
                $distance = $this->calculateDistance($request->latitude, $request->longitude, $store->latitude, $store->longitude);
                if ($distance > $store->geofence_radius) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Absen ditolak: Anda berada ' . number_format($distance, 0, ',', '.') . "m dari lokasi, di luar radius yang diizinkan ({$store->geofence_radius}m)",
                        'code'    => 'OUTSIDE_GEOFENCE',
                    ], 422);
                }
            }

            $face = $this->verifyFace($request->file('photo'), $employee->photos);
            if (!$face['success']) {
                return response()->json(['success' => false, 'message' => $face['message'], 'code' => $face['code'] ?? 'FACE_CHECK_FAILED'], 422);
            }
            if (!$face['is_real']) {
                return response()->json(['success' => false, 'message' => 'Terdeteksi kemungkinan spoofing (foto dari foto/layar), absen ditolak', 'code' => 'SPOOFING_DETECTED'], 422);
            }
            if (!$face['is_verified']) {
                return response()->json(['success' => false, 'message' => 'Wajah tidak cocok dengan data karyawan, absen ditolak', 'code' => 'FACE_MISMATCH'], 422);
            }

            $photoPath = $request->file('photo')->store('attendance/' . now()->format('Y/m'), 's3');

            try {
                $log = AttendanceLog::create([
                    'employee_id'         => $employee->id,
                    'store_id'            => $storeId,
                    'work_date'           => $workDate,
                    'type'                => $type,
                    'latitude'            => $request->latitude,
                    'longitude'           => $request->longitude,
                    'distance_from_store' => $distance,
                    'is_within_geofence'  => true,
                    'is_mock_location'    => false,
                    'photo_path'          => $photoPath,
                    'liveness_score'      => isset($face['distance']) ? round((float) $face['distance'], 2) : null,
                    'liveness_passed'     => true,
                    'device_id'           => $request->device_id,
                    'status'              => self::STATUS_OK,
                    'flag_reason'         => null,
                    'logged_at'           => now(),
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                Storage::disk('s3')->delete($photoPath);
                if ($e->getCode() === '23000') {
                    // unique (employee_id, work_date, type): request ganda yang lolos bersamaan
                    return response()->json([
                        'success' => false,
                        'message' => $type === 'checkin' ? 'Anda sudah melakukan check-in untuk shift ini' : 'Anda sudah melakukan check-out untuk shift ini',
                        'code'    => $type === 'checkin' ? 'ALREADY_CHECKED_IN' : 'ALREADY_CHECKED_OUT',
                    ], 422);
                }
                Log::error('Attendance save failed', ['employee_id' => $employee->id, 'type' => $type, 'error' => $e->getMessage()]);
                return response()->json(['success' => false, 'message' => 'Gagal menyimpan absen, coba lagi'], 500);
            }
        } finally {
            $lock->release();
        }

        $data = ['id' => $log->id, 'status' => $log->status, 'logged_at' => $log->logged_at, 'work_date' => $log->work_date];
        if ($type === 'checkout') {
            $data['checkin_at']            = $checkinLog->logged_at;
            $data['work_duration_minutes'] = (int) $checkinLog->logged_at->diffInMinutes($log->logged_at);
        }

        return response()->json([
            'success' => true,
            'message' => $type === 'checkin' ? 'Absen berhasil' : 'Check-out berhasil',
            'data'    => $data,
        ]);
    }

    // /**
    private function verifyFace($attendancePhoto, $referencePhotoPath)
    {
        $startTotal = microtime(true);

        try {
            $startCache = microtime(true);
            $cacheKey = 'reference_photo_' . md5($referencePhotoPath);
            $referenceContent = Cache::remember($cacheKey, now()->addHours(6), function () use ($referencePhotoPath) {
                return Storage::disk('s3')->get($referencePhotoPath);
            });
            Log::info('Waktu ambil foto referensi: ' . round(microtime(true) - $startCache, 2) . 's');

            $startHttp = microtime(true);
            // Di bawah batas waktu request (120 detik) supaya error-nya tertangani, bukan fatal
            $response = Http::connectTimeout(10)->timeout(75)
                ->attach('photo', file_get_contents($attendancePhoto->getRealPath()), 'attendance.jpg')
                ->attach('reference_photo', $referenceContent, 'reference.jpg')
                ->post(config('services.deepface.url') . '/verify');
            Log::info('Waktu request ke DeepFace: ' . round(microtime(true) - $startHttp, 2) . 's');
            Log::info('Waktu total verifyFace: ' . round(microtime(true) - $startTotal, 2) . 's');

            $result = $response->json();

            Log::info('DeepFace response', [
                'status' => $response->status(),
                'body'   => $result,
            ]);

            // Error input (400/422) — masalah dari foto, bukan dari layanan
            if ($response->status() === 400 || $response->status() === 422) {
                $errorMessage = $result['message'] ?? $result['error'] ?? null;

                $userMessage = match (true) {
                    str_contains($errorMessage ?? '', 'Face could not be detected')
                    => 'Wajah tidak terdeteksi, pastikan wajah terlihat jelas dan pencahayaan cukup',
                    str_contains($errorMessage ?? '', 'Multiple faces')
                    => 'Terdeteksi lebih dari satu wajah, pastikan hanya ada satu wajah',
                    str_contains($errorMessage ?? '', 'blurry') || str_contains($errorMessage ?? '', 'Low quality')
                    => 'Foto terlalu buram atau gelap, coba lagi dengan pencahayaan lebih baik',
                    default
                    => 'Foto tidak valid, pastikan wajah terlihat jelas dan coba lagi',
                };

                Log::warning('DeepFace input error', [
                    'raw_message' => $errorMessage,
                    'status'      => $response->status(),
                ]);

                return [
                    'success' => false,
                    'message' => $userMessage,
                    'code'    => 'NO_FACE_DETECTED',
                ];
            }

            // Error server (500, dst) — layanan bermasalah
            if (!$response->successful()) {
                Log::error('DeepFace server error', [
                    'status' => $response->status(),
                    'body'   => $result,
                ]);

                return [
                    'success' => false,
                    'message' => 'Layanan verifikasi wajah sedang bermasalah, coba lagi dalam beberapa saat',
                ];
            }

            return [
                'success'     => true,
                'is_real'     => $result['is_real']     ?? false,
                'is_verified' => $result['is_verified'] ?? false,
                'distance'    => $result['distance']    ?? null,
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('DeepFace connection timeout: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Layanan verifikasi wajah tidak merespons, coba lagi dalam beberapa saat',
            ];
        } catch (\Exception $e) {
            Log::error('DeepFace verification error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke layanan verifikasi wajah',
            ];
        }
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dLon / 2) ** 2;
        $c = 2 * asin(sqrt($a));

        return round(self::EARTH_RADIUS * $c, 2);
    }


    public function history(Request $request)
    {
        $employee = $request->user()->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'end_date'   => 'nullable|date|date_format:Y-m-d|after_or_equal:start_date',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Filter tanggal tidak valid',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Default: 30 hari terakhir kalau tidak ada filter
        $startDate = $request->start_date ?? now()->subDays(30)->toDateString();
        $endDate   = $request->end_date   ?? now()->toDateString();
        if (Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) > 93) {
            return response()->json(['success' => false, 'message' => 'Rentang tanggal maksimal 3 bulan'], 422);
        }

        // Ambil semua log dalam range, group by work_date
        $logs = AttendanceLog::where('employee_id', $employee->id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->orderBy('work_date', 'desc')
            ->orderBy('logged_at', 'asc')
            ->get();

        // Group by work_date → per hari ada checkin + checkout
        $grouped = $logs->groupBy(fn($l) => Carbon::parse($l->work_date)->toDateString())->map(function ($dayLogs, $workDate) {
            $checkin  = $dayLogs->where('type', 'checkin')->first();
            $checkout = $dayLogs->where('type', 'checkout')->first();

            $workDurationMinutes = null;
            if ($checkin && $checkout) {
                $workDurationMinutes = $checkin->logged_at->diffInMinutes($checkout->logged_at);
            }

            return [
                'work_date'             => $workDate,
                'day_label'             => Carbon::parse($workDate)->locale('id')->translatedFormat('l, d F Y'),
                'checkin_time'          => $checkin?->logged_at?->format('H:i'),
                'checkout_time'         => $checkout?->logged_at?->format('H:i'),
                'work_duration_minutes' => $workDurationMinutes !== null ? (int) $workDurationMinutes : null,
                'status'                => $checkin?->status ?? $checkout?->status,
                'checkin_status'        => $checkin ? [
                    'is_within_geofence' => $checkin->is_within_geofence,
                    'flag_reason'        => $checkin->flag_reason,
                ] : null,
                'checkout_status'       => $checkout ? [
                    'is_within_geofence' => $checkout->is_within_geofence,
                    'flag_reason'        => $checkout->flag_reason,
                ] : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'period_start' => $startDate,
                'period_end'   => $endDate,
                'total_days'   => $grouped->count(),
                'history'      => $grouped,
            ],
        ]);
    }
    public function today(Request $request)
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan',
            ], 404);
        }

        $workDate = $this->resolveWorkDate($employee);

        $todayLog = AttendanceLog::where('employee_id', $employee->id)
            ->where('work_date', $workDate)
            ->orderBy('logged_at')
            ->get();

        $checkin  = $todayLog->where('type', 'checkin')->first();
        $checkout = $todayLog->where('type', 'checkout')->first();

        $nextAction = !$checkin ? 'checkin' : (!$checkout ? 'checkout' : 'done');

        return response()->json([
            'success' => true,
            'data' => [
                'next_action'   => $nextAction,
                'work_date'     => $workDate,
                'checkin_time'  => $checkin?->logged_at?->format('H:i'),
                'checkin_at'    => $checkin?->logged_at,
                'checkout_time' => $checkout?->logged_at?->format('H:i'),
                'checkout_at'   => $checkout?->logged_at,
            ],
        ]);
    }

    public function myRoster(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date'   => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Filter tanggal tidak valid', 'errors' => $validator->errors()], 422);
        }

        // Default: periode payroll yang sedang berjalan (26 sampai 25)
        $today = today();
        $first = $today->copy()->startOfMonth();
        [$defaultStartDate, $defaultEndDate] = $today->day >= 26
            ? [$today->copy()->day(26)->toDateString(), $first->copy()->addMonthNoOverflow()->day(25)->toDateString()]
            : [$first->copy()->subMonthNoOverflow()->day(26)->toDateString(), $today->copy()->day(25)->toDateString()];

        $startDate = $request->start_date ?? $defaultStartDate;
        $endDate   = $request->end_date ?? $defaultEndDate;
        if (Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) > 93) {
            return response()->json(['success' => false, 'message' => 'Rentang tanggal maksimal 3 bulan'], 422);
        }

        $rosters = $employee->rosters()
            ->whereBetween('date', [$startDate, $endDate])
            ->with('shift:id,shift_name,start_time,end_time')
            ->orderBy('date')
            ->get();

        $data = $rosters->map(function ($roster) {
            $date = \Carbon\Carbon::parse($roster->date);

            return [
                'date' => $date->toDateString(),
                'day_label' => $date->locale('id')->translatedFormat('l'),
                'day_type' => $roster->day_type,
                'shift_name' => $roster->shift->shift_name ?? null,
                'start_time' => $roster->shift->start_time ?? null,
                'end_time' => $roster->shift->end_time ?? null,
                'notes' => $roster->notes,
                'is_today' => $date->isToday(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'period_start' => $startDate,
                'period_end' => $endDate,
                'rosters' => $data,
            ],
        ]);
    }
}
