<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'schedules';

    protected $fillable = [
        'employee_id',
        'roster_id',
        'date',
        'day_type',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // Relasi ke Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // Relasi ke Roster (shift Pagi/Siang/Malam)
    public function roster()
    {
        return $this->belongsTo(Roster::class, 'roster_id');
    }

    // Relasi ke FingerprintRecap
    public function fingerprintRecap()
    {
        return $this->hasOne(FingerprintRecap::class, 'employee_id', 'employee_id')
            ->whereColumn('fingerprint_recaps.date', 'schedules.date');
    }
}