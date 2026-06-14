<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Position extends Model
{
    use HasFactory;
    protected $table = 'position_tables';
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
        'name',
        'role_summary',
        'status'
        
    ];
     public function responsibilities()
    {
        return $this->hasMany(PositionResponsibility::class, 'position_id')
            ->where('type', 'key_respon')
            ->orderBy('order');
    }

    public function qualifications()
    {
        return $this->hasMany(PositionResponsibility::class, 'position_id')
            ->where('type', 'qualification')
            ->orderBy('order');
    }

    // Ambil semua sekaligus
    public function positionResponsibilities()
    {
        return $this->hasMany(PositionResponsibility::class, 'position_id')
            ->orderBy('type')
            ->orderBy('order');
    }
}
