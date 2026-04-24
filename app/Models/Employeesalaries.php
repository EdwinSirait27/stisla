<?php

namespace App\Models;
use Ramsey\Uuid\Uuid;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employeesalaries extends Model
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
    protected $table = 'employee_salaries'; 
    protected $fillable = [
        'employee_id',
        'satuts',
        'basic_salary',
        'daily_rate',
        'effective_date',
        'end_date',
    ];
public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id','id');
    }
}
