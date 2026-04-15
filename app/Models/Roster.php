<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Roster extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'roster';

    protected $fillable = [
        'employee_id',
        'shift_id',
        'date',
        'day_type',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function fingerprintRecap()
    {
        return $this->hasOne(FingerprintRecap::class, 'employee_id', 'employee_id')
            ->whereColumn('fingerprint_recaps.date', 'roster.date');
    }
}