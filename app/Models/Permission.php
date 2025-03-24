<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Notifications\Notifiable;
class Permission extends Model
{
    use HasFactory, Notifiable;

    public $incrementing = false; // Nonaktifkan auto-increment
    protected $keyType = 'string'; // Pastikan tipe data adalah string
    protected $fillable = [
        'id', 'device_wifi_mac', 'device_lan_mac'
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

 
}
