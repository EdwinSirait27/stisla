<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

use Spatie\Permission\Traits\HasRoles;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'users'; 
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'terms_id',
        'username',
        'password',
        'status',
    ];
    
    
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
// app/Models/User.php
protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s', // Format default MySQL
    'updated_at' => 'datetime:Y-m-d H:i:s',
];
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    
        public function findForAuth($username)
    {
        return $this->where('username', $username)->first();
    }
    public function Sales()
    {
        return $this->hasMany(Sale::class);
    }
    public function Terms()
    {
        return $this->belongsTo(Terms::class,'terms_id');
    }
    public function Employee()
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }

    // Relasi ke tabel StockAdjustment
    public function StockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }

    // Relasi ke tabel Purchase
    public function Purchases()
    {
        return $this->hasMany(Purchase::class);
    }
    public function Activity()
    {
        return $this->hasMany(Activity::class, 'user_id', 'id');
    }

    public function sessions()
    {
        return $this->hasMany(UserSession::class, 'user_id', 'id');
    }

    // Method untuk mendapatkan sesi aktif
    public function getActiveSessionsAttribute()
    {
        return $this->sessions()->where('last_activity', '>', now()->subHours(2))->get();
    }

    // Method untuk mendapatkan jumlah sesi aktif
    public function getActiveSessioCountAttribute()
    {
        return $this->sessions()->where('last_activity', '>', now()->subHours(2))->count();
    }

}


