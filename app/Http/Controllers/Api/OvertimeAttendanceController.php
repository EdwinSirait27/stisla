<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\VerifiesMobileAttendance;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\OvertimeAttendanceLog;
use App\Models\Toilbalances;
use App\Services\ImageCompressor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Absensi lembur dari aplikasi mobile. Alurnya sama dengan absensi biasa (mock GPS → wajah → simpan),
 * tapi hanya bisa dilakukan kalau karyawan punya lembur yang disetujui dan saldonya tercatat di
 * Toilbalances (status selain cancelled).
 *
 * Lembur tidak terikat lokasi: semua karyawan boleh absen lembur dari mana saja, apa pun
 * attendance_type-nya. Store terdekat dan jaraknya tetap dicatat sebagai info untuk HR
 * (is_offsite = di luar radius semua store yang ditugaskan), tapi tidak pernah menolak absen.
 */
class OvertimeAttendanceController extends Controller
{
    use VerifiesMobileAttendance;

    private const STATUS_OK = 'approved';

    /** Lembur selalu bebas lokasi, tidak mengikuti attendance_type karyawan. */
    private const LOCATION_MODE = Employee::ATTENDANCE_LOCATION_FLEXIBLE;

    /**
     * Saldo lembur (Toilbalances) yang lemburnya disetujui dan bertanggal kemarin s/d besok,
     * lengkap dengan log absensi lemburnya.
     * - Kemarin: lembur lewat tengah malam masih bisa check-out.
     * - Besok  : lembur yang mulai dini hari (mis. 00:30) sudah bisa check-in sejak 23:30.
     *            Hanya ikut kalau jendela check-in-nya sudah terbuka, supaya lembur besok
     *            tidak tampil sebagai lembur hari ini.
     */
    private function candidateBalances(Employee $employee): Collection
    {
        $today     = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $tomorrow  = now()->addDay()->toDateString();

        $balances = Toilbalances::with('overtimeSubmission')
            ->where('employee_id', $employee->id)
            ->where('status', '!=', 'cancelled')
            ->whereHas('overtimeSubmission', fn($q) => $q->where('status', 'Approved')->whereBetween('date', [$yesterday, $tomorrow]))
            ->get();

        $logs = OvertimeAttendanceLog::whereIn('overtime_submission_id', $balances->pluck('overtime_submission_id'))
            ->get(['overtime_submission_id', 'type', 'logged_at', 'status'])
            ->groupBy('overtime_submission_id');

        return $balances->map(function ($b) use ($logs) {
            [$start, $end] = OvertimeAttendanceLog::overtimeWindow($b->overtimeSubmission);
            $own = $logs->get($b->overtime_submission_id, collect());
            return (object) [
                'balance'  => $b,
                'ot'       => $b->overtimeSubmission,
                'start'    => $start,
                'end'      => $end,
                'checkin'  => $own->firstWhere('type', 'checkin'),
                'checkout' => $own->firstWhere('type', 'checkout'),
                'deadline' => OvertimeAttendanceLog::checkoutDeadline($b->overtimeSubmission),
            ];
        })
            ->reject(fn($c) => Carbon::parse($c->ot->date)->toDateString() > $today
                && now()->lt($c->start->copy()->subMinutes(OvertimeAttendanceLog::checkinEarlyMinutes())))
            ->sortBy(fn($c) => $c->start->timestamp)->values();
    }

    /** Pilih lembur yang sedang bisa di-check-in / di-check-out sekarang. */
    private function pickOvertime(Collection $candidates, string $type): ?object
    {
        $now = now();
        if ($type === 'checkin') {
            return $candidates->first(fn($c) => !$c->checkin
                && $now->gte($c->start->copy()->subMinutes(OvertimeAttendanceLog::checkinEarlyMinutes()))
                && $now->lte($c->end));
        }
        // Check-out tidak dibatasi sekian jam setelah selesai; jam selesai dicek terpisah (OVERTIME_TOO_SHORT)
        return $candidates->first(fn($c) => $c->checkin && !$c->checkout && $now->lte($c->deadline));
    }

