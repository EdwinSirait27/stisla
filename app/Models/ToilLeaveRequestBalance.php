<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class ToilLeaveRequestBalance extends Model
{
    use HasFactory;

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

    protected $table = 'toil_leave_request_balances';

    protected $fillable = [
        'leave_request_id',
        'toil_balance_id',
        'hours_taken',
    ];

    protected $casts = [
        'hours_taken' => 'decimal:2',
    ];

    // ── Relasi ke klaim leave ──
    public function leaveRequest()
    {
        return $this->belongsTo(ToilLeaveRequests::class, 'leave_request_id', 'id');
    }

    // ── Relasi ke saldo yang dipotong ──
    public function balance()
    {
        return $this->belongsTo(Toilbalances::class, 'toil_balance_id', 'id');
    }
}