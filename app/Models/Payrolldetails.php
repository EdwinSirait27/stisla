<?php

namespace App\Models;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payrolldetails extends Model
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
    protected $table = 'payroll_details'; 
    protected $fillable = [
        'employee_id',
        'period_month',
        'period_year',
        'gross_salary',
        'total_deduction',
        'total_income',
        'net_salary',
        'take_home',
        'status',
    ];
public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id','id');
    }
}
