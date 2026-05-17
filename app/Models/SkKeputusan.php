<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class SkKeputusan extends Model
{
   use HasFactory;
 protected $table = 'sk_keputusan';
    public $incrementing = false;
    protected $keyType = 'string';
     public $timestamps = false;

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
        'sk_letter_id',
        'content_keputusan',
        'order_no'
    ];
    public function skletters()
    {
        return $this->belongsTo(SkLetter::class, 'sk_letter_id', 'id');
    }
}
