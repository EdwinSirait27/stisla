<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Leavebalance extends Model
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
    protected $table = 'leave_balances_tables';
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'balance_days',
        'balance_hours',
        'type',
        'year',
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
  