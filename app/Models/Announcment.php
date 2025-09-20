<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Announcment extends Model
{
    use HasFactory;
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
    protected $table = 'announcements'; 
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'publish_date',
        
    ];
   
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
