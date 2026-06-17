<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Models\Shifts;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Log;

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
        'sick_attachment'
    ];


public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->useLogName('roster')
        ->logOnly([
            'employee_id',
            'date',
            'day_type',
            'notes',
        ])
        // Hapus logOnlyDirty() dan dontSubmitEmptyLogs()
        ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
            'created' => 'Roster was created',
            'updated' => 'Roster was updated',
            'deleted' => 'Roster was deleted',
            default   => "Roster {$eventName}",
        });
}

public function tapActivity(Activity $activity, string $eventName): void
{
    // Ambil shift_name dari relasi
    $shiftName = $this->shift?->shift_name ?? '-';
    $employeeName = $this->employee?->employee_name ?? '-';

    $attributes = [
        'employee name' => $employeeName,
        'shift name'  => $shiftName,
        'date'        => $this->date?->toDateString(),
        'day type'    => $this->day_type,
        'notes'       => $this->notes ?? 'empty',
    ];

    if ($eventName === 'created') {
        $activity->properties = $activity->properties->merge([
            'attributes' => $attributes,
        ]);
    }

    if ($eventName === 'updated') {
        $activity->properties = $activity->properties->merge([
            'attributes' => $attributes,
        ]);
    }

    if ($eventName === 'deleted') {
        $activity->properties = $activity->properties->merge([
            'attributes' => $attributes,
            'old'        => $attributes,
        ]);
    }
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
// public function getActivitylogOptions(): LogOptions
// {
//     return LogOptions::defaults()
//         ->logOnly([
//             'employee_id',
//             'shift.shift_name', 
//             'date',
//             'day_type',
//             'notes',
//         ])
//         ->logOnlyDirty()
//         ->dontSubmitEmptyLogs()
//         ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
//             'created' => 'Roster was created',
//             'updated' => 'Roster was updated',
//             'deleted' => 'Roster was deleted',
//             default   => "Roster {$eventName}",
//         });
// }