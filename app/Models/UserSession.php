<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class UserSession extends Model
{
    use HasFactory;
    protected $table = 'user_sessions'; 
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
        'user_id', 
        'session_id', 
        'ip_address', 
        'last_activity', 
        'device_type'
    ];
   
    // Relationship with User model
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    // Scope untuk sesi aktif
    public function scopeActive($query)
    {
        return $query->where('last_activity', '>', now()->subHours(2));
    }
}