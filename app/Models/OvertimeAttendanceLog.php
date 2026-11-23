<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

/**
 * Log absensi lembur (check-in / check-out) dari aplikasi mobile atau entri manual.
 * Selalu terikat ke satu lembur yang disetujui (Overtimesubmissions) dan saldonya (Toilbalances).
 */
class OvertimeAttendanceLog extends Model
{
    protected $table = 'overtime_attendance_logs';

    /** Check-in lembur paling cepat sekian menit sebelum jam mulai lembur. */
    public const CHECKIN_EARLY_MINUTES = 60;

    /**
     * Persetujuan bertingkat:
     * pending (diajukan) → approved (lolos checker / verifikasi sistem) → approved_hr (final).
     * flagged = ditandai bermasalah oleh HR.
     */
    public const STATUS_PENDING     = 'pending';
    public const STATUS_APPROVED    = 'approved';
    public const STATUS_APPROVED_HR = 'approved_hr';
    public const STATUS_FLAGGED     = 'flagged';

    public const STATUSES = [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_APPROVED_HR, self::STATUS_FLAGGED];

    public const STATUS_LABELS = [
        self::STATUS_PENDING     => 'Menunggu checker',
        self::STATUS_APPROVED    => 'Menunggu HR',
        self::STATUS_APPROVED_HR => 'Disetujui HR',
        self::STATUS_FLAGGED     => 'Ditandai',
    ];

    /** Nama log aktivitas (Spatie activitylog) untuk absensi lembur mobile. */
    public const LOG_NAME = 'overtime-attendance-mobile';
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = Uuid::uuid7()->toString();
            }
        });
    }

    protected $fillable = [
        'employee_id', 'overtime_submission_id', 'toil_balance_id', 'store_id', 'work_date', 'type',
        'attendance_mode', 'latitude', 'longitude', 'distance_from_store', 'is_within_geofence',
        'is_offsite', 'is_mock_location', 'photo_path', 'liveness_score', 'liveness_passed',
        'device_id', 'status', 'flag_reason', 'logged_at',
        'created_by', 'checked_by', 'checked_at', 'hr_approved_by', 'hr_approved_at',
    ];

    protected $casts = [
        'is_within_geofence' => 'boolean',
        'is_offsite'         => 'boolean',
        'is_mock_location'   => 'boolean',
        'liveness_passed'    => 'boolean',
        'logged_at'          => 'datetime',
        'checked_at'         => 'datetime',
        'hr_approved_at'     => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function store()
    {
        return $this->belongsTo(Stores::class, 'store_id', 'id');
    }

    public function overtimeSubmission()
    {
        return $this->belongsTo(Overtimesubmissions::class, 'overtime_submission_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function checkedBy()
    {
        return $this->belongsTo(User::class, 'checked_by', 'id');
    }

    public function hrApprovedBy()
    {
        return $this->belongsTo(User::class, 'hr_approved_by', 'id');
    }

    public function toilBalance()
    {
        return $this->belongsTo(Toilbalances::class, 'toil_balance_id', 'id');
    }

    /**
     * Jam mulai & selesai lembur yang disetujui. Selesai <= mulai berarti lewat tengah malam.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function overtimeWindow(Overtimesubmissions $ot): array
    {
        $date    = Carbon::parse($ot->date)->toDateString();
        $endDate = $ot->end_date ? Carbon::parse($ot->end_date)->toDateString() : $date;
        $start   = Carbon::parse($date . ' ' . $ot->start_time);
        $end     = Carbon::parse($endDate . ' ' . $ot->end_time);
        if ($end->lte($start)) {
            $end->addDay();
        }
        return [$start, $end];
    }

    /**
     * Check-out lembur boleh mulai jam selesai lembur (sesuai jadwal) dan tetap boleh setelah lewat,
     * sampai akhir hari setelah tanggal lembur. Lewat dari itu lemburnya tidak lagi dicari aplikasi
     * (Api\OvertimeAttendanceController hanya melihat lembur kemarin s/d besok), jadi pakai entri manual.
     */
    public static function checkoutDeadline(Overtimesubmissions $ot): Carbon
    {
        [, $end] = self::overtimeWindow($ot);
        return Carbon::parse($ot->date)->addDay()->endOfDay()->max($end);
    }
}
