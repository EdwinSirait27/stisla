<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RosterPhCarryover extends Model
{
    use HasUuids;

    protected $table = 'roster_ph_carryovers';

    protected $fillable = [
        'employee_id',
        'ph_date',
        'ph_name',
        'expired_at',
        'status',
        'used_date',
    ];

    protected $casts = [
        'ph_date'    => 'date',
        'expired_at' => 'date',
        'used_date'  => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}