<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Stores extends Model
{
    use HasFactory;
    protected $table = 'stores_tables';
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
        'name','address','phone_num','manager_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'manager_id', 'id');
    }
}

