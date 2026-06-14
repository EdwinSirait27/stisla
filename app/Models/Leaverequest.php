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
    protected $table = 'leave_requests';
    protected $fillable = [
        'leave_balance_id',
        'start_date',
        'end_date',
        'total_days',
        'employee_reason',
        'approver_reason',
        'status',
        'approved_by',
    ];

    public function leavebalance()
    {
        return $this->belongsTo(Leavebalance::class, 'leave_balance_id', 'id');
    }

    public function employee()
    {
        return $this->leavebalance->employees;
    }

    public function leaveType()
    {
        return $this->leaveBalance->leaves;
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by', 'id');
    }

    public function approvers()
    {
        $employee = $this->leavebalance->employees;

        if (!$employee) {
            return collect();
        }

        return $employee->atasanList()->pluck('id');
    }

    public function canBeApprovedBy($employeeId)
    {
        return $this->approvers()->contains($employeeId);
    }
}