<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class RegisteredDevice extends Model
{
    protected $table = 'registered_devices';
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
        'device_id',
        'device_name',
        'is_active',
        'registered_at',
        'reset_at',
        'reset_by',
        'reset_reason',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'registered_at' => 'datetime',
        'reset_at'      => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function resetBy()
    {
        return $this->belongsTo(User::class, 'reset_by', 'id');
    }

    /** ID device disamarkan untuk ditampilkan: 4 karakter awal & akhir saja. */
    public function getMaskedDeviceIdAttribute(): string
    {
        $id = (string) $this->device_id;
        return strlen($id) <= 8 ? str_repeat('•', strlen($id)) : substr($id, 0, 4) . '••••' . substr($id, -4);
    }
}