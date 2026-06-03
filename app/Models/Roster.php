<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Models\Shifts;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Roster extends Model
{
    use HasUuids, LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'employee_id',
                'shift_id',
                'date',
                'day_type',
                'notes',
            ])
            ->logOnlyDirty()          
            ->dontSubmitEmptyLogs()   
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => 'Roster was created',
                'updated' => 'Roster was updated',
                'deleted' => 'Roster was deleted',
                default   => "Roster {$eventName}",
            });
    }


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