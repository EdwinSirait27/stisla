<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Overtimepayments extends Model
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
    protected $table = 'overtime_payments_tables';
    protected $fillable = [
        'overtime_submission_id',
        'employee_id',
        'total_hours',
        'hourly_rate',
        'multiplier',
        'amount',
        'payroll_period_id',
    ];
     public function employees()
    {
        return $this->belongsTo(Employee::class,'employee_id','id');
    }
    public function payrolls()
    {
        return $this->belongsTo(Payrolls::class, 'payroll_period_id','id');
    }
}
