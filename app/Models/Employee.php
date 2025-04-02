<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;


class Employee extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'employees';
    protected $primaryKey = 'employee_id';
    public $incrementing = false;
    protected $keyTypes = 'string';
    protected $fillable = [
        'fullName',
        'position',
        'department_id',
        'hireDate',
        'phone',
        'email',
        'salary',
        'status'
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->employee_id) {
                $model->employee_id = Str::uuid();
            }
        });
    }
    public function department()
    {
        return $this->belongsTo(Departments::class, 'department_id');
    }
}

