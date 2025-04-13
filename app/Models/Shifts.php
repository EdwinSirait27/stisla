<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Shifts extends Model
{
    use HasFactory;
    protected $table = 'shifts_tables';
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
        'store_id',
        'shift_name',
        'start_name',
        'end_time',
        'last_sync',
        'is_holiday'
        
    ];
    public function store()
    {
        return $this->belongsTo(Stores::class, 'store_id', 'id');
    }
    
}
