<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Terms extends Model
{
    use HasFactory;
    protected $fillable = [
        'id', 'device_wifi_mac', 'device_lan_mac'
    ];
    public function users()
    {
        return $this->hasOne(User::class, 'terms_id');
    }
}
