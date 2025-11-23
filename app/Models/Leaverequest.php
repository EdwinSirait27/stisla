<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Leaverequest extends Model
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
    protected $table = 'leave_requests_tables';
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'total_hours',
        'reason',
        'status',
        'approver_id',
        'approved_at',
    ];
     public function employees()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
    public function leavetypes()
    {
        return $this->belongsTo(Leavetypes::class, 'leave_type_id', 'id');
    }
    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_id', 'id');
    }

}
