<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FingerprintRecap extends Model
{
    use HasUuids;

    protected $table = 'fingerprints_recap';

    protected $fillable = [
        'employee_id',
        'pin',
        'date',
        'time_in',
        'time_out',
        'duration_minutes',
        'device_sn',
        'sync_status',
        'synced_at',
    ];

    protected $casts = [
        'date'      => 'date',
        'synced_at' => 'datetime',
    ];

    // Relasi ke Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // Relasi ke Schedule (jadwal harian)
    // Schedule → roster_id → Roster (shift Pagi/Siang/Malam)
    public function schedule()
    {
        return $this->hasOne(Schedule::class, 'employee_id', 'employee_id')
            ->whereColumn('schedules.date', 'fingerprints_recap.date');
    }
}