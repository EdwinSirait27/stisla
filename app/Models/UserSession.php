<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UserSession extends Model
{
    use HasFactory, HasUuids;
    public $incrementing = false; // Nonaktifkan auto-increment
    protected $keyType = 'string'; // Pastikan tipe data adalah string
    protected $fillable = [
        'user_id', 
        'session_id', 
        'ip_address', 
        'last_activity', 
        'device_type'
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