    private function overtimePayload(object $c): array
    {
        return [
            'overtime_submission_id' => $c->ot->id,
            'date'                   => Carbon::parse($c->ot->date)->toDateString(),
            'start_at'               => $c->start->toIso8601String(),
            'end_at'                 => $c->end->toIso8601String(),
            'start_time'             => $c->start->format('H:i'),
            'end_time'               => $c->end->format('H:i'),
            'total_hours'            => (float) $c->ot->total_hours,
            'compensation_type'      => $c->ot->compensation_type,
            'reason'                 => $c->ot->reason,
        ];
    }

    public function checkin(Request $request)
    {
        return $this->record($request, 'checkin');
    }

    public function checkout(Request $request)
    {
        return $this->record($request, 'checkout');
    }

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
        if (!$employee->photos) {
            return response()->json(['success' => false, 'message' => 'Foto referensi karyawan belum tersedia, hubungi HR'], 422);
        }

        // Kunci yang sama dengan absensi biasa: satu proses absen per karyawan pada satu waktu
        $lock = Cache::lock('attendance:' . $employee->id, 120);
        if (!$lock->get()) {
            return response()->json(['success' => false, 'message' => 'Absen sebelumnya masih diproses, tunggu sebentar', 'code' => 'IN_PROGRESS'], 429);
        }

