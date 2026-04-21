<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Models\Shifts;

class Roster extends Model
{
    use HasUuids;

    protected $table = 'roster';

    public function uniqueIds(): array
    {
        return ['id'];
    }

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
        return $this->belongsTo(Shifts::class, 'shift_id');
    }

    public function fingerprintRecap()
    {
        return $this->hasOne(FingerprintRecap::class, 'employee_id', 'employee_id')
            ->whereColumn('fingerprint_recaps.date', 'roster.date');
    }
}