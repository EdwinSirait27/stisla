<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Submissions extends Model
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
    protected $table = 'submissions_tables'; 
    protected $fillable = [
        'user_id',
        'approval_id',
        'type',
        'duration',
        'status',
    ];
   
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function approval()
    {
        return $this->belongsTo(User::class, 'approval_id', 'id');
    }
}
