<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;


class Leaves extends Model
{
    use HasFactory;
    protected $table = 'leave_requests_tables';
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
        'user_id',
        'start_date',
        'end_date',
        'reason',
        'approved_by',
        'approved_at',
        'rejection_reason'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function approved()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }
}