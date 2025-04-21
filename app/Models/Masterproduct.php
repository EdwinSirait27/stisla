<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;


class Masterproduct extends Model
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
    protected $table = 'masterproduct_tables'; 

    protected $fillable = [
        'plu',
        'description',
        'long_description',
        'brand_id',
        'category_id',
        'uom_id',
        'taxstatus_id',
        'statusproduct_id',
        'good_stock',
        'bad_stock',
        'cogs',
        'retailprice',
        'memberbronzeprice',
        'membersilverprice',
        'membergoldprice',
        'memberplatinumprice',
        'min_stock',
        'max_stock',
        'weight',
        
    ];
   
    public function brand()
    {
        return $this->belongsTo(Brands::class, 'brand_id', 'id');
    }
    public function uom()
    {
        return $this->belongsTo(Uoms::class, 'uom_id', 'id');
    }
    public function taxstatus()
    {
        return $this->belongsTo(Taxstatus::class, 'taxstatus_id', 'id');
    }
    public function statusproduct()
    {
        return $this->belongsTo(Statusproduct::class, 'statusproduct_id', 'id');
    }
    public function category()
    {
        return $this->belongsTo(Categories::class, 'category_id', 'id');
    }
//     public function employee()
// {
//     return $this->hasOne(Employee::class, 'department_id');
// }

}
