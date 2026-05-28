<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
class PublicHoliday extends Model
{
    protected $table = 'ph';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'type',
        'date',
        'remark',
    ];

    protected $casts = [
        'date' => 'date',
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
}