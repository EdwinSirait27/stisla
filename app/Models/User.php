<?php

namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
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
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'users'; 
    protected $fillable = [
        'id',
        'terms_id',
        'employee_id',
        'username',
        'password',
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


