<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Shiftswaps extends Model
{
    use HasFactory;
    protected $table = 'shift_swaps_tables';
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
    protected $fillable = [
        'request_id',
        'reciever_id',
        'original_shift_id',
        'new_shift_id',
        'status',
        'reason',
        'rejection_reason',
        'approved_by',
        'approved_at'
    ];
    public function request()
    {
        return $this->belongsTo(User::class, 'request_id', 'id');
    }
    public function reciever()
    {
        return $this->belongsTo(User::class, 'reciever_id', 'id');
    }
    public function originalshift()
    {
        return $this->belongsTo(EmployeeShifts::class, 'original_shift_id', 'id');
    }
    public function newshift()
    {
        return $this->belongsTo(EmployeeShifts::class, 'new_shift_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }
}