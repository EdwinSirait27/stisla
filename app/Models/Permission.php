<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Illuminate\Notifications\Notifiable;
class Permission extends Model
{
    use HasFactory;

    protected $table = 'terms'; 
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
         'device_wifi_mac', 'device_lan_mac','description'
    ];
  
    public function users()
    {
        return $this->hasOne(User::class, 'permission_id');
    }
 
}