        try {
            $candidates = $this->candidateBalances($employee);
            if ($candidates->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Anda tidak punya lembur yang disetujui untuk hari ini', 'code' => 'NO_OVERTIME'], 422);
            }

            $c = $this->pickOvertime($candidates, $type);
            if (!$c) {
                if ($type === 'checkout') {
                    $open = $candidates->first(fn($x) => $x->checkin && !$x->checkout);
                    return $open
                        ? response()->json(['success' => false, 'message' => 'Batas check-out lembur sudah lewat, hubungi atasan untuk entri manual', 'code' => 'OUTSIDE_OVERTIME_WINDOW'], 422)
                        // Masih ada lembur yang belum check-in → itu yang relevan, walau lembur lain sudah selesai
                        : ($candidates->contains(fn($x) => !$x->checkin)
                            ? response()->json(['success' => false, 'message' => 'Anda belum check-in lembur', 'code' => 'NO_CHECKIN_FOUND'], 422)
                            : response()->json(['success' => false, 'message' => 'Anda sudah check-out lembur', 'code' => 'ALREADY_CHECKED_OUT'], 422));
                }
                if ($candidates->every(fn($x) => $x->checkin)) {
                    return response()->json(['success' => false, 'message' => 'Anda sudah check-in lembur', 'code' => 'ALREADY_CHECKED_IN'], 422);
                }
                $next = $candidates->first(fn($x) => !$x->checkin && now()->lt($x->start));
                return response()->json([
                    'success' => false,
                    'message' => $next
                        ? 'Check-in lembur baru bisa mulai jam ' . $next->start->copy()->subMinutes(OvertimeAttendanceLog::checkinEarlyMinutes())->format('H:i') . ' (lembur mulai ' . $next->start->format('H:i') . ')'
                        : 'Waktu lembur Anda sudah berakhir, check-in tidak bisa dilakukan',
                    'code' => 'OUTSIDE_OVERTIME_WINDOW',
                ], 422);
            }

            // Check-out baru boleh setelah jam selesai lembur sesuai jadwal (lewat dari itu tetap boleh)
            if ($type === 'checkout' && now()->lt($c->end)) {
                return response()->json([
                    'success'         => false,
                    'message'         => 'Lembur Anda dijadwalkan selesai jam ' . $c->end->format('H:i') . '. Check-out baru bisa setelah jam itu.',
                    'code'            => 'OVERTIME_TOO_SHORT',
                    'can_checkout_at' => $c->end->toIso8601String(),
                ], 422);
            }

            if (filter_var($request->is_mock_location, FILTER_VALIDATE_BOOLEAN)) {
                Log::warning('Overtime attendance rejected: mock location', ['employee_id' => $employee->id, 'type' => $type, 'device_id' => $request->device_id]);
                return response()->json(['success' => false, 'message' => 'Absen ditolak: terdeteksi fake GPS/mock location', 'code' => 'ATTENDANCE_REJECTED'], 422);
            }

            // Lembur bebas lokasi untuk semua karyawan: lokasi hanya dicatat, tidak pernah menolak
            $location = $this->resolveLocation($employee, (float) $request->latitude, (float) $request->longitude, self::LOCATION_MODE);

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
            $packed    = app(ImageCompressor::class)->compress($request->file('photo'));
            $photoPath = 'attendance-overtime/' . now()->format('Y/m') . '/' . \Ramsey\Uuid\Uuid::uuid7()->toString() . '.' . $packed['extension'];
            Storage::disk('s3')->put($photoPath, $packed['contents'], ['ContentType' => $packed['mime']]);

            try {
                $log = OvertimeAttendanceLog::create([
                    'employee_id'            => $employee->id,
                    'overtime_submission_id' => $c->ot->id,
                    'toil_balance_id'        => $c->balance->id,
                    'store_id'               => $location['store_id'],
                    'work_date'              => Carbon::parse($c->ot->date)->toDateString(),
                    'type'                   => $type,
                    'attendance_mode'        => self::LOCATION_MODE,
                    'latitude'               => $request->latitude,
                    'longitude'              => $request->longitude,
                    'distance_from_store'    => $location['distance'],
                    'is_within_geofence'     => $location['within'],
                    'is_offsite'             => $location['offsite'],
                    'is_mock_location'       => false,
                    'photo_path'             => $photoPath,
                    'liveness_score'         => isset($face['distance']) ? round((float) $face['distance'], 2) : null,
                    'liveness_passed'        => true,
                    'device_id'              => $request->device_id,
                    'status'                 => self::STATUS_OK,
                    'flag_reason'            => null,
                    'logged_at'              => now(),
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                Storage::disk('s3')->delete($photoPath);
                if ($e->getCode() === '23000') {
                    return response()->json([
                        'success' => false,
                        'message' => $type === 'checkin' ? 'Anda sudah check-in lembur' : 'Anda sudah check-out lembur',
                        'code'    => $type === 'checkin' ? 'ALREADY_CHECKED_IN' : 'ALREADY_CHECKED_OUT',
                    ], 422);
                }
                Log::error('Overtime attendance save failed', ['employee_id' => $employee->id, 'type' => $type, 'error' => $e->getMessage()]);
                return response()->json(['success' => false, 'message' => 'Gagal menyimpan absen lembur, coba lagi'], 500);
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
            'overtime'   => $this->overtimePayload($c),
        ];
        if ($type === 'checkout') {
            $data['checkin_at']                = $c->checkin->logged_at;
            $data['overtime_duration_minutes'] = (int) $c->checkin->logged_at->diffInMinutes($log->logged_at);
        }

        return response()->json([
            'success' => true,
            'message' => $type === 'checkin' ? 'Check-in lembur berhasil' : 'Check-out lembur berhasil',
            'data'    => $data,
        ]);
    }

    /** Lembur kemarin/hari ini dan apa yang bisa dilakukan sekarang, untuk layar lembur di aplikasi. */
    public function today(Request $request)
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Data karyawan tidak ditemukan'], 404);
        }

        $candidates = $this->candidateBalances($employee);
        $checkin    = $this->pickOvertime($candidates, 'checkin');
        $checkout   = $this->pickOvertime($candidates, 'checkout');
        // Check-out yang masih terbuka didahulukan supaya lembur sebelumnya ditutup dulu
        $active     = $checkout ?? $checkin;

        return response()->json([
            'success' => true,
            'data' => [
                'has_overtime' => $candidates->isNotEmpty(),
                'next_action'  => $checkout ? 'checkout' : ($checkin ? 'checkin' : ($candidates->isEmpty() ? 'none' : 'wait_or_done')),
                'active'       => $active ? $this->overtimePayload($active) : null,
                'overtimes'    => $candidates->map(fn($c) => $this->overtimePayload($c) + [
                    'checkin_time'  => $c->checkin?->logged_at?->timezone(config('app.timezone'))->format('H:i'),
                    'checkout_time' => $c->checkout?->logged_at?->timezone(config('app.timezone'))->format('H:i'),
                    'can_checkin_from' => $c->start->copy()->subMinutes(OvertimeAttendanceLog::checkinEarlyMinutes())->toIso8601String(),
                    'can_checkout_from'  => $c->end->toIso8601String(),
                    'can_checkout_until' => $c->deadline->toIso8601String(),
                ])->values(),
            ],
        ]);
    }

