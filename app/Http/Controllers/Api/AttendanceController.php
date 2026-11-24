<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\VerifiesMobileAttendance;
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
    use VerifiesMobileAttendance;

    /** Status absen sah dari aplikasi. Enum kolom: approved | pending | flagged (bukan "valid"). */
    private const STATUS_OK = 'approved';

    /** Berapa jam setelah jam pulang shift malam, check-out masih dihitung untuk shift kemarin. */
    private const NIGHT_SHIFT_GRACE_HOURS = 6;

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

    /** Shift kerja di tanggal kerja (null kalau tidak ada roster Work dengan shift). */
    private function shiftFor(Employee $employee, string $workDate): ?\App\Models\Shifts
    {
        $roster = $employee->rosters()->with('shift')->whereDate('date', $workDate)->first();

        return $roster && $roster->day_type === 'Work' ? $roster->shift : null;
    }

    /**
     * Sesi absen saat ini. Shift biasa selalu sesi 1.
     * Split shift: sesi 2 kalau blok 1 sudah check-out, atau jam sekarang sudah lewat titik tengah jeda
     * (blok 1 yang terlewat atau lupa check-out tidak menghalangi absen blok 2).
     */
    private function resolveSession(?\App\Models\Shifts $shift, string $workDate, $logs): int
    {
        if (!$shift || !$shift->isSplit()) return 1;

        if ($logs->contains(fn($l) => (int) $l->session === 1 && $l->type === 'checkout')) return 2;

        $boundary = Carbon::parse($workDate . ' ' . $shift->splitBoundary());

        return now()->gte($boundary) ? 2 : 1;
    }

    /** Ringkasan per sesi untuk respons API. */
    private function sessionSummary(?\App\Models\Shifts $shift, $logs): array
    {
        $blocks = $shift ? $shift->blocks() : [[null, null]];

        return collect($blocks)->map(function ($block, $i) use ($logs) {
            $session  = $i + 1;
            $checkin  = $logs->first(fn($l) => (int) $l->session === $session && $l->type === 'checkin');
            $checkout = $logs->first(fn($l) => (int) $l->session === $session && $l->type === 'checkout');

            return [
                'session'       => $session,
                'start_time'    => $block[0] ? substr($block[0], 0, 5) : null,
                'end_time'      => $block[1] ? substr($block[1], 0, 5) : null,
                'checkin_time'  => $checkin?->logged_at?->format('H:i'),
                'checkin_at'    => $checkin?->logged_at,
                'checkout_time' => $checkout?->logged_at?->format('H:i'),
                'checkout_at'   => $checkout?->logged_at,
            ];
        })->values()->all();
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
            'photo'            => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
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
        if ($employee->isAttendanceExempt()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak wajib absen, absen lewat aplikasi tidak diperlukan', 'code' => 'ATTENDANCE_EXEMPT'], 403);
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
            $allLogs  = AttendanceLog::where('employee_id', $employee->id)->where('work_date', $workDate)->get(['type', 'session', 'logged_at']);
            $shift    = $this->shiftFor($employee, $workDate);
            $session  = $this->resolveSession($shift, $workDate, $allLogs);
            $isSplit  = $shift?->isSplit() ?? false;
            $existing = $allLogs->filter(fn($l) => (int) $l->session === $session);
            $checkinLog = $existing->firstWhere('type', 'checkin');
            $target   = $isSplit ? "blok {$session} shift ini" : 'shift ini';

            if ($type === 'checkin' && $checkinLog) {
                return response()->json(['success' => false, 'message' => "Anda sudah melakukan check-in untuk {$target}", 'code' => 'ALREADY_CHECKED_IN', 'session' => $session], 422);
            }
            if ($type === 'checkout' && !$checkinLog) {
                return response()->json(['success' => false, 'message' => "Anda belum melakukan check-in untuk {$target}", 'code' => 'NO_CHECKIN_FOUND', 'session' => $session], 422);
            }
            if ($type === 'checkout' && $existing->contains('type', 'checkout')) {
                return response()->json(['success' => false, 'message' => "Anda sudah melakukan check-out untuk {$target}", 'code' => 'ALREADY_CHECKED_OUT', 'session' => $session], 422);
            }

            if (filter_var($request->is_mock_location, FILTER_VALIDATE_BOOLEAN)) {
                Log::warning('Attendance rejected: mock location', ['employee_id' => $employee->id, 'type' => $type, 'device_id' => $request->device_id]);
                return response()->json(['success' => false, 'message' => 'Absen ditolak: terdeteksi fake GPS/mock location', 'code' => 'ATTENDANCE_REJECTED'], 422);
            }

            // Geofence dicek sebelum wajah: lebih murah, dan tidak membebani DeepFace untuk absen yang pasti ditolak
            $location = $this->resolveLocation($employee, (float) $request->latitude, (float) $request->longitude);
            if (isset($location['error'])) {
                return response()->json(['success' => false, 'message' => $location['error'], 'code' => $location['code']], 422);
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

            // Selfie dikompres & dibersihkan sebelum disimpan (yang dikirim ke DeepFace juga sudah dikompres, lihat selfieForVerification)
            $photoPath = app(\App\Services\UploadStorage::class)->put(
                $request->file('photo'), 'attendance/' . now()->format('Y/m'), 'selfie-' . $type, \App\Services\UploadStorage::IMAGES, 's3', false, 'photo'
            );

            try {
                $log = AttendanceLog::create([
                    'employee_id'         => $employee->id,
                    'store_id'            => $location['store_id'],
                    'work_date'           => $workDate,
                    'type'                => $type,
                    'session'             => $session,
                    'attendance_mode'     => $employee->attendance_type ?: Employee::ATTENDANCE_STORE_BOUND,
                    'latitude'            => $request->latitude,
                    'longitude'           => $request->longitude,
                    'distance_from_store' => $location['distance'],
                    'is_within_geofence'  => $location['within'],
                    'is_offsite'          => $location['offsite'],
                    'is_mock_location'    => false,
                    'photo_path'          => $photoPath,
                    'liveness_score'      => isset($face['distance']) ? round((float) $face['distance'], 2) : null,
                    'liveness_passed'     => true,
                    'device_id'           => $request->device_id,
                    'status'              => self::STATUS_OK,
                    'flag_reason'         => $location['note'],
                    'logged_at'           => now(),
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                Storage::disk('s3')->delete($photoPath);
                if ($e->getCode() === '23000') {
                    // unique (employee_id, work_date, type, session): request ganda yang lolos bersamaan
                    return response()->json([
                        'success' => false,
                        'message' => $type === 'checkin' ? "Anda sudah melakukan check-in untuk {$target}" : "Anda sudah melakukan check-out untuk {$target}",
                        'code'    => $type === 'checkin' ? 'ALREADY_CHECKED_IN' : 'ALREADY_CHECKED_OUT',
                    ], 422);
                }
                Log::error('Attendance save failed', ['employee_id' => $employee->id, 'type' => $type, 'error' => $e->getMessage()]);
                return response()->json(['success' => false, 'message' => 'Gagal menyimpan absen, coba lagi'], 500);
            }
        } finally {
            $lock->release();
        }

        $data = [
            'id'         => $log->id,
            'status'     => $log->status,
            'logged_at'  => $log->logged_at,
            'work_date'  => $log->work_date,
            'is_offsite' => $log->is_offsite,
            'store_name' => $location['store_name'],
            'session'        => $session,
            'total_sessions' => $isSplit ? 2 : 1,
        ];
        if ($type === 'checkout') {
            $data['checkin_at']            = $checkinLog->logged_at;
            $data['work_duration_minutes'] = (int) $checkinLog->logged_at->diffInMinutes($log->logged_at);
        }

        return response()->json([
            'success' => true,
            'message' => ($type === 'checkin' ? 'Absen berhasil' : 'Check-out berhasil') . ($isSplit ? " (blok {$session})" : ''),
            'data'    => $data,
        ]);
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
            // Split shift punya dua sesi: check-in pertama dan check-out terakhir mewakili hari itu,
            // durasi dijumlah per sesi (jeda tidak dihitung).
            $checkin  = $dayLogs->where('type', 'checkin')->sortBy('session')->first();
            $checkout = $dayLogs->where('type', 'checkout')->sortByDesc('session')->first();

            $workDurationMinutes = null;
            $sessions = $dayLogs->groupBy(fn($l) => (int) $l->session)->sortKeys()->map(function ($sLogs, $session) use (&$workDurationMinutes) {
                $in  = $sLogs->firstWhere('type', 'checkin');
                $out = $sLogs->firstWhere('type', 'checkout');
                if ($in && $out) {
                    $workDurationMinutes = ($workDurationMinutes ?? 0) + $in->logged_at->diffInMinutes($out->logged_at);
                }

                return [
                    'session'       => $session,
                    'checkin_time'  => $in?->logged_at?->format('H:i'),
                    'checkout_time' => $out?->logged_at?->format('H:i'),
                ];
            })->values();

            return [
                'work_date'             => $workDate,
                'day_label'             => Carbon::parse($workDate)->locale('id')->translatedFormat('l, d F Y'),
                'checkin_time'          => $checkin?->logged_at?->format('H:i'),
                'checkout_time'         => $checkout?->logged_at?->format('H:i'),
                'work_duration_minutes' => $workDurationMinutes !== null ? (int) $workDurationMinutes : null,
                'is_split'              => $sessions->count() > 1,
                'sessions'              => $sessions,
                'status'                => $checkin?->status ?? $checkout?->status,
                'checkin_status'        => $checkin ? [
                    'is_within_geofence' => $checkin->is_within_geofence,
                    'is_offsite'         => $checkin->is_offsite,
                    'flag_reason'        => $checkin->flag_reason,
                ] : null,
                'checkout_status'       => $checkout ? [
                    'is_within_geofence' => $checkout->is_within_geofence,
                    'is_offsite'         => $checkout->is_offsite,
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

        if ($employee->isAttendanceExempt()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'next_action'     => 'exempt',
                    'attendance_type' => $employee->attendance_type,
                    'work_date'       => today()->toDateString(),
                    'checkin_time'    => null,
                    'checkin_at'      => null,
                    'checkout_time'   => null,
                    'checkout_at'     => null,
                ],
            ]);
        }

        $workDate = $this->resolveWorkDate($employee);

        $todayLog = AttendanceLog::where('employee_id', $employee->id)
            ->where('work_date', $workDate)
            ->orderBy('logged_at')
            ->get();

        $shift   = $this->shiftFor($employee, $workDate);
        $isSplit = $shift?->isSplit() ?? false;
        $session = $this->resolveSession($shift, $workDate, $todayLog);

        // checkin_time / checkout_time mengikuti sesi yang sedang berjalan, supaya aplikasi lama tetap konsisten
        $sessionLogs = $todayLog->filter(fn($l) => (int) $l->session === $session);
        $checkin  = $sessionLogs->where('type', 'checkin')->first();
        $checkout = $sessionLogs->where('type', 'checkout')->first();

        $nextAction = !$checkin ? 'checkin' : (!$checkout ? 'checkout' : 'done');

        return response()->json([
            'success' => true,
            'data' => [
                'next_action'     => $nextAction,
                'attendance_type' => $employee->attendance_type,
                'work_date'       => $workDate,
                'checkin_time'  => $checkin?->logged_at?->format('H:i'),
                'checkin_at'    => $checkin?->logged_at,
                'checkout_time' => $checkout?->logged_at?->format('H:i'),
                'checkout_at'   => $checkout?->logged_at,
                // Split shift
                'is_split'       => $isSplit,
                'session'        => $session,
                'total_sessions' => $isSplit ? 2 : 1,
                'shift_name'     => $shift?->shift_name,
                'shift_time'     => $shift?->time_label,
                'sessions'       => $this->sessionSummary($shift, $todayLog),
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
            ->with('shift:id,shift_name,start_time,end_time,is_split,break_start,break_end')
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
                'is_split' => (bool) $roster->shift?->isSplit(),
                'break_start' => $roster->shift?->isSplit() ? $roster->shift->break_start : null,
                'break_end' => $roster->shift?->isSplit() ? $roster->shift->break_end : null,
                'time_label' => $roster->shift?->time_label,
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
