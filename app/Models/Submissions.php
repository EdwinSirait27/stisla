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
    ];  
    protected $casts = [
            'leave_date_form' => 'date:Y-m-d',
            'leave_date_to' => 'date:Y-m-d',
     
    ];
    public function getDurationAttribute($value)
    {
        if (is_numeric($value)) {
            return $value;
        }

        if ($this->leave_date_from && $this->leave_date_to) {
            $from = Carbon::parse($this->leave_date_from);
            $to = Carbon::parse($this->leave_date_to);
            return $from->diffInDays($to) + 1;
        }

        return 0;
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