<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
class Payrollcomponents extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $keyType = 'string';
 protected $attributes = [
        'is_fixed' => false,
    ];
    protected $casts = [
        'is_fixed' => 'boolean',
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = Uuid::uuid7()->toString();
            }
        });
    }
    protected $table = 'payroll_components'; 
    protected $fillable = [
        'component_name',
        'type',
        'is_fixed',
    ];
     public static function getTypeOptions()
{
    return [
        'Deduction' => 'Deduction',
        'Income' => 'Income'];
}
 public function setComponentNameAttribute($value)
    {
        $this->attributes['component_name'] = strtoupper($value);
    }
}
