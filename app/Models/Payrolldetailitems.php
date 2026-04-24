<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Payrolldetailitems extends Model
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
    protected $table = 'payroll_detail_items'; 
    protected $fillable = [
        'payroll_detail_id',
        'payroll_component_id',
        'amount',
        
    ];
public function component()
    {
        return $this->belongsTo(Payrollcomponents::class, 'component_id','id');
    }
public function detail()
    {
        return $this->belongsTo(Payrollcomponents::class, 'payroll_detail_id','id');
    }
}
