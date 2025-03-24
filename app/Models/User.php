<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;



class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;
    public $incrementing = false; // Nonaktifkan auto-increment
    protected $keyType = 'string'; // Pastikan tipe data adalah string
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'permission_id',
        'name',
        'username',
        'password',
        'user_type',
        'role',
        'phone',
        'status',
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
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string', // Cast UUID sebagai string

        'email_verified_at' => 'datetime',
    ];
    
        public function findForAuth($username)
    {
        return $this->where('username', $username)->first();
    }
    public function Sales()
    {
        return $this->hasMany(Sale::class);
    }
    public function Permissions()
    {
        return $this->belongsTo(Permission::class,'permission_id');
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



}


