<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Fingerprints extends Model
{
    use HasFactory;
    protected $table = 'fingerprint_devices_tables';
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
        'device_name',
        'serial_number',
        'last_sync',
        'status'
    ];
    public function store()
    {
        return $this->belongsTo(Stores::class, 'store_id', 'id');
    }
}
