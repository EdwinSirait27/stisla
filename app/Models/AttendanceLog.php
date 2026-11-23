<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class AttendanceLog extends Model
{
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

    /** Nama log aktivitas (Spatie activitylog) untuk absensi mobile. */
    public const LOG_NAME = 'attendance-mobile';

   protected $table = 'attendance_logs';
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
        'employee_id', 'store_id', 'type', 'session', 'latitude', 'longitude',
        'distance_from_store', 'is_within_geofence', 'is_mock_location',
        'photo_path', 'liveness_score', 'liveness_passed',
        'device_id', 'status', 'flag_reason', 'logged_at', 'work_date',
        'attendance_mode', 'is_offsite',
        'created_by', 'checked_by', 'checked_at', 'hr_approved_by', 'hr_approved_at',
    ];

    protected $casts = [
        'session' => 'integer',
        'is_within_geofence' => 'boolean',
        'is_offsite' => 'boolean',
        'is_mock_location' => 'boolean',
        'liveness_passed' => 'boolean',
        'logged_at' => 'datetime',
        'checked_at' => 'datetime',
        'hr_approved_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
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

    public function store()
    {
        return $this->belongsTo(Stores::class, 'store_id', 'id');
    }
}