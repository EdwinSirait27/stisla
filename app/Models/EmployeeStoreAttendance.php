<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class EmployeeStoreAttendance extends Model
{
     protected $table = 'employee_store_attendances';
    public $incrementing = false;
    protected $keyType   = 'string';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = Uuid::uuid7()->toString();
            }
        });
    }

    protected $fillable = [
        'employee_id',
        'employee_store_id',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function employeeStore()
    {
        return $this->belongsTo(EmployeeStore::class, 'employee_store_id', 'id');
    }
}