    /**
     * Status satu lembur dari sudut pandang karyawan.
     * upcoming    : belum waktunya check-in
     * open        : sudah bisa check-in, belum check-in
     * active      : sudah check-in, belum check-out, batas check-out belum lewat
     * done        : sudah check-in dan check-out
     * missed      : tidak check-in sampai jam selesai lembur lewat
     * no_checkout : sudah check-in tapi tidak check-out sampai batasnya lewat
     */
    private function overtimeState(Carbon $start, Carbon $end, Carbon $deadline, $checkin, $checkout): string
    {
        $now = now();
        if ($checkout) return 'done';
        if ($checkin) return $now->lte($deadline) ? 'active' : 'no_checkout';
        if ($now->lt($start->copy()->subMinutes(OvertimeAttendanceLog::checkinEarlyMinutes()))) return 'upcoming';
        return $now->lte($end) ? 'open' : 'missed';
    }

    /** Satu baris riwayat lembur: jadwal, absen, status, dan saldo TOIL/Cash-nya. */
    private function historyItem(Toilbalances $balance, Collection $logs): array
    {
        $ot = $balance->overtimeSubmission;
        [$start, $end] = OvertimeAttendanceLog::overtimeWindow($ot);
        $deadline = OvertimeAttendanceLog::checkoutDeadline($ot);
        $in  = $logs->firstWhere('type', 'checkin');
        $out = $logs->firstWhere('type', 'checkout');
        $tz  = config('app.timezone');
        $date = Carbon::parse($ot->date);
        $point = fn($log) => $log ? [
            'is_within_geofence' => $log->is_within_geofence,
            'is_offsite'         => $log->is_offsite,
            'is_manual'          => $log->latitude === null,
            'status'             => $log->status,
            'flag_reason'        => $log->flag_reason,
        ] : null;

        // Status gabungan absen: ditandai > menunggu checker > menunggu HR > disetujui HR
        $statuses = collect([$in?->status, $out?->status])->filter();
        $status = collect(['flagged', 'pending', 'approved', 'approved_hr'])->first(fn($st) => $statuses->contains($st)) ?? $statuses->first();

        return [
            'overtime_submission_id'    => $ot->id,
            'work_date'                 => $date->toDateString(),
            'day_label'                 => $date->locale('id')->translatedFormat('l, d F Y'),
            'planned_start'             => $start->format('H:i'),
            'planned_end'               => $end->format('H:i'),
            'planned_hours'             => (float) $ot->total_hours,
            'compensation_type'         => $ot->compensation_type,
            'reason'                    => $ot->reason,
            'start_at'                  => $start->toIso8601String(),
            'end_at'                    => $end->toIso8601String(),
            'can_checkin_from'          => $start->copy()->subMinutes(OvertimeAttendanceLog::checkinEarlyMinutes())->toIso8601String(),
            'can_checkout_from'         => $end->toIso8601String(),
            'can_checkout_until'        => $deadline->toIso8601String(),
            'state'                     => $this->overtimeState($start, $end, $deadline, $in, $out),
            'checkin_time'              => $in?->logged_at?->timezone($tz)->format('H:i'),
            'checkout_time'             => $out?->logged_at?->timezone($tz)->format('H:i'),
            'overtime_duration_minutes' => $in && $out ? (int) $in->logged_at->diffInMinutes($out->logged_at) : null,
            'status'                    => $status,
            'checkin_status'            => $point($in),
            'checkout_status'           => $point($out),
            'balance'                   => [
                'status'          => $balance->effectiveStatus($ot->compensation_type),
                'earned_hours'    => (float) $balance->earned_hours,
                'used_hours'      => (float) $balance->used_hours,
                'remaining_hours' => $balance->remaining_hours !== null ? (float) $balance->remaining_hours : null,
                'expires_at'      => $balance->expires_at?->toDateString(),
                'paid_at'         => $balance->paid_at?->toDateString(),
            ],
        ];
    }

