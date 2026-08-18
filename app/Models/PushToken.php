<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class PushToken extends Model
{
    protected $table = 'push_tokens';
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
    protected $fillable = ['user_id', 'token', 'platform', 'is_active'];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}