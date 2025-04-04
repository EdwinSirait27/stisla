<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Notifications\Notifiable;
class Permission extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'terms'; 
   
    protected $fillable = [
        'id', 'device_wifi_mac', 'device_lan_mac'
    ];
  
    public function users()
    {
        return $this->hasOne(User::class, 'permission_id');
    }
 
}
