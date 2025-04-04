<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UserSession extends Model
{
    use HasFactory;
    protected $table = 'user_sessions'; 
    protected $primaryKey = 'id';
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
