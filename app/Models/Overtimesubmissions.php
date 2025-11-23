<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Overtimesubmissions extends Model
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
    protected $table = 'overtime_submissions_tables';
    protected $fillable = [
        'employee_id',
        'date',
        'start_time',
        'end_time',
        'total_hours',
        'reason',
        'compensation_type',
        'status',
        'approver_id',
        'approver_at',
    ];
     public function employees()
    {
        return $this->belongsTo(Employee::class,'employee_id','id');
    }
     public function approver()
    {
        return $this->belongsTo(Employee::class,'employee_id','id');
    }
    public function payrolls()
    {
        return $this->belongsTo(Payrolls::class, 'payroll_period_id','id');
    }
}
