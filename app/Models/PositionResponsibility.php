<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class PositionResponsibility extends Model
{
    protected $table = 'position_responsibilities';
    public $incrementing = false;
    protected $keyType = 'string';

    const TYPE_KEY_RESPON = 'key_respon';
    const TYPE_QUALIFICATION = 'qualification';

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
        'position_id',
        'type',
        'description',
        'order',
    ];

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    // Scope helper
    public function scopeKeyRespon($query)
    {
        return $query->where('type', self::TYPE_KEY_RESPON);
    }

    public function scopeQualification($query)
    {
        return $query->where('type', self::TYPE_QUALIFICATION);
    }
}