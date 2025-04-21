<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Brands extends Model
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
    protected $table = 'brands_tables'; 
    protected $casts = [
        'created_at' => 'datetime:Y-m-d', // Format default MySQL
       
    ];
    protected $fillable = [
        'brand_code',
        'brand_name',
        'description',
    ];
 

   
}
