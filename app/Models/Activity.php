<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Activity extends Model
{
    use HasFactory;
    public $incrementing = false; 
    protected $table = 'activity'; // Tentukan nama tabel secara eksplisit

   
    protected $keyType = 'string'; // Pastikan tipe data adalah string
    protected $fillable = [
        'user_id',
        'activity_type',
        'activity_time',
        'device_lan_mac',
        'device_wifi_mac',
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
