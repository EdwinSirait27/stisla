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
