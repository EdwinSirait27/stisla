<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class AttendanceLog extends Model
{
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
        'employee_id', 'store_id', 'type', 'latitude', 'longitude',
        'distance_from_store', 'is_within_geofence', 'is_mock_location',
        'photo_path', 'liveness_score', 'liveness_passed',
        'device_id', 'status', 'flag_reason', 'logged_at','work_date',
    ];

    protected $casts = [
        'is_within_geofence' => 'boolean',
        'is_mock_location' => 'boolean',
        'liveness_passed' => 'boolean',
        'logged_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function store()
    {
        return $this->belongsTo(Stores::class, 'store_id', 'id');
    }
}