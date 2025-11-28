<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class LeaveRequestApproval extends Model
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
    protected $table = 'leave_request_approvals_tables';
    protected $fillable = [
        'supervisor_id',
        'leave_request_id',
        'sequence',
        'status',
        'approved_at',
        'note',
    ];
   public function employees()
{
    return $this->belongsTo(Employee::class, 'employee_id', 'id')
        ->where('status', 'Active');
}

     public function leaves()
    {
        return $this->belongsTo(Leavetypes::class, 'leave_type_id', 'id');
    }
    
}