    /**
     * Riwayat lembur: SEMUA lembur yang disetujui (punya saldo di Toilbalances, tidak dibatalkan)
     * pada rentang tanggal, termasuk yang belum/tidak diabsen, plus lembur yang akan datang.
     */
    public function history(Request $request)
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Data karyawan tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date'   => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Filter tanggal tidak valid', 'errors' => $validator->errors()], 422);
        }

        $startDate = $request->start_date ?? now()->subDays(30)->toDateString();
        $endDate   = $request->end_date ?? now()->toDateString();
        if (Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) > 93) {
            return response()->json(['success' => false, 'message' => 'Rentang tanggal maksimal 3 bulan'], 422);
        }
        $today = now()->toDateString();

        $base = fn() => Toilbalances::with('overtimeSubmission')
            ->where('employee_id', $employee->id)
            ->where('status', '!=', 'cancelled')
            ->whereHas('overtimeSubmission', fn($q) => $q->where('status', 'Approved'));

        $inRange = $base()
            ->whereHas('overtimeSubmission', fn($q) => $q->whereBetween('date', [$startDate, $endDate]))
            ->get();
        // Lembur yang akan datang selalu ikut, terlepas dari rentang tanggal
        $upcoming = $base()
            ->whereHas('overtimeSubmission', fn($q) => $q->where('date', '>', $today))
            ->get()
            ->reject(fn($b) => $inRange->contains('id', $b->id));

        $logs = OvertimeAttendanceLog::whereIn('overtime_submission_id', $inRange->merge($upcoming)->pluck('overtime_submission_id'))
            ->get()
            ->groupBy('overtime_submission_id');

        $toItems = fn($balances) => $balances
            ->map(fn($b) => $this->historyItem($b, $logs->get($b->overtime_submission_id, collect())));

        $history = $toItems($inRange)->sortByDesc('start_at')->values();
        $next    = $toItems($upcoming)->sortBy('start_at')->values();

        return response()->json([
            'success' => true,
            'data' => [
                'period_start' => $startDate,
                'period_end'   => $endDate,
                'total'        => $history->count(),
                'summary'      => [
                    'done'           => $history->where('state', 'done')->count(),
                    'active'         => $history->where('state', 'active')->count(),
                    'open'           => $history->where('state', 'open')->count(),
                    'missed'         => $history->whereIn('state', ['missed', 'no_checkout'])->count(),
                    'upcoming'       => $history->where('state', 'upcoming')->count() + $next->count(),
                    'duration_minutes' => (int) $history->sum('overtime_duration_minutes'),
                    'planned_hours'  => (float) $history->sum('planned_hours'),
                ],
                'history'      => $history,
                'upcoming'     => $next,
            ],
        ]);
    }
}
