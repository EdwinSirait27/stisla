<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class AssetCategories extends Model
{
     use HasFactory;
    protected $table = 'asset_categories';
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
        'asset_category_name',
        'description'
    ];
   public function setAssetCategoryNameAttribute($value)
    {
        $this->attributes['asset_category_name'] = strtoupper($value);
    }
}
