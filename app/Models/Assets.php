<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Assets extends Model
{
    use HasFactory;

    protected $table = 'assets';
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
        'asset_category_id',
        'uoms',
        'qty',
        'asset_name',
        'serial_number',
        'brand',
        'model',
        'purchase_date',
        'purchase_price',
        'purchase_price',
        'status',
        'notes'
    ];
    
protected $casts = [
    'purchase_date'  => 'date',
    'purchase_price' => 'decimal:2',
];
    public static function getStatusOptions(): array
    {
        return [
            'Damaged'            => 'Damaged',
            'Lost' => 'Lost',
            'Retired'              => 'Retired',
        ];
    }
    public static function getUomOptions(): array
    {
        return [
            'PIECES' => 'PIECES',
            'UNIT' => 'UNIT',
            'SET' => 'SET',
            'PACK' => 'PACK',
            'BOX' => 'BOX',
            'RIM' => 'RIM',
            'ROLL' => 'ROLL',
        ];
    }
    public function setAssetNameAttribute($value)
    {
        $this->attributes['asset_name'] = strtoupper($value);
    }
    public function setSerialNumberAttribute($value)
    {
        $this->attributes['serial_number'] = strtoupper($value);
    }
    public function setBrandAttribute($value)
    {
        $this->attributes['brand'] = strtoupper($value);
    }
    public function setModelAttribute($value)
    {
        $this->attributes['model'] = strtoupper($value);
    }
    public function assetCategory()
    {
        return $this->belongsTo(AssetCategories::class, 'asset_category_id', 'id');
    }
}
