<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Activity extends Model
{
    use HasFactory;
    protected $table = 'activity_logs'; 
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'activity_type',
        'activity_time',
        'device_lan_mac',
        'device_wifi_mac',
    ];
 

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
