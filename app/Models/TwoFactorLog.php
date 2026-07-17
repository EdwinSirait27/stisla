<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class TwoFactorLog extends Model
{
     protected $table = 'two_factor_logs';
    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'id', 'user_id', 'event', 'ip_address', 'device_type',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = Uuid::uuid7()->toString();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
