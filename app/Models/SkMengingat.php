<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class SkMengingat extends Model
{
   use HasFactory;
 protected $table = 'sk_mengingat';
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
        'content_mengingat',
        'order_no'
    ];
    public function skletters()
    {
        return $this->belongsTo(SkLetter::class, 'sk_letter_id', 'id');
    }
   
}
