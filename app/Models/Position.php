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
    protected $attributes = [
        'publish_career' => false,
    ];
    protected $casts = [
        'career_start_date'       => 'date',
        'career_end_date' => 'date',
        'publish_career'         => 'boolean',
    ];
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
        'vacancy',
        'publish_career',
        'career_start_date',
        'career_end_date',
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
    public function benefits()
    {
        return $this->hasMany(PositionResponsibility::class, 'position_id')
            ->where('type', 'benefit')
            ->orderBy('order');
    }
    public function requirements()
    {
        return $this->hasMany(PositionResponsibility::class, 'position_id')
            ->where('type', 'requirement')
            ->orderBy('order');
    }
    public function skills()
    {
        return $this->hasMany(PositionResponsibility::class, 'position_id')
            ->where('type', 'skill')
            ->orderBy('order');
    }
    public function allowances()
    {
        return $this->hasMany(PositionResponsibility::class, 'position_id')
            ->where('type', 'allowance')
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
