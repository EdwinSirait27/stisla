<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
class Fingerprintrecap extends Model
{
    use HasUuids;
    protected $table = 'fingerprints_recap';
    protected $fillable = [
        'employee_id',
        'pin',
        'date',
        'period_in',
        'period_out',
        'time_in',
        'time_out',
        'duration_minutes',
        'is_counted',
        'device_sn',
        'sync_status',
        'synced_at',
    ];
    protected $casts = [
        'date'      => 'date',
        'synced_at' => 'datetime',
    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    public function schedule()
    {
        return $this->hasOne(Schedule::class, 'employee_id', 'employee_id')
            ->whereColumn('schedules.date', 'fingerprints_recap.date');
    }
}