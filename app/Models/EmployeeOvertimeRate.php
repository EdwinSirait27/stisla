<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class EmployeeOvertimeRate extends Model
{
    use HasFactory;
    protected $table    = 'employee_overtime_rates';
    protected $fillable = ['employee_id', 'rate_per_hour'];
    protected $casts    = ['rate_per_hour' => 'decimal:2'];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = Uuid::uuid7()->toString();
            }
        });
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
