<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;
class Submissions extends Model
{
     use HasFactory;
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
    protected $table = 'submissions'; 
    protected $fillable = [
        'employee_id',
        'approver_id',
        'type',
        'leave_date_from',
        'leave_date_to',
        'duration',
        'status',
        'status_submissions',
        'time_toil',
    ];  
    protected $casts = [
          
      'leave_date_from' => 'datetime',
        'leave_date_to'   => 'datetime',
    ];
     public function getFormattedDurationAttribute()
    {
        $from = $this->leave_date_from;
        $to = $this->leave_date_to;

        if ($from->format('H:i:s') !== '00:00:00' || $to->format('H:i:s') !== '00:00:00') {
            // Ada jam → pakai hour
            return $this->duration . ' ' . (\Illuminate\Support\Str::plural('Hour', $this->duration));
        }

        // Hanya tanggal → pakai day
        return $this->duration . ' ' . (\Illuminate\Support\Str::plural('Day', $this->duration));
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_id', 'id');
    }
}