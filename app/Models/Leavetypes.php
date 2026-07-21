<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Leavetypes extends Model
{
    use HasFactory;

    public $incrementing = false;
    public $timestamps = false;
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

    protected $table = 'leave_types_tables';

    protected $fillable = [
        'name',
        'is_paid',
        'default_balance',
        // ── kolom aturan Level 2 ──
        'is_special',
        'gender_rule',
        'fixed_days',
        'max_days',
        'require_attachment',
        'require_married',
        'allowed_status',
        'roster_day_type',
        'is_active',
    ];

    protected $casts = [
        'is_paid'            => 'boolean',
        'is_special'         => 'boolean',
        'require_attachment' => 'boolean',
        'require_married'    => 'boolean',
        'is_active'          => 'boolean',
        'fixed_days'         => 'integer',
        'max_days'           => 'integer',
        'default_balance'    => 'decimal:2',
    ];

    public function balances()
    {
        return $this->hasMany(Leavebalance::class);
    }

    public function leaverequests()
    {
        return $this->hasMany(Leaverequest::class);
    }
}